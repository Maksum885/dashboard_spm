# Panduan menjalankan sistem SPM SCADA (dari awal sampai operasional)

Dokumen ini menjelaskan **tahapan berurutan** untuk menyiapkan lingkungan pengembangan atau demo lokal hingga semua layanan (Laravel, frontend, bridge Modbus) dapat digunakan bersama PLC atau simulator.

---

## Ringkasan komponen

| Komponen     | Teknologi                      | Fungsi                                                        |
| ------------ | ------------------------------ | ------------------------------------------------------------- |
| Aplikasi web | Laravel 12 (PHP 8.2+)          | Autentikasi, dashboard, API, penyimpanan data PLC & kamera    |
| Antarmuka    | Vite + JavaScript + Three.js   | Dashboard, peta 3D, settings PLC/kamera                       |
| Bridge PLC   | Python (FastAPI + pymodbus)    | Polling Modbus TCP → webhook ke Laravel                       |
| Layanan CV   | Python (Flask + OpenCV + YOLO) | RTSP multi-room, stream MJPEG, deteksi manusia                |
| Database     | SQL Server                     | User, ruang uji, `plc_devices`, `room_cameras`, log, snapshot |

---

## Prasyarat perangkat lunak

Instal di mesin pengembangan:

1. **PHP 8.2 atau lebih baru** — ekstensi umum Laravel: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`.
2. **Composer** — manajer dependensi PHP.
3. **Node.js** (disarankan LTS) dan **npm** — untuk Vite dan aset frontend.
4. **Python 3.10+** (disarankan 3.11+) — untuk `python-modbus/` dan `camera/`.
5. **FFmpeg** (disarankan) — decode RTSP di layanan CV.
6. **Git** (opsional) — kontrol versi.

**Windows:** gunakan PowerShell atau terminal yang mendukung path proyek `c:\Users\...\spm-scada`.

---

## Tahap 1 — Siapkan salinan proyek dan file lingkungan Laravel

1. Buka folder root proyek (berisi `artisan`, `composer.json`, `package.json`).
2. Salin file lingkungan:
    - Jika belum ada `.env`, salin dari `.env.example` ke `.env`.
3. Generate kunci aplikasi:

```powershell
cd c:\Users\user\spm-scada
php artisan key:generate
```

4. **Database** di `.env`:
    - Default contoh memakai SQLite: `DB_CONNECTION=sqlite` dan pastikan file database ada, misalnya:
        - Buat file kosong `database\database.sqlite` jika belum ada.
    - Atau set `DB_CONNECTION=mysql` (atau `pgsql`) lengkap dengan host, nama DB, user, password.

---

## Tahap 2 — Instal dependensi PHP dan Node

Dari root proyek:

```powershell
composer install
npm install
```

---

## Tahap 3 — Migrasi dan seed database

Menjalankan migrasi membuat tabel. **Seed** mengisi ruang uji, device PLC default, dan akun contoh.

```powershell
php artisan migrate
php artisan db:seed
```

Untuk menyegarkan total dari nol (hapus data lama):

```powershell
php artisan migrate:fresh --seed
```

**Akun contoh setelah seed** (lihat juga output `DatabaseSeeder`):

- Admin: `admin@spm-scada.com` / `admin123`
- Operator per control room: `operator1@spm-scada.com` … (password `operator123`)
- Viewer: `viewer@spm-scada.com` / `viewer123`

Ganti password di lingkungan produksi.

---

## Tahap 4 — Konfigurasi variabel penting Laravel (PLC & keamanan)

Tambahkan atau sesuaikan di **`.env`** Laravel (referensi juga di `.env.example`):

| Variabel                   | Keterangan                                                                                                                           |
| -------------------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| `APP_URL`                  | URL dasar aplikasi, mis. `http://127.0.0.1:8000`                                                                                     |
| `PLC_BRIDGE_TOKEN`         | Token rahasia untuk `GET /api/plc/bridge-devices?token=...` (dipakai Python agar konfigurasi IP/port/unit_id mengikuti **Settings**) |
| `PLC_WEBHOOK_SECRET`       | (Opsional) Jika diisi, webhook Python harus mengirim header `X-PLC-Secret` dengan nilai sama                                         |
| `VITE_CV_ENABLED`          | `true` = dashboard memuat stream live dari layanan `camera/`                                                                         |
| `VITE_CV_BASE_URL`         | URL Flask CV, mis. `http://127.0.0.1:5000` (tanpa trailing slash)                                                                    |
| `VITE_DASHBOARD_USE_DUMMY` | `true` = data demo + panel Demo roof/CV (tanpa PLC/CV live)                                                                          |

**Realtime (opsional):** jika ingin pembaruan dashboard lewat WebSocket tanpa refresh penuh, set `BROADCAST_CONNECTION` (mis. `pusher` atau `reverb`) dan isi kunci Pusher/Reverb serta variabel `VITE_*` di frontend. Tanpa ini, broadcast tetap dipanggil di server tetapi klien tidak menerima event kecuali dikonfigurasi.

---

## Tahap 5 — Menjalankan server Laravel

```powershell
cd c:\Users\user\spm-scada
php artisan serve
```

Default: `http://127.0.0.1:8000`. Samakan host/port dengan `APP_URL` dan dengan URL webhook/bridge di Python.

---

## Tahap 6 — Menjalankan frontend (Vite)

Terminal terpisah:

```powershell
cd c:\Users\user\spm-scada
npm run dev
```

Ikuti URL yang ditampilkan Vite (biasanya `http://127.0.0.1:5173` dengan proxy ke Laravel sesuai `vite.config`). Untuk produksi gunakan `npm run build` dan serve aset ter-build lewat web server.

---

## Tahap 7 — Bridge Python (Modbus → Laravel)

### 7.1 Lingkungan virtual dan dependensi

```powershell
cd c:\Users\user\spm-scada\python-modbus
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

### 7.2 File `.env` di `python-modbus`

Pastikan minimal:

| Variabel                     | Contoh                                                   | Keterangan                                                                                                         |
| ---------------------------- | -------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| `LARAVEL_WEBHOOK_URL`        | `http://127.0.0.1:8000/api/plc/webhook`                  | Target POST hasil polling                                                                                          |
| `POLL_INTERVAL_SEC`          | `5`                                                      | Interval antar siklus polling (detik)                                                                              |
| `ENABLE_POLLING`             | `1`                                                      | Aktifkan loop polling saat FastAPI startup                                                                         |
| `MODBUS_PORT`                | `502`                                                    | Port Modbus TCP default per device (bisa di-override dari Laravel jika pakai bridge)                               |
| `PLC_WEBHOOK_SECRET`         | (sama dengan Laravel)                                    | Jika Laravel memvalidasi header webhook                                                                            |
| `LARAVEL_BRIDGE_DEVICES_URL` | `http://127.0.0.1:8000/api/plc/bridge-devices?token=...` | **Opsional:** samakan `token` dengan `PLC_BRIDGE_TOKEN` di Laravel agar IP/port/**unit_id** mengikuti **Settings** |
| `PLC_DEFAULT_UNIT_ID`        | `1`                                                      | Fallback unit/slave jika tidak pakai bridge (map statis `config.py`)                                               |

Tanpa `LARAVEL_BRIDGE_DEVICES_URL`, Python memakai **`config.py` / env `PLC_01_IP` …** yang harus Anda jaga konsisten dengan database secara manual.

### 7.3 Menjalankan uvicorn

```powershell
cd c:\Users\user\spm-scada\python-modbus
.\.venv\Scripts\Activate.ps1
uvicorn main:app --host 0.0.0.0 --port 8001
```

Port 8001 hanya untuk **API bridge** (health, endpoint PLC lokal); traffic Modbus ke PLC tetap ke **IP:502** per device.

---

## Tahap 7b — Layanan CV / kamera (opsional)

### 7b.1 Lingkungan dan dependensi

```powershell
cd c:\Users\user\spm-scada\camera
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

Model YOLO: `camera/models/yolov8n.pt` (sudah disertakan).

### 7b.2 File `.env` di `camera/`

Salin dari `camera/.env.example`. Minimal:

| Variabel                     | Contoh                                                  | Keterangan                             |
| ---------------------------- | ------------------------------------------------------- | -------------------------------------- |
| `CV_HOST`                    | `0.0.0.0`                                               | Bind address Flask                     |
| `CV_PORT`                    | `5000`                                                  | Port stream & alarm                    |
| `LARAVEL_BRIDGE_CAMERAS_URL` | `http://127.0.0.1:8000/api/cv/bridge-cameras?token=...` | Token = `PLC_BRIDGE_TOKEN` Laravel     |
| `CV_MAP_REFRESH_SEC`         | `15`                                                    | Interval reload kamera enabled dari DB |

### 7b.3 Konfigurasi kamera di web

1. Login admin/operator → **Settings → Camera configuration** (`/settings/cameras`).
2. Per ruang uji: isi RTSP host, port, path, user/password; centang **Enable stream** per slot (Kamera 1 & 2).
3. Simpan — Python memuat ulang daftar dalam ~15 detik.

### 7b.4 Menjalankan Flask CV

```powershell
cd c:\Users\user\spm-scada\camera
.\.venv\Scripts\Activate.ps1
python camera_stream.py
```

Endpoint utama:

| URL                            | Fungsi                               |
| ------------------------------ | ------------------------------------ |
| `GET /stream/{room_id}/{slot}` | MJPEG per kamera                     |
| `GET /alarm_status`            | Status deteksi manusia per room/slot |
| `GET /health`                  | Health check                         |

Di Laravel `.env`: `VITE_CV_ENABLED=true`, `VITE_CV_BASE_URL=http://127.0.0.1:5000`, lalu **restart `npm run dev`**.

### 7b.5 Mode dummy (tanpa Flask)

Untuk uji UI saja:

```env
VITE_DASHBOARD_USE_DUMMY=true
VITE_CV_ENABLED=false
```

Admin mendapat panel **Demo CV** di peta 3D (Human ON/OFF). Contoh alarm: Test Cell 1 / Kamera 1.

---

## Tahap 8 — Jaringan dan PLC

1. **PLC atau simulator Modbus TCP** harus dapat dijangkau dari mesin yang menjalankan Python (firewall, subnet, routing).
2. Di **Settings → PLC** di website, set **IP, port (biasanya 502), unit_id (slave)** agar sama dengan konfigurasi fisik PLC.
3. **Unit ID** di PLC harus sama dengan yang tersimpan di Laravel (`plc_devices.unit_id`) dan yang dibaca Python (via bridge atau `PLC_DEFAULT_UNIT_ID`).
4. **Peta register** di PLC harus selaras dengan `python-modbus/modbus/register_map.py` (holding **40001–40012**, blok 12 word dari address PDU **0**).

---

## Tahap 9 — Verifikasi end-to-end

1. Buka aplikasi web → login sebagai admin atau operator.
2. Buka **Dashboard** — data ruang ter-load dari API (`GET /api/dashboard`) atau dummy jika `VITE_DASHBOARD_USE_DUMMY=true`.
3. Buka **Settings → PLC** — device enabled, IP/port/unit_id benar.
4. (Opsional CV) **Settings → Cameras** — enable minimal satu slot; Flask CV jalan; stream tampil di tab Overview ruang.
5. Pastikan **uvicorn** (`python-modbus`) berjalan jika memakai PLC live.
6. Cek `GET /api/plc/{room_id}/data` setelah webhook sukses.
7. (Opsional) Pusher/Echo — event `PlcRoomUpdated` memperbarui UI per ruang.
8. (Dummy) Panel **Demo roof** / **Demo CV** muncul untuk admin saat `VITE_DASHBOARD_USE_DUMMY=true`.

---

## Urutan start yang disarankan (development)

1. `php artisan serve` (atau `composer run dev`)
2. `npm run dev` (jika tidak memakai `composer run dev`)
3. `uvicorn main:app ...` di `python-modbus` (PLC live)
4. `python camera_stream.py` di `camera/` (CV live)

Matikan dengan `Ctrl+C` pada masing-masing terminal.

---

## Troubleshooting singkat

| Gejala                     | Periksa                                                                           |
| -------------------------- | --------------------------------------------------------------------------------- |
| 403 pada bridge devices    | `PLC_BRIDGE_TOKEN` Laravel sama dengan `?token=` di URL Python                    |
| 401 pada webhook           | `PLC_WEBHOOK_SECRET` cocok di kedua sisi (atau kosongkan keduanya untuk dev)      |
| PLC timeout                | IP/port, kabel/jaringan, PLC program run, unit_id                                 |
| Dashboard kosong / 401 API | Login ulang; token Sanctum; cookie/session                                        |
| Python tidak ikut Settings | Set `LARAVEL_BRIDGE_DEVICES_URL`; pastikan device `is_enabled` di DB              |
| Stream kamera hitam        | RTSP benar; FFmpeg; log `[WARN] RTSP connect failed` di `camera/`                 |
| CV "disabled" di UI        | `VITE_CV_ENABLED=true` + restart Vite; atau pakai `VITE_DASHBOARD_USE_DUMMY=true` |
| 0 stream di Python CV      | `LARAVEL_BRIDGE_CAMERAS_URL` + token; enable kamera di Settings                   |

---

## Dokumen terkait

- **`docs/GAMBARAN_SISTEM.md`** — ringkasan produk dan fitur.
- **`docs/PENJELASAN_CARA_KERJA_SISTEM.md`** — alur kerja dari sisi tampilan dan sisi kode secara detail.
