"""
Modul deteksi orang menggunakan YOLOv8.

Inisialisasi (muat model) terjadi SEKALI saat modul ini pertama kali di-import.
Fungsi detect_person() kemudian dipanggil berulang kali oleh camera_manager.py
dari dalam thread pool — tidak pernah dari thread kamera utama.
"""

from __future__ import annotations

from pathlib import Path

import cv2  # OpenCV — untuk menggambar kotak dan teks di atas frame

from config import YOLO_DEVICE, YOLO_ENABLED, YOLO_HALF, YOLO_MODEL_PATH


# ─── Resolusi path model ──────────────────────────────────────────────────────

# Coba gunakan path dari .env terlebih dahulu.
_model_path = Path(YOLO_MODEL_PATH)

# Jika path dari .env tidak menunjuk ke file yang ada,
# fallback ke camera/models/yolov8n.pt (lokasi default).
if not _model_path.is_file():
    _model_path = Path(__file__).resolve().parent / "models" / "yolov8n.pt"


# ─── Muat model (hanya jika YOLO_ENABLED=true) ───────────────────────────────

if YOLO_ENABLED:
    # Import dilakukan di dalam blok ini agar ultralytics tidak perlu terinstall
    # ketika YOLO_ENABLED=false — menghemat ~400 MB RAM dan waktu startup.
    from ultralytics import YOLO

    # Memuat model ke memori. Untuk .pt → PyTorch biasa.
    # Untuk .engine → TensorRT (hanya Jetson), proses loading lebih cepat setelah
    # engine selesai dikompilasi.
    _model = YOLO(str(_model_path))

    print(f"[CV] Model YOLO dimuat: {_model_path}")
    print(f"[CV] Device: {YOLO_DEVICE} | FP16 (half): {YOLO_HALF}")
else:
    # YOLO dimatikan — set None agar detect_person() langsung return tanpa error.
    _model = None
    print("[CV] YOLO_ENABLED=false — deteksi orang dinonaktifkan, stream tetap jalan")


# ─── Fungsi utama ─────────────────────────────────────────────────────────────

def detect_person(frame):
    """
    Jalankan inferensi YOLO pada satu frame gambar untuk mendeteksi orang.

    Parameter:
        frame : numpy.ndarray  — frame BGR dari OpenCV (hasil cap.read())

    Mengembalikan:
        (frame_anotasi, ada_orang)
        frame_anotasi : frame dengan kotak hijau + label confidence di sekitar orang
        ada_orang     : True jika minimal satu orang terdeteksi, False jika tidak ada

    Dipanggil dari _run_yolo_async() di camera_manager.py dalam thread pool,
    sehingga tidak pernah memblokir thread pembaca kamera.
    """

    # Jika YOLO dimatikan, kembalikan frame asli tanpa anotasi dan deteksi = False.
    if not YOLO_ENABLED or _model is None:
        return frame, False

    # Jalankan inferensi YOLO pada frame.
    # device  → 'cpu' untuk PC, 'cuda:0' untuk GPU Jetson/NVIDIA
    # half    → True = FP16 (lebih cepat di GPU), False = FP32 (wajib di CPU)
    # verbose → False = matikan logging per-frame (sangat cerewet jika true)
    results = _model(
        frame,
        device=YOLO_DEVICE,
        half=YOLO_HALF,
        verbose=False,
    )

    person_found = False  # akan di-set True jika ada class 0 (person) terdeteksi

    # results adalah list (satu per gambar input; di sini selalu 1 gambar).
    for result in results:
        # result.boxes berisi semua objek yang terdeteksi dalam satu gambar.
        for box in result.boxes:

            # box.cls[0] → kelas objek (int). YOLO COCO dataset:
            # 0=person, 1=bicycle, 2=car, 63=laptop, dll.
            cls = int(box.cls[0])

            # Filter: hanya proses class 0 (orang). Abaikan semua kelas lain.
            if cls != 0:
                continue

            person_found = True

            # box.xyxy[0] → koordinat piksel [x1, y1, x2, y2]
            # (x1,y1) = sudut kiri atas, (x2,y2) = sudut kanan bawah
            x1, y1, x2, y2 = map(int, box.xyxy[0])

            # box.conf[0] → tingkat kepercayaan deteksi (0.0–1.0)
            # Contoh: 0.87 berarti model 87% yakin ini adalah orang.
            conf = float(box.conf[0])

            # Gambar kotak persegi panjang hijau di sekitar orang.
            # (0, 255, 0) = warna hijau dalam format BGR
            # 2 = ketebalan garis piksel
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)

            # Tulis label "PERSON 0.87" di atas kotak.
            # putText(gambar, teks, posisi, font, ukuran, warna, ketebalan)
            cv2.putText(
                frame,
                f"PERSON {conf:.2f}",
                (x1, y1 - 10),            # posisi: 10 piksel di atas kotak
                cv2.FONT_HERSHEY_SIMPLEX,  # font standar OpenCV
                0.6,                       # ukuran font
                (0, 255, 0),               # warna hijau
                2,                         # ketebalan teks
            )

    return frame, person_found


# ─── Export TensorRT (Jetson Nano — jalankan sekali) ─────────────────────────
#
# Untuk performa maksimal di Jetson Nano, ekspor model .pt ke .engine (TensorRT).
# Proses ini membutuhkan 5–10 menit HANYA PERTAMA KALI — hasilnya disimpan ke disk.
#
# Cara menjalankan (di terminal Jetson Nano):
#
#   cd camera
#   python3 -c "
#   from ultralytics import YOLO
#   model = YOLO('models/yolov8n.pt')
#   model.export(format='engine', device='cuda:0', half=True, imgsz=640)
#   # Menghasilkan: models/yolov8n.engine
#   "
#
# Setelah selesai, update camera/.env:
#   YOLO_MODEL_PATH=models/yolov8n.engine
#   YOLO_DEVICE=cuda:0
#   YOLO_HALF=true
#
# Perbandingan kecepatan deteksi:
#   PC CPU + .pt         →  5–10 FPS
#   Jetson cuda:0 + .pt  → 15–25 FPS
#   Jetson cuda:0 + .engine (TensorRT) → ~30 FPS
