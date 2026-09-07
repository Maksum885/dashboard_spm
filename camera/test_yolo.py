"""
Skrip verifikasi cepat: pastikan model YOLO berhasil dimuat sebelum menjalankan server.

Cara pakai:
  cd camera
  python test_yolo.py

Jika berhasil, output:
  [CV] Model YOLO dimuat: models/yolov8n.pt
  [CV] Device: cuda:0 | FP16 (half): True
  Verifikasi OK.

Jika gagal (model tidak ada, CUDA tidak tersedia, dll), error akan tampil di sini
sehingga bisa diperbaiki sebelum menjalankan camera_stream.py.
"""

import sys
from pathlib import Path

# Tambahkan folder camera/ ke sys.path agar import bekerja saat dijalankan dari luar folder.
sys.path.insert(0, str(Path(__file__).resolve().parent))

# Import ini akan memicu inisialisasi model di yolo_detector.py.
# Jika YOLO_ENABLED=false, _model=None dan tidak ada error.
from yolo_detector import _model, detect_person  # noqa: F401

if _model is not None:
    print("Verifikasi OK — model YOLO siap digunakan.")
else:
    print("Verifikasi OK — YOLO_ENABLED=false, deteksi dinonaktifkan, stream tetap jalan.")
