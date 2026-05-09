import asyncio, httpx
from datetime import datetime
from config import PLC_DEVICES, POLL_INTERVAL_SEC, LARAVEL_WEBHOOK
from modbus.client import read_plc

# Simpan state terakhir setiap PLC untuk deteksi perubahan
_last_state: dict = {}

async def detect_changes(room_id: int, new_data: dict) -> list:
    """Deteksi perubahan register dibanding polling sebelumnya."""
    changes = []
    last = _last_state.get(room_id, {})

    for address, info in new_data.items():
        old_entry = last.get(address)
        new_val   = info["value"]
        old_val   = old_entry["value"] if old_entry else None

        if old_val is None or old_val != new_val:
            changes.append({
                "register_address": address,
                "register_name":    info["key"],
                "register_desc":    info["desc"],
                "old_value":        old_val,
                "new_value":        new_val,
                "raw_value":        info["raw"],
            })

    _last_state[room_id] = new_data
    return changes

async def send_to_laravel(room_id: int, snapshot: dict, changes: list):
    """Kirim data snapshot & perubahan ke Laravel via webhook."""
    payload = {
        "room_id":    room_id,
        "polled_at":  datetime.utcnow().isoformat(),
        "status":     snapshot["status"],
        "snapshot":   snapshot["data"],
        "changes":    changes,
    }
    try:
        async with httpx.AsyncClient() as client:
            await client.post(LARAVEL_WEBHOOK, json=payload, timeout=5)
    except Exception as e:
        print(f"[WARN] Gagal kirim ke Laravel room {room_id}: {e}")

async def poll_single(room_id: int):
    """Poll 1 PLC dan kirim hasilnya ke Laravel."""
    config  = PLC_DEVICES[room_id]
    result  = await read_plc(room_id, config)
    changes = await detect_changes(room_id, result.get("data", {}))

    # Kirim ke Laravel hanya kalau ada perubahan atau setiap N poll
    if changes or True:  # Ganti 'True' dengan counter jika mau throttle
        await send_to_laravel(room_id, result, changes)

    return result

async def poll_all():
    """Poll semua 11 PLC secara paralel."""
    tasks   = [poll_single(room_id) for room_id in PLC_DEVICES]
    results = await asyncio.gather(*tasks, return_exceptions=True)
    return results

async def start_polling():
    """Loop polling terus menerus."""
    print(f"[INFO] Polling dimulai, interval: {POLL_INTERVAL_SEC}s")
    while True:
        try:
            await poll_all()
        except Exception as e:
            print(f"[ERROR] Poll error: {e}")
        await asyncio.sleep(POLL_INTERVAL_SEC)
