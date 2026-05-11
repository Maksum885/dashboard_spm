"""
Load PLC connection map (testing_room_id → ip, port, unit_id) for the poller.

If LARAVEL_BRIDGE_DEVICES_URL is set, fetches enabled devices from Laravel Settings
(must match PLC_BRIDGE_TOKEN). Otherwise uses static PLC_DEVICES from config.py / env.
"""

from __future__ import annotations

import os

import httpx


def load_plc_devices_sync() -> dict[int, dict]:
    url = os.getenv("LARAVEL_BRIDGE_DEVICES_URL", "").strip()
    if not url:
        from config import PLC_DEVICES

        return {int(k): dict(v) for k, v in PLC_DEVICES.items()}

    try:
        with httpx.Client(timeout=15.0) as client:
            resp = client.get(url)
            resp.raise_for_status()
            body = resp.json()

        if not body.get("ok"):
            raise ValueError(body.get("reason", "not ok"))

        raw = body.get("devices") or {}
        out: dict[int, dict] = {}
        for key, v in raw.items():
            rid = int(key)
            out[rid] = {
                "ip": str(v["ip"]),
                "port": int(v.get("port", 502)),
                "unit_id": int(v["unit_id"]),
                "name": str(v.get("name", f"Room {rid}")),
            }

        # All rooms disabled in Settings → poll nothing (do not fall back to config.py).
        if not out:
            print("[INFO] PLC map from Laravel: 0 enabled device(s) — polling skipped until Settings enable a room.")
            return {}

        print(f"[INFO] PLC map loaded from Laravel ({len(out)} device(s))")
        return out
    except Exception as e:
        print(f"[WARN] Cannot load PLC map from Laravel ({e!r}); using config.py / env defaults")
        from config import PLC_DEVICES

        return {int(k): dict(v) for k, v in PLC_DEVICES.items()}
