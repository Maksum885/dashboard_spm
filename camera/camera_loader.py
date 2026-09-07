"""
Mengambil daftar kamera aktif dari Laravel via HTTP.

Fungsi ini dipanggil:
  1. Saat startup (sekali, sebelum Flask jalan)
  2. Setiap CV_MAP_REFRESH_SEC detik oleh thread background di camera_stream.py

Jika Laravel tidak bisa dijangkau, fungsi mengembalikan {} (dict kosong)
dan mencetak warning — program tidak crash, hanya tidak ada kamera yang dimuat.
"""

from __future__ import annotations

import os

import httpx  # library HTTP yang lebih modern dari requests; mendukung timeout yang lebih jelas


def load_cameras_sync() -> dict[int, dict[int, dict]]:
    """
    Mengembalikan struktur:
      {
        room_id (int): {
          slot (int): {
            'rtsp_url': str,   ← URL sumber kamera (RTSP, csi://, usb://)
            'name':     str,   ← nama kamera
            'room_name': str,  ← nama ruangan
          }
        }
      }

    Contoh:
      {
        1: {1: {'rtsp_url': 'rtsp://192.168.1.50:554/stream', 'name': 'Kamera 1', ...}},
        2: {1: {'rtsp_url': 'csi://0', 'name': 'CSI Cam', ...}},
      }
    """

    # Ambil URL dari environment. Jika tidak di-set, kembalikan dict kosong.
    url = os.getenv("LARAVEL_BRIDGE_CAMERAS_URL", "").strip()
    if not url:
        print("[INFO] LARAVEL_BRIDGE_CAMERAS_URL tidak di-set — tidak ada kamera yang dimuat")
        return {}

    try:
        # Buka sesi HTTP dengan timeout 15 detik.
        # 'with' memastikan koneksi ditutup rapi setelah selesai.
        with httpx.Client(timeout=15.0) as client:
            resp = client.get(url)
            resp.raise_for_status()  # lempar error jika status HTTP 4xx/5xx
            body = resp.json()       # parse JSON dari Laravel

        # Laravel mengembalikan {"ok": true, "cameras": {...}}.
        # Jika 'ok' false atau tidak ada, anggap gagal.
        if not body.get("ok"):
            raise ValueError(body.get("reason", "respons Laravel tidak ok"))

        # 'cameras' berisi mapping room_id → {slot → metadata kamera}
        raw = body.get("cameras") or {}

        out: dict[int, dict[int, dict]] = {}
        for room_key, slots in raw.items():
            room_id = int(room_key)  # key dari JSON selalu string, konversi ke int
            out[room_id] = {}
            for slot_key, meta in (slots or {}).items():
                slot = int(slot_key)
                out[room_id][slot] = {
                    "rtsp_url":  str(meta["rtsp_url"]),
                    "name":      str(meta.get("name",      f"Camera {slot}")),
                    "room_name": str(meta.get("room_name", f"Room {room_id}")),
                }

        total = sum(len(v) for v in out.values())
        print(f"[INFO] Daftar kamera dimuat dari Laravel ({total} stream)")
        return out

    except Exception as exc:
        # Tangkap semua error (network timeout, JSON invalid, dll).
        # Jangan crash — kembalikan dict kosong dan biarkan retry berikutnya mencoba lagi.
        print(f"[WARN] Gagal memuat kamera dari Laravel: {exc!r}")
        return {}
