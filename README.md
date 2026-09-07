# Testing Bay Dashboard

Web-based SCADA dashboard untuk **testing bay** — monitoring test pit dan test cell, telemetri PLC, dan kamera CCTV dengan deteksi manusia opsional.

## Fitur

- **Peta fasilitas 3D** dengan panel detail per ruang
- **Integrasi PLC** via Modbus TCP (`python-modbus` bridge → Laravel webhook)
- **Kamera RTSP per ruang** (2 slot) dengan **YOLOv8** person detection (`camera/` Flask service — berjalan di **Jetson Nano**)
- **Role-based access** — admin, operator, viewer
- **Realtime updates** via Laravel Echo / Pusher (opsional)
- **Dummy mode** untuk demo UI tanpa PLC atau CV live (`VITE_DASHBOARD_USE_DUMMY`)

## Tech stack

| Layer       | Stack                                         |
| ----------- | --------------------------------------------- |
| Backend     | Laravel 12, PHP 8.2+, Sanctum                 |
| Frontend    | Vite, JavaScript, Three.js, Tailwind          |
| Database    | SQL Server (sqlsrv)                           |
| PLC bridge  | Python 3.10+, FastAPI, pymodbus 3.x           |
| CV service  | Python 3.8+, Flask, OpenCV, Ultralytics YOLO  |
| CV hardware | Jetson Nano P3450 (JetPack 4.6)               |

## Requirements

- PHP 8.2+, Composer, Node.js (LTS), npm
- Python 3.10+ untuk `python-modbus/` (server PC)
- Python 3.8+ untuk `camera/` (Jetson Nano, terpisah)
- SQL Server
- PLC Schneider M221 TM221CE24R via Modbus TCP port 502

## Quick start

```bash
# 1. Environment
cp .env.example .env
php artisan key:generate

# 2. Dependencies
composer install
npm install

# 3. Database
php artisan migrate
php artisan db:seed

# 4. Run (dev — Laravel + Vite + queue)
composer run dev
```

Buka `http://127.0.0.1:8000` dan login:

| Role              | Email                              | Password      |
| ----------------- | ---------------------------------- | ------------- |
| Admin             | `admin@testing-bay.local`          | `admin123`    |
| Operator (room N) | `operator.roomN@testing-bay.local` | `operator123` |

Ganti password di lingkungan produksi.

### Optional services

**PLC bridge** (`python-modbus/`) — jalankan di server PC:

```bash
cd python-modbus
python -m venv .venv
# activate venv, lalu:
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8001
```

**CV / cameras** (`camera/`) — jalankan di **Jetson Nano**:

```bash
cd camera
cp .env.example .env   # set LARAVEL_BRIDGE_CAMERAS_URL + token + CAMERA_INPUT_MODE=gst_rtsp
# WAJIB sebelum jalankan Python di Jetson:
export OPENBLAS_CORETYPE=ARMV8   # tambahkan ke ~/.bashrc
pip install -r requirements.txt  # termasuk pymodbus==2.5.3
python camera_stream.py
```

Model YOLO didownload otomatis oleh ultralytics saat pertama kali jalan di Jetson. Jika ingin TensorRT:

```python
from ultralytics import YOLO
YOLO('models/yolov8n.pt').export(format='engine', device='cuda:0', half=True, imgsz=640)
```

Enable kamera di **Settings → Camera configuration** dan set `VITE_CV_ENABLED=true` + `VITE_CV_BASE_URL=http://192.168.1.200:5001` di Laravel `.env`, lalu rebuild Vite.

**UI-only demo** (tanpa PLC/CV):

```env
VITE_DASHBOARD_USE_DUMMY=true
```

## Variabel lingkungan penting

Lihat [`.env.example`](.env.example) untuk penjelasan lengkap.

| Variabel                   | Fungsi                                                             |
| -------------------------- | ------------------------------------------------------------------ |
| `PLC_BRIDGE_TOKEN`         | Token untuk bridge API Python (`bridge-devices`, `bridge-cameras`) |
| `PLC_WEBHOOK_SECRET`       | Header `X-PLC-Secret` pada PLC webhook                             |
| `VITE_CV_ENABLED`          | `true` = aktifkan stream kamera live di dashboard                  |
| `VITE_CV_BASE_URL`         | URL Flask CV di Jetson Nano — `http://192.168.1.200:5001`          |
| `VITE_DASHBOARD_USE_DUMMY` | Data statis demo + panel Demo roof / Demo CV                       |

## Struktur project

```
testing-bay/
├── app/                 # Aplikasi Laravel
├── resources/js/        # Dashboard (Vite + Three.js)
├── python-modbus/       # Polling Modbus TCP → webhook Laravel
├── camera/              # RTSP + YOLO Flask service (deploy ke Jetson Nano)
├── database/            # Migrations & seeders
└── docs/                # Dokumentasi teknis (Bahasa Indonesia)
```

## Dokumentasi

| Dokumen                                                                                | Keterangan                          |
| -------------------------------------------------------------------------------------- | ----------------------------------- |
| [docs/GAMBARAN_SISTEM.md](docs/GAMBARAN_SISTEM.md)                                     | Gambaran sistem & fitur             |
| [docs/PANDUAN_MENJALANKAN_SISTEM.md](docs/PANDUAN_MENJALANKAN_SISTEM.md)               | Panduan instalasi & operasional     |
| [docs/SISTEM_CARA_KERJA_DAN_INDEKS_FILE.md](docs/SISTEM_CARA_KERJA_DAN_INDEKS_FILE.md) | Arsitektur & indeks file            |
| [docs/PENJELASAN_CARA_KERJA_SISTEM.md](docs/PENJELASAN_CARA_KERJA_SISTEM.md)           | Alur data end-to-end                |
| [camera/PANDUAN_CV.md](camera/PANDUAN_CV.md)                                           | Panduan lengkap CV service (Jetson) |
| [ROADMAP.md](ROADMAP.md)                                                               | Rencana penyempurnaan               |

## Testing & code style

```bash
composer test
vendor/bin/pint
```

## License

Kerangka aplikasi mengikuti lisensi **Laravel** ([MIT](https://opensource.org/licenses/MIT)). Lisensi produk mengikuti kebijakan pemilik repositori.
