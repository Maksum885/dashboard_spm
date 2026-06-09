# SPM SCADA

Web-based SCADA dashboard for **SPM testing bay** — monitor test pits and test cells, PLC telemetry, and optional CCTV with human detection.

## Features

- **3D facility map** with per-room detail panels
- **PLC integration** via Modbus TCP (`python-modbus` bridge → Laravel webhook)
- **Per-room RTSP cameras** (2 slots) with **YOLOv8** person detection (`camera/` Flask service)
- **Role-based access** — admin, operator, viewer
- **Realtime updates** via Laravel Echo / Pusher (optional)
- **Dummy mode** for UI demos without live PLC or CV (`VITE_DASHBOARD_USE_DUMMY`)

## Tech stack

| Layer      | Stack                                         |
| ---------- | --------------------------------------------- |
| Backend    | Laravel 12, PHP 8.2+, Sanctum                 |
| Frontend   | Vite, JavaScript, Three.js, Tailwind          |
| Database   | SQL Server (or SQLite/MySQL for dev)          |
| PLC bridge | Python 3.10+, FastAPI, pymodbus               |
| CV service | Python 3.10+, Flask, OpenCV, Ultralytics YOLO |

## Requirements

- PHP 8.2+, Composer, Node.js (LTS), npm
- Python 3.10+ (for `python-modbus/` and `camera/`)
- SQL Server or compatible DB (see `.env.example`)
- FFmpeg (recommended for RTSP via OpenCV)

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

Open `http://127.0.0.1:8000` and sign in:

| Role              | Email                            | Password      |
| ----------------- | -------------------------------- | ------------- |
| Admin             | `admin@spm-scada.com`            | `admin123`    |
| Operator (room N) | `operator.room{N}@spm-scada.com` | `operator123` |

Change passwords in production.

### Optional services

**PLC bridge** (`python-modbus/`):

```bash
cd python-modbus
python -m venv .venv
# activate venv, then:
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8001
```

**CV / cameras** (`camera/`):

```bash
cd camera
cp .env.example .env   # set LARAVEL_BRIDGE_CAMERAS_URL + token
pip install -r requirements.txt
python camera_stream.py
```

Enable cameras in **Settings → Camera configuration** and set `VITE_CV_ENABLED=true` in Laravel `.env`, then restart Vite.

**UI-only demo** (no PLC/CV):

```env
VITE_DASHBOARD_USE_DUMMY=true
```

## Key environment variables

See [`.env.example`](.env.example) for full comments.

| Variable                   | Purpose                                                           |
| -------------------------- | ----------------------------------------------------------------- |
| `PLC_BRIDGE_TOKEN`         | Token for Python bridge APIs (`bridge-devices`, `bridge-cameras`) |
| `PLC_WEBHOOK_SECRET`       | Header `X-PLC-Secret` on PLC webhook                              |
| `VITE_CV_ENABLED`          | Enable live camera streams in dashboard                           |
| `VITE_CV_BASE_URL`         | Flask CV service URL (e.g. `http://127.0.0.1:5000`)               |
| `VITE_DASHBOARD_USE_DUMMY` | Static demo data + Demo roof / Demo CV panels                     |

## Project structure

```
spm-scada/
├── app/                 # Laravel application
├── resources/js/        # Dashboard (Vite)
├── python-modbus/       # Modbus polling → Laravel webhook
├── camera/              # RTSP + YOLO Flask service
├── database/            # Migrations & seeders
└── docs/                # Detailed documentation (Indonesian)
```

## Documentation

| Document                                                                               | Description                  |
| -------------------------------------------------------------------------------------- | ---------------------------- |
| [docs/GAMBARAN_SISTEM.md](docs/GAMBARAN_SISTEM.md)                                     | System overview (Indonesian) |
| [docs/PANDUAN_MENJALANKAN_SISTEM.md](docs/PANDUAN_MENJALANKAN_SISTEM.md)               | Setup & operations guide     |
| [docs/SISTEM_CARA_KERJA_DAN_INDEKS_FILE.md](docs/SISTEM_CARA_KERJA_DAN_INDEKS_FILE.md) | Architecture & file index    |
| [docs/PENJELASAN_CARA_KERJA_SISTEM.md](docs/PENJELASAN_CARA_KERJA_SISTEM.md)           | End-to-end data flows        |
| [ROADMAP-SCADA.md](ROADMAP-SCADA.md)                                                   | Improvement roadmap          |

## Testing & code style

```bash
composer test
vendor/bin/pint
```

## License

The Laravel framework is [MIT licensed](https://opensource.org/licenses/MIT). Product licensing follows repository owner policy.
