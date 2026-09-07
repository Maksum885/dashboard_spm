"""
Flask CV service — entry point utama yang dijalankan di Jetson Nano atau PC.

Cara menjalankan:
  cd camera
  python camera_stream.py

Yang terjadi saat startup:
  1. Flask app dibuat
  2. Daftar kamera diambil dari Laravel (load_cameras_sync)
  3. CameraManager memulai satu thread kamera per stream
  4. Thread background berjalan untuk refresh daftar kamera setiap N detik
  5. Flask mulai mendengarkan di CV_HOST:CV_PORT

Endpoint HTTP yang tersedia:
  GET /stream/<room_id>/<slot>  → MJPEG video stream (untuk tag <img> di browser)
  GET /alarm_status             → JSON status deteksi orang (dipoll dashboard tiap 1 detik)
  GET /health                   → cek berapa stream yang aktif (untuk monitoring)
"""

from __future__ import annotations

import logging
import os
import threading
import time

from flask import Flask, Response, jsonify
from flask_cors import CORS  # mengizinkan browser dari domain/port berbeda akses API ini

from camera_loader import load_cameras_sync
from camera_manager import CameraManager
from config import CV_HOST, CV_MAP_REFRESH_SEC, CV_PORT, RTSP_TRANSPORT

# Setup logging: format "waktu [LEVEL] nama_logger: pesan"
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)
logger = logging.getLogger("cv.app")

# Paksa OpenCV (mode ffmpeg) menggunakan transport TCP/UDP yang dikonfigurasi di .env.
# Harus di-set sebelum VideoCapture pertama dibuka.
os.environ["OPENCV_FFMPEG_CAPTURE_OPTIONS"] = f"rtsp_transport;{RTSP_TRANSPORT}"

# Buat Flask app.
app = Flask(__name__)

# Aktifkan CORS (Cross-Origin Resource Sharing):
# Browser memblokir request dari domain berbeda secara default.
# CORS(app) mengizinkan semua origin mengakses semua endpoint Flask ini.
# Wajib agar dashboard Laravel (port 8000/443) bisa akses CV service (port 5000).
CORS(app)

# Satu instance CameraManager untuk seluruh aplikasi.
# Mengelola semua thread kamera dan menyimpan frame terbaru.
manager = CameraManager()


# ─── Thread refresh daftar kamera ────────────────────────────────────────────

def _refresh_loop() -> None:
    """
    Thread background yang berjalan terus selama aplikasi hidup.

    Setiap CV_MAP_REFRESH_SEC detik, mengambil daftar kamera terbaru dari Laravel
    dan memberitahu manager untuk menyesuaikan stream yang aktif.

    Tanpa ini, perubahan kamera di dashboard Laravel tidak akan terefleksi
    sampai CV service di-restart.
    """
    while True:
        # load_cameras_sync() → HTTP GET ke Laravel → dict kamera
        # manager.refresh() → start/stop thread sesuai perubahan
        manager.refresh(load_cameras_sync())
        time.sleep(CV_MAP_REFRESH_SEC)


# ─── Endpoint HTTP ────────────────────────────────────────────────────────────

@app.route("/")
def home():
    """Cek cepat bahwa service berjalan."""
    return "Testing Bay CV service running"


@app.route("/health")
def health():
    """
    Status kesehatan service: berapa stream yang aktif.

    Digunakan untuk monitoring. Contoh:
      curl http://192.168.1.20:5000/health
      → {"ok": true, "streams": 3}
    """
    rooms = manager.alarm_status()
    # Hitung total slot kamera yang aktif di semua ruangan.
    stream_count = sum(len(slots) for slots in rooms.values())
    return jsonify({"ok": True, "streams": stream_count})


@app.route("/stream/<int:room_id>/<int:slot>")
def stream(room_id: int, slot: int):
    """
    MJPEG video stream untuk satu kamera.

    Cara browser menggunakannya:
      <img src="http://192.168.1.20:5000/stream/1/1">

    Koneksi HTTP tetap terbuka (tidak pernah ditutup) dan frame terus mengalir
    dalam format multipart/x-mixed-replace. Browser memperbarui tampilan gambar
    setiap kali frame baru diterima.

    Setiap browser tab yang membuka URL ini mendapat thread Flask sendiri
    karena app.run(threaded=True) di bawah.
    """
    return Response(
        manager.generate_mjpeg(room_id, slot),        # generator Python
        mimetype="multipart/x-mixed-replace; boundary=frame",  # format MJPEG
    )


@app.route("/alarm_status")
def alarm_status():
    """
    Status deteksi orang untuk semua kamera aktif.

    Dipanggil oleh dashboard JavaScript (pollCvAlarmStatus() di main.js)
    setiap 1 detik untuk mengecek apakah ada orang terdeteksi.

    Contoh respons:
      {
        "ok": true,
        "rooms": {
          "1": {"1": false, "2": true},   ← room 1 slot 2 ada orang
          "2": {"1": false}
        }
      }

    Jika "true" → ikon alarm muncul di sidebar dashboard untuk ruangan tersebut.
    """
    return jsonify({"ok": True, "rooms": manager.alarm_status()})


# Route lama untuk kompatibilitas dengan versi dashboard sebelumnya.
# Mengalihkan ke stream room=1 slot=1 (kamera pertama di ruangan pertama).
@app.route("/camera1")
def camera1_legacy():
    return stream(1, 1)


# ─── Startup ──────────────────────────────────────────────────────────────────

if __name__ == "__main__":
    # Muat daftar kamera pertama kali sebelum Flask mulai menerima request.
    manager.refresh(load_cameras_sync())

    # Mulai thread background untuk refresh kamera secara berkala.
    # daemon=True → thread ini ikut berhenti saat proses Python berhenti (Ctrl+C).
    threading.Thread(
        target=_refresh_loop,
        name="cv-map-refresh",
        daemon=True,
    ).start()

    logger.info("CV service berjalan di %s:%s", CV_HOST, CV_PORT)

    # Jalankan Flask.
    # host → dari config.py (default 0.0.0.0, menerima dari semua network interface)
    # port → dari config.py (default 5000)
    # threaded=True → WAJIB: setiap request HTTP mendapat thread sendiri.
    # Tanpa ini, saat browser A sedang streaming, browser B harus menunggu.
    # debug=False  → WAJIB untuk production; debug=True menonaktifkan threaded mode.
    app.run(host=CV_HOST, port=CV_PORT, threaded=True, debug=False)
