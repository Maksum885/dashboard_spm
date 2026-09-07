from __future__ import annotations

import os
from pathlib import Path

from dotenv import load_dotenv

# Membaca file camera/.env ke dalam os.environ.
# Harus dipanggil sebelum os.getenv() apa pun di bawah ini.
load_dotenv()

# Direktori tempat file config.py ini berada (= folder camera/).
# Dipakai sebagai base path untuk mencari file model YOLO.
BASE_DIR = Path(__file__).resolve().parent


# ─── Flask Server ────────────────────────────────────────────────────────────

# Alamat IP tempat Flask mendengarkan koneksi.
# '0.0.0.0' = menerima dari semua interface (localhost + jaringan LAN).
# Wajib '0.0.0.0' agar browser dari komputer/Jetson lain bisa akses.
CV_HOST = os.getenv("CV_HOST", "0.0.0.0")

# Port tempat Flask berjalan. Browser mengakses http://<ip>:<port>/stream/...
# Jetson Nano: gunakan 5001 (port 5000 sering dipakai proses lain di JetPack).
CV_PORT = int(os.getenv("CV_PORT", "5001"))


# ─── Laravel Bridge ──────────────────────────────────────────────────────────

# URL endpoint Laravel untuk mengambil daftar kamera aktif.
# Format respons: {"ok": true, "cameras": {room_id: {slot: {rtsp_url, name, ...}}}}
# PENTING untuk Jetson Nano: ganti 127.0.0.1 dengan IP server Laravel.
# Contoh: http://192.168.1.10:8000/api/cv/bridge-cameras?token=xxx
LARAVEL_BRIDGE_CAMERAS_URL = os.getenv("LARAVEL_BRIDGE_CAMERAS_URL", "").strip()

# Seberapa sering (detik) daftar kamera di-reload dari Laravel.
# Berguna ketika admin menambah/menghapus kamera lewat dashboard.
CV_MAP_REFRESH_SEC = float(os.getenv("CV_MAP_REFRESH_SEC", "15"))


# ─── YOLO / Deteksi Orang ────────────────────────────────────────────────────

# Master switch untuk fitur deteksi orang.
# false = model YOLO tidak dimuat sama sekali → hemat ~400 MB RAM dan 100% CPU YOLO.
# Stream video RTSP tetap berjalan; hanya kotak deteksi yang dinonaktifkan.
YOLO_ENABLED = os.getenv("YOLO_ENABLED", "true").strip().lower() in ("1", "true", "yes")

# Path ke file model YOLO.
# Bisa .pt  → PyTorch biasa (untuk PC atau Jetson tanpa TensorRT)
# Bisa .engine → TensorRT (hanya Jetson, performa 2–3× lebih cepat)
# Jika kosong/tidak ada file, otomatis fallback ke camera/models/yolov8n.pt
YOLO_MODEL_PATH = os.getenv("YOLO_MODEL_PATH", str(BASE_DIR / "models" / "yolov8n.pt"))

# Interval inferensi YOLO: jalankan deteksi setiap N frame.
# N=1  → setiap frame (hanya cocok Jetson + TensorRT, GPU kuat)
# N=3  → setiap 3 frame (Jetson dengan .pt biasa)
# N=5  → default (PC biasa atau beban ringan)
# N=10 → hemat CPU, deteksi lebih lambat bereaksi
# Inferensi YOLO berjalan di thread TERPISAH sehingga tidak pernah memblokir
# pembacaan frame kamera, berapapun nilai N ini.
YOLO_DETECT_EVERY_N_FRAMES = max(1, int(os.getenv("YOLO_DETECT_EVERY_N_FRAMES", "5")))

# Perangkat untuk menjalankan inferensi YOLO.
# 'cpu'    → default, berjalan di CPU (lambat, 5–10 FPS deteksi)
# 'cuda:0' → GPU pertama NVIDIA — untuk Jetson Nano dan PC dengan GPU NVIDIA
# '0'      → alias cuda:0 (diterima oleh ultralytics)
YOLO_DEVICE = os.getenv("YOLO_DEVICE", "cpu").strip()

# FP16 half-precision inference.
# true  → setengah presisi float, ~2× lebih cepat dan hemat VRAM di GPU Jetson.
# false → FP32 penuh (wajib untuk CPU; GPU juga bisa tapi lebih lambat)
# AKTIFKAN HANYA jika YOLO_DEVICE=cuda:0. Tidak berpengaruh di CPU.
YOLO_HALF = os.getenv("YOLO_HALF", "false").strip().lower() in ("1", "true", "yes")


# ─── Mode Input Kamera ───────────────────────────────────────────────────────

# Menentukan cara cv2.VideoCapture membuka sumber kamera.
# Empat pilihan:
#
#   'ffmpeg'   → OpenCV via FFmpeg (default). Cocok PC biasa, RTSP IP camera.
#                Decoding H.264 dilakukan CPU.
#
#   'gst_rtsp' → GStreamer pipeline untuk RTSP. Khusus Jetson Nano.
#                Menggunakan nvv4l2decoder (hardware H.264 decoder di GPU Jetson).
#                Decoding berpindah dari CPU ke GPU → CPU jauh lebih bebas.
#
#   'csi'      → CSI camera fisik yang terpasang di slot kamera Jetson Nano
#                (contoh: Raspberry Pi Camera v2, IMX219).
#                Gunakan URL format 'csi://0' atau 'csi://1' di database kamera.
#
#   'usb'      → USB webcam yang terhubung ke Jetson atau PC.
#                Gunakan URL format 'usb:///dev/video0' di database kamera.
CAMERA_INPUT_MODE = os.getenv("CAMERA_INPUT_MODE", "ffmpeg").strip().lower()

# Resolusi frame yang diminta dari kamera (untuk mode csi dan usb).
# Untuk RTSP, resolusi ditentukan oleh stream kamera — variabel ini diabaikan.
FRAME_WIDTH  = int(os.getenv("FRAME_WIDTH",  "1280"))
FRAME_HEIGHT = int(os.getenv("FRAME_HEIGHT", "720"))
FRAME_FPS    = int(os.getenv("FRAME_FPS",    "30"))


# ─── Stream Encoding ─────────────────────────────────────────────────────────

# Kualitas JPEG saat mengirim frame ke browser (1–100).
# Lebih rendah = file lebih kecil, bandwidth hemat, gambar kurang tajam.
# 70 adalah titik seimbang: kualitas cukup baik, ukuran tidak besar.
# Turunkan ke 50–60 jika jaringan lambat atau stream patah-patah.
JPEG_QUALITY = max(10, min(100, int(os.getenv("JPEG_QUALITY", "70"))))

# Transport layer untuk RTSP:
# 'tcp'  → lebih stabil, tidak ada packet loss (direkomendasikan LAN)
# 'udp'  → latensi lebih rendah tapi bisa packet loss
RTSP_TRANSPORT = os.getenv("RTSP_TRANSPORT", "tcp")

# Jeda sebelum coba reconnect saat kamera terputus (detik).
RECONNECT_DELAY_SEC = float(os.getenv("RECONNECT_DELAY_SEC", "2"))


# ─── Skema Hybrid: Tulis Hasil CV ke PLC (Modbus TCP FC6) ────────────────────
#
# Jika diaktifkan, Jetson Nano menulis hasil deteksi orang ke holding register PLC.
# Hasilnya kemudian dibaca oleh python-modbus, dikirim ke Laravel webhook,
# dan ditampilkan di dashboard seperti alarm PLC lainnya.
#
# Jalur langsung /alarm_status → browser TETAP berjalan bersamaan (skema hybrid).

# Master switch: aktifkan penulisan hasil CV ke PLC.
# false (default) = hanya jalur langsung /alarm_status ke browser yang aktif.
# true            = Jetson juga menulis ke PLC register 40013 tiap state berubah.
PLC_WRITE_ENABLED = os.getenv("PLC_WRITE_ENABLED", "false").strip().lower() in ("1", "true", "yes")

# Port Modbus TCP PLC. Standar industri = 502.
PLC_MODBUS_PORT = int(os.getenv("PLC_MODBUS_PORT", "502"))

# Unit ID (slave ID) PLC.
# Schneider biasanya 1. Beberapa model pakai 0 atau 255. Cek manual PLC.
PLC_MODBUS_UNIT_ID = int(os.getenv("PLC_MODBUS_UNIT_ID", "1"))

# Timeout koneksi dan baca Modbus (detik).
PLC_MODBUS_TIMEOUT = float(os.getenv("PLC_MODBUS_TIMEOUT", "3.0"))

# PDU address (0-based) tempat Jetson menulis cv_person_detected di PLC.
#
# Default 9 = PDU 9 = "Holding Register 10" (40010) = %MW9 di Schneider M221 TM221CE24R.
# Layout register:
#   Register 40001–40009 : PDU 0–8   (9 word)
#   Register 40010 (CV)  : PDU 9     (bool, 1 word)  ← di sini (Holding Register 10)
#   Register 40011       : PDU 10–11 (float32 pressure_1, 2 word)
#   Register 40012       : PDU 12–13 (float32 pressure_2, 2 word)
#
# Nilai 1 ditulis saat Jetson mendeteksi orang → PLC membaca %MW9 → nyalakan aktuator lampu.
# Nilai 0 ditulis saat tidak ada orang → PLC matikan lampu.
#
# Sesuaikan jika PLC Anda memakai layout register yang berbeda.
CV_PLC_WRITE_ADDRESS = int(os.getenv("CV_PLC_WRITE_ADDRESS", "9"))

# IP PLC per ruangan dibaca langsung dari env oleh plc_writer.py menggunakan pola:
#   PLC_01_IP=192.168.1.1
#   PLC_02_IP=192.168.1.2
#   ... dst
# Ini sengaja TIDAK didefinisikan sebagai konstanta di sini karena
# jumlah ruangan bisa berbeda-beda dan dibaca dinamis saat write.
