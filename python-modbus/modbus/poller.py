"""
python-modbus/modbus/poller.py
Background polling loop: baca semua 11 PLC async, deteksi perubahan,
kirim ke Laravel webhook.
"""

import asyncio
import httpx
from datetime import datetime, timezone

from modbus.client import read_plc_registers
from config import PLC_DEVICES, POLL_INTERVAL_SEC, LARAVEL_WEBHOOK

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
    """Kirim data ke Laravel webhook secara async. Tidak blocking polling loop."""
    try:
        async with httpx.AsyncClient(timeout=5.0) as client:
            resp = await client.post(LARAVEL_WEBHOOK, json=payload)
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
    config = PLC_DEVICES[room_id]
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
    """Poll semua 11 PLC secara paralel."""
    tasks   = [poll_single(room_id) for room_id in PLC_DEVICES]
    results = await asyncio.gather(*tasks, return_exceptions=True)

    # Log error per room
    for i, (room_id, result) in enumerate(zip(PLC_DEVICES.keys(), results)):
        if isinstance(result, Exception):
            print(f"[ERROR] Poll room {room_id}: {result}")

    return results


# ─── Background Loop ──────────────────────────────────────────────────────────

async def start_polling() -> None:
    """
    Loop polling terus-menerus.
    Dipanggil saat FastAPI startup via lifespan context.
    """
    print(f"[INFO] Polling dimulai — {len(PLC_DEVICES)} PLC, interval: {POLL_INTERVAL_SEC}s")

    while True:
        start = asyncio.get_event_loop().time()
        try:
            await poll_all()
        except Exception as e:
            print(f"[ERROR] Polling loop error: {e}")

        # Hitung sisa waktu agar interval tetap konsisten
        elapsed  = asyncio.get_event_loop().time() - start
        wait_sec = max(0, POLL_INTERVAL_SEC - elapsed)
        await asyncio.sleep(wait_sec)