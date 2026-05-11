"""
python-modbus/modbus/poller.py
Background polling loop: baca semua 11 PLC async, deteksi perubahan,
kirim ke Laravel webhook.
"""

import asyncio
import os
import httpx
from datetime import datetime, timezone

from modbus.client import read_plc_registers
from config import (
    PLC_DEVICES as _DEFAULT_PLC_DEVICES,
    POLL_INTERVAL_SEC,
    LARAVEL_WEBHOOK,
    LARAVEL_WEBHOOK_TIMEOUT,
    WEBHOOK_MAX_CONCURRENT,
    PLC_MAP_REFRESH_SEC,
)

from modbus.device_loader import load_plc_devices_sync

_webhook_sem = asyncio.Semaphore(WEBHOOK_MAX_CONCURRENT)

# Mutable copy; replaced at startup via set_plc_devices() (e.g. from Laravel Settings).
_plc_devices: dict[int, dict] = {
    int(k): dict(v) for k, v in _DEFAULT_PLC_DEVICES.items()
}


def set_plc_devices(devices: dict) -> None:
    global _plc_devices
    _plc_devices = {int(k): dict(v) for k, v in devices.items()}

# ─── In-memory state ──────────────────────────────────────────────────────────
# Menyimpan nilai register terakhir per room untuk deteksi perubahan
_last_state: dict[int, dict] = {}
# Menyimpan snapshot terakhir per room (untuk endpoint /snapshot)
_last_snapshot: dict[int, dict] = {}


def get_last_snapshot(room_id: int) -> dict | None:
    return _last_snapshot.get(room_id)


# ─── Change Detection ─────────────────────────────────────────────────────────

def detect_changes(room_id: int, new_data: dict) -> list[dict]:
    """
    Bandingkan data baru dengan data sebelumnya.
    Return list perubahan yang akan dikirim ke Laravel.
    """
    changes = []
    last    = _last_state.get(room_id, {})

    for address, info in new_data.items():
        new_val = info["value"]
        old_entry = last.get(address)
        old_val   = old_entry["value"] if old_entry else None

        # Pertama kali (old_val None) atau nilai berubah
        if old_val is None or old_val != new_val:
            changes.append({
                "register_address": address,
                "register_name":    info["key"],
                "register_desc":    info["desc"],
                "old_value":        old_val,
                "new_value":        new_val,
                "raw_value":        info["raw"],
            })

    # Update state
    _last_state[room_id] = new_data
    return changes


# ─── Webhook ke Laravel ───────────────────────────────────────────────────────

async def send_to_laravel(payload: dict) -> None:
    """Kirim ke Laravel; semaphore membatasi paralelisme agar artisan serve/SQLite tidak kewalahan."""
    secret = os.getenv("PLC_WEBHOOK_SECRET", "").strip()
    headers = {"X-PLC-Secret": secret} if secret else {}
    timeout = httpx.Timeout(LARAVEL_WEBHOOK_TIMEOUT, connect=15.0)
    async with _webhook_sem:
        try:
            async with httpx.AsyncClient(timeout=timeout) as client:
                resp = await client.post(LARAVEL_WEBHOOK, json=payload, headers=headers)
                if resp.status_code not in (200, 201):
                    print(f"[WARN] Laravel webhook room {payload['room_id']}: "
                          f"status {resp.status_code}")
        except httpx.TimeoutException:
            print(f"[WARN] Webhook timeout room {payload['room_id']}")
        except Exception as e:
            print(f"[WARN] Webhook error room {payload['room_id']}: {e}")


# ─── Single Room Poll ─────────────────────────────────────────────────────────

async def poll_single(room_id: int) -> dict:
    """Poll 1 PLC, deteksi perubahan, kirim ke Laravel, return hasilnya."""
    config = _plc_devices[room_id]
    result = await read_plc_registers(room_id, config)

    # Simpan snapshot ke memory
    _last_snapshot[room_id] = result

    # Deteksi perubahan (hanya jika berhasil baca)
    changes = []
    if result["status"] == "success":
        changes = detect_changes(room_id, result.get("data", {}))

    # Kirim ke Laravel (snapshot + changes)
    payload = {
        "room_id":   room_id,
        "polled_at": datetime.now(timezone.utc).isoformat(),
        "status":    result["status"],
        "snapshot":  result.get("data", {}),
        "changes":   changes,
        "error":     result.get("error"),
    }

    # Fire-and-forget: jangan tunggu response agar tidak block polling berikutnya
    asyncio.create_task(send_to_laravel(payload))

    return result


# ─── All Rooms Poll ───────────────────────────────────────────────────────────

async def poll_all() -> list:
    """Poll semua room yang ada di map secara paralel."""
    room_ids = list(_plc_devices.keys())
    tasks = [poll_single(room_id) for room_id in room_ids]
    results = await asyncio.gather(*tasks, return_exceptions=True)

    for room_id, result in zip(room_ids, results):
        if isinstance(result, Exception):
            print(f"[ERROR] Poll room {room_id}: {result}")

    return results


# ─── Background Loop ──────────────────────────────────────────────────────────

async def start_polling() -> None:
    """
    Loop polling terus-menerus.
    Dipanggil saat FastAPI startup via lifespan context.
    """
    loop = asyncio.get_event_loop()
    last_map_refresh = 0.0

    print(
        f"[INFO] Polling dimulai — {len(_plc_devices)} PLC, interval: {POLL_INTERVAL_SEC}s, "
        f"webhook timeout={LARAVEL_WEBHOOK_TIMEOUT}s, max concurrent={WEBHOOK_MAX_CONCURRENT}"
    )
    if PLC_MAP_REFRESH_SEC > 0:
        print(
            f"[INFO] PLC map refresh from Laravel every {PLC_MAP_REFRESH_SEC}s "
            "(uncheck “Enable polling” in Settings to stop a room; no uvicorn restart needed)."
        )

    while True:
        now = loop.time()
        if PLC_MAP_REFRESH_SEC > 0 and (now - last_map_refresh) >= PLC_MAP_REFRESH_SEC:
            try:
                devices = await asyncio.to_thread(load_plc_devices_sync)
                set_plc_devices(devices)
                last_map_refresh = now
                n = len(_plc_devices)
                print(f"[INFO] PLC map refreshed — {n} enabled room(s) will be polled")
            except Exception as e:
                print(f"[WARN] PLC map refresh failed (keeping previous map): {e}")

        start = loop.time()
        try:
            if _plc_devices:
                await poll_all()
        except Exception as e:
            print(f"[ERROR] Polling loop error: {e}")

        # Hitung sisa waktu agar interval tetap konsisten
        elapsed = loop.time() - start
        wait_sec = max(0, POLL_INTERVAL_SEC - elapsed)
        await asyncio.sleep(wait_sec)