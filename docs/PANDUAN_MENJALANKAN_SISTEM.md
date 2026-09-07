# Panduan Menjalankan Sistem Testing Bay (dari awal sampai operasional)

Dokumen ini menjelaskan **tahapan berurutan** untuk menyiapkan lingkungan pengembangan atau produksi hingga semua layanan (Laravel, frontend, bridge Modbus, CV Jetson) dapat digunakan bersama PLC.

---

## Ringkasan komponen

| Komponen      | Teknologi                              | Dijalankan di | Fungsi                                                        |
| ------------- | -------------------------------------- | ------------- | ------------------------------------------------------------- |
| Aplikasi web  | Laravel 12 (PHP 8.2+)                  | Server PC     | Autentikasi, dashboard, API, penyimpanan data PLC & kamera    |
| Antarmuka     | Vite + JavaScript + Three.js           | Server PC     | Dashboard, peta 3D, settings PLC/kamera                       |
| Bridge PLC    | Python 3.10+, FastAPI, pymodbus 3.x    | Server PC     | Polling Modbus TCP → webhook ke Laravel                       |
| Layanan CV    | Python 3.8+, Flask, OpenCV, YOLO       | Jetson Nano   | RTSP multi-room, stream MJPEG, deteksi manusia → PLC          |
| Database      | SQL Server                             | Server PC     | User, ruang uji, `plc_devices`, `room_cameras`, log, snapshot |
| PLC           | Schneider M221 TM221CE24R              | Lapangan      | Sensor/aktuator via Modbus TCP port 502                       |

---

## Prasyarat perangkat lunak

### Server PC (Laravel + python-modbus)

1. **PHP 8.2 atau lebih baru** — ekstensi: `openssl`, `pdo`, `pdo_sqlsrv`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`.
2. **Composer** — manajer dependensi PHP.
3. **Node.js** (LTS) dan **npm** — untuk Vite dan aset frontend.
4. **Python 3.10+** — untuk `python-modbus/`.
5. **SQL Server** — database utama.
6. **Git** (opsional) — kontrol versi.

### Jetson Nano P3450 (layanan CV)

1. **JetPack 4.6** (Ubuntu 18.04 + CUDA 10.2).
2. **Python 3.8** — `sudo apt install python3.8 python3.8-venv python3.8-dev`.
3. **GStreamer** — sudah termasuk JetPack (avdec_h264 + GStreamer OpenCV build).
4. **Kamera IP Hikvision** terhubung via RTSP ke jaringan LAN.

---

## Tahap 1 — Siapkan file lingkungan Laravel

1. Buka folder root proyek (berisi `artisan`, `composer.json`, `package.json`).
2. Salin file lingkungan:
    ```powershell
    cp .env.example .env
    ```
3. Generate kunci aplikasi:
    ```powershell
    php artisan key:generate
    ```
4. **Database** di `.env` — proyek memakai SQL Server:
    ```env
    DB_CONNECTION=sqlsrv
    DB_HOST=127.0.0.1
    DB_PORT=1433
    DB_DATABASE=db_testing_bay
    DB_USERNAME=sa
    DB_PASSWORD=YourStrongPassword
    ```

---

## Tahap 2 — Instal dependensi PHP dan Node

Dari root proyek:

```powershell
composer install
npm install
```

---

## Tahap 3 — Migrasi dan seed database

```powershell
php artisan migrate
php artisan db:seed
```

Untuk menyegarkan total dari nol (hapus data lama):

```powershell
php artisan migrate:fresh --seed
```

**Akun contoh setelah seed:**

- Admin: `admin@testing-bay.local` / `admin123`
- Operator: `operator.room1@testing-bay.local` … (password `operator123`)

Ganti password di lingkungan produksi.

---

## Tahap 4 — Konfigurasi variabel penting Laravel

Tambahkan atau sesuaikan di **`.env`** Laravel (referensi di `.env.example`):

| Variabel                   | Keterangan                                                                           |
| -------------------------- | ------------------------------------------------------------------------------------ |
| `APP_URL`                  | URL dasar aplikasi, mis. `http://127.0.0.1:8000`                                     |
| `PLC_BRIDGE_TOKEN`         | Token untuk `GET /api/plc/bridge-devices?token=...` dan `GET /api/cv/bridge-cameras` |
| `PLC_WEBHOOK_SECRET`       | Jika diisi, webhook Python harus mengirim header `X-PLC-Secret` dengan nilai sama    |
| `VITE_CV_ENABLED`          | `true` = dashboard memuat stream live dari Jetson Nano                               |
| `VITE_CV_BASE_URL`         | URL Flask CV di Jetson: `http://192.168.1.200:5001` (tanpa trailing slash)           |
| `VITE_DASHBOARD_USE_DUMMY` | `true` = data demo + panel Demo roof/CV (tanpa PLC/CV live)                          |

Setelah mengubah variabel `VITE_*`, jalankan `npm run build`.

**Realtime (opsional):** set `BROADCAST_CONNECTION=pusher` dan isi kunci Pusher/Reverb serta `VITE_PUSHER_*`.

---

## Tahap 5 — Menjalankan server Laravel

```powershell
php artisan serve
```

Default: `http://127.0.0.1:8000`.

---

## Tahap 6 — Menjalankan frontend (Vite)

Development (hot reload):

```powershell
npm run dev
```

Produksi (build statis, lalu serve via web server):

```powershell
npm run build
```

---

## Tahap 7 — Bridge Python / Modbus (Server PC)

### 7.1 Lingkungan virtual dan dependensi

```powershell
cd python-modbus
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

### 7.2 File `.env` di `python-modbus/`

Pastikan minimal:

| Variabel                     | Contoh                                                    | Keterangan                                                              |
| ---------------------------- | --------------------------------------------------------- | ----------------------------------------------------------------------- |
| `LARAVEL_WEBHOOK_URL`        | `http://127.0.0.1:8000/api/plc/webhook`                   | Target POST hasil polling                                               |
| `POLL_INTERVAL_SEC`          | `5`                                                       | Interval antar siklus polling (detik)                                   |
| `ENABLE_POLLING`             | `1`                                                       | Aktifkan loop polling saat FastAPI startup                              |
| `PLC_WEBHOOK_SECRET`         | (sama dengan Laravel)                                     | Header `X-PLC-Secret` jika Laravel memvalidasi                          |
| `LARAVEL_BRIDGE_DEVICES_URL` | `http://127.0.0.1:8000/api/plc/bridge-devices?token=...`  | IP/port/unit_id PLC diambil dari Laravel Settings (token = `PLC_BRIDGE_TOKEN`) |

### 7.3 Menjalankan uvicorn

```powershell
cd python-modbus
.\.venv\Scripts\Activate.ps1
uvicorn main:app --host 0.0.0.0 --port 8001
```

---

## Tahap 7b — Layanan CV / kamera (Jetson Nano)

Layanan ini **berjalan di Jetson Nano**, bukan di PC server. File `camera/` disalin ke Jetson.

### 7b.1 Setup awal di Jetson Nano

```bash
# Wajib: set sebelum Python apapun dijalankan
echo "export OPENBLAS_CORETYPE=ARMV8" >> ~/.bashrc
source ~/.bashrc
```

Tambahkan juga ke unit systemd jika menjalankan sebagai service:
```
Environment=OPENBLAS_CORETYPE=ARMV8
```

### 7b.2 Lingkungan virtual dan dependensi

```bash
cd camera
python3.8 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt   # termasuk pymodbus==2.5.3
```

Model YOLO didownload otomatis saat pertama kali dijalankan (`ultralytics` mendownload `yolov8n.pt`). Untuk performa maksimal, ekspor ke TensorRT:

```python
from ultralytics import YOLO
YOLO('models/yolov8n.pt').export(format='engine', device='cuda:0', half=True, imgsz=640)
# Hasil: models/yolov8n.engine
```

### 7b.3 File `.env` di `camera/`

Salin dari `camera/.env.example` dan sesuaikan:

| Variabel                     | Nilai Jetson                                                     | Keterangan                                       |
| ---------------------------- | ---------------------------------------------------------------- | ------------------------------------------------ |
| `CV_HOST`                    | `0.0.0.0`                                                        | Bind semua interface                             |
| `CV_PORT`                    | `5001`                                                           | Port Flask                                       |
| `LARAVEL_BRIDGE_CAMERAS_URL` | `http://192.168.1.10:8000/api/cv/bridge-cameras?token=TOKEN`     | IP server Laravel di jaringan LAN                |
| `CAMERA_INPUT_MODE`          | `gst_rtsp`                                                       | GStreamer + avdec_h264 (software H.264 decoder)  |
| `YOLO_DEVICE`                | `cuda:0`                                                         | GPU Jetson Nano                                  |
| `YOLO_HALF`                  | `true`                                                           | FP16 — lebih cepat di GPU                        |
| `PLC_WRITE_ENABLED`          | `true`                                                           | Tulis hasil CV ke Holding Register 10 PLC        |
| `PLC_04_IP`                  | `192.168.1.4`                                                    | IP PLC Schneider M221 (room_id 4)                |

Format URL kamera Hikvision di database (Settings → Camera configuration):
```
rtsp://admin:password@192.168.1.64:554/Streaming/Channels/102
```
(`Channels/102` = sub-stream resolusi rendah 640×360, lebih ringan)

### 7b.4 Menjalankan Flask CV

```bash
cd camera
source .venv/bin/activate
python camera_stream.py
```

Endpoint yang tersedia:

| URL                            | Fungsi                                          |
| ------------------------------ | ----------------------------------------------- |
| `GET /stream/{room_id}/{slot}` | MJPEG per kamera                                |
| `GET /alarm_status`            | Status deteksi manusia per room/slot (polling)  |
| `GET /health`                  | Health check + jumlah stream aktif              |

Di Laravel `.env` server: `VITE_CV_ENABLED=true`, `VITE_CV_BASE_URL=http://192.168.1.200:5001`, lalu **`npm run build`**.

### 7b.5 Mode dummy (tanpa Jetson/Flask)

```env
VITE_DASHBOARD_USE_DUMMY=true
VITE_CV_ENABLED=false
```

Admin mendapat panel **Demo CV** di peta 3D. Contoh alarm: Test Cell 1 / Kamera 1.

---

## Tahap 8 — Jaringan dan PLC

1. **PLC Schneider M221** harus dapat dijangkau dari PC server (python-modbus) dan dari Jetson Nano via jaringan LAN.
2. Di **Settings → PLC** di website, set **IP, port (502), unit_id (1)** sesuai konfigurasi fisik PLC.
3. **Peta register** PLC harus selaras dengan `python-modbus/modbus/register_map.py`:
    - Holding register 40001–40009: boolean/uint16
    - Holding register 40010: `cv_person_detected` (ditulis Jetson via FC6)
    - Holding register 40011–40012: float32 tekanan (2 word masing-masing)
    - Total 14 word (PDU 0–13)
4. Di Settings → Camera configuration: tambahkan record kamera per ruang (slot 1 & 2, RTSP URL, enable).

---

## Tahap 9 — Verifikasi end-to-end

1. Buka aplikasi web → login sebagai admin atau operator.
2. Buka **Dashboard** — data ruang ter-load dari API atau dummy jika `VITE_DASHBOARD_USE_DUMMY=true`.
3. Buka **Settings → PLC** — device enabled, IP/port/unit_id benar.
4. Buka **Settings → Cameras** — enable minimal satu slot kamera.
5. Pastikan Jetson Nano menjalankan `camera_stream.py` — cek `GET http://192.168.1.200:5001/health`.
6. Stream kamera tampil di tab Overview ruang.
7. Pastikan **uvicorn** (`python-modbus`) berjalan — cek `GET /api/plc/{room_id}/data` setelah webhook sukses.
8. Ketika orang terdeteksi kamera, sidebar dashboard menampilkan alarm human.

---

## Urutan start yang disarankan

1. SQL Server aktif
2. `php artisan serve` (atau `composer run dev`)
3. `npm run dev` / `npm run build`
4. `uvicorn main:app ...` di `python-modbus/`
5. `python camera_stream.py` di Jetson Nano

Matikan dengan `Ctrl+C` pada masing-masing terminal.

---

## Troubleshooting singkat

| Gejala                       | Periksa                                                                                   |
| ---------------------------- | ----------------------------------------------------------------------------------------- |
| 403 pada bridge devices      | `PLC_BRIDGE_TOKEN` Laravel sama dengan `?token=` di URL Python                            |
| 401 pada webhook             | `PLC_WEBHOOK_SECRET` cocok di kedua sisi (atau kosongkan keduanya untuk dev)              |
| PLC timeout                  | IP/port, kabel/jaringan, PLC program run, unit_id=1                                       |
| Dashboard kosong / 401 API   | Login ulang; token Sanctum; cookie/session                                                |
| Python tidak ikut Settings   | Set `LARAVEL_BRIDGE_DEVICES_URL`; pastikan device `is_enabled` di DB                     |
| Stream kamera hitam          | Cek RTSP URL Hikvision (Channels/102); log `[WARN] RTSP connect failed` di Jetson        |
| CV "disabled" di UI          | `VITE_CV_ENABLED=true` + `npm run build`; atau pakai `VITE_DASHBOARD_USE_DUMMY=true`     |
| 0 stream di Jetson           | `LARAVEL_BRIDGE_CAMERAS_URL` + token; enable kamera di Settings                          |
| numpy crash di Jetson        | `export OPENBLAS_CORETYPE=ARMV8` di `~/.bashrc` sebelum Python                           |
| pymodbus import error        | Cek `pip install pymodbus==2.5.3` (bukan >=3.0 — API berbeda)                            |

---

## Dokumen terkait

- **`docs/GAMBARAN_SISTEM.md`** — ringkasan produk dan fitur.
- **`docs/PENJELASAN_CARA_KERJA_SISTEM.md`** — alur kerja dari sisi tampilan dan sisi kode.
- **`camera/PANDUAN_CV.md`** — panduan lengkap CV service dan sintaks kode Jetson Nano.
