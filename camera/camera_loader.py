"""
Load enabled RTSP cameras from Laravel Settings (bridge API).

Falls back to an empty map when the URL is not set or the request fails.
"""

from __future__ import annotations

import os

import httpx


def load_cameras_sync() -> dict[int, dict[int, dict]]:
    url = os.getenv("LARAVEL_BRIDGE_CAMERAS_URL", "").strip()
    if not url:
        print("[INFO] LARAVEL_BRIDGE_CAMERAS_URL not set — no cameras loaded")
        return {}

    try:
        with httpx.Client(timeout=15.0) as client:
            resp = client.get(url)
            resp.raise_for_status()
            body = resp.json()

        if not body.get("ok"):
            raise ValueError(body.get("reason", "not ok"))

        raw = body.get("cameras") or {}
        out: dict[int, dict[int, dict]] = {}
        for room_key, slots in raw.items():
            room_id = int(room_key)
            out[room_id] = {}
            for slot_key, meta in (slots or {}).items():
                slot = int(slot_key)
                out[room_id][slot] = {
                    "rtsp_url": str(meta["rtsp_url"]),
                    "name": str(meta.get("name", f"Camera {slot}")),
                    "room_name": str(meta.get("room_name", f"Room {room_id}")),
                }

        print(f"[INFO] Camera map loaded from Laravel ({sum(len(v) for v in out.values())} stream(s))")
        return out
    except Exception as exc:
        print(f"[WARN] Cannot load cameras from Laravel ({exc!r})")
        return {}
