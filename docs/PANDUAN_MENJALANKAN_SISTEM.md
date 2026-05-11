# Panduan menjalankan sistem SPM SCADA (dari awal sampai operasional)

Dokumen ini menjelaskan **tahapan berurutan** untuk menyiapkan lingkungan pengembangan atau demo lokal hingga semua layanan (Laravel, frontend, bridge Modbus) dapat digunakan bersama PLC atau simulator.

---

## Ringkasan komponen

| Komponen | Teknologi | Fungsi |
|----------|-----------|--------|
| Aplikasi web | Laravel 12 (PHP 8.2+) | Autentikasi, dashboard, API, penyimpanan data PLC |
| Antarmuka | Vite + Tailwind + JavaScript | Tampilan dashboard, peta 3D, pengaturan PLC |
| Bridge industri | Python (FastAPI + pymodbus) | Polling Modbus TCP → webhook ke Laravel |
| Database | SQL Server | User, ruang uji, device PLC, log, snapshot |

---

## Prasyarat perangkat lunak

Instal di mesin pengembangan:

1. **PHP 8.2 atau lebih baru** — ekstensi umum Laravel: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`.
2. **Composer** — manajer dependensi PHP.
3. **Node.js** (disarankan LTS) dan **npm** — untuk Vite dan aset frontend.
4. **Python 3.10+** (disarankan 3.11+) — untuk folder `python-modbus`.
5. **Git** (opsional) — kontrol versi.

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

| Variabel | Keterangan |
|----------|------------|
| `APP_URL` | URL dasar aplikasi, mis. `http://127.0.0.1:8000` |
| `PLC_BRIDGE_TOKEN` | Token rahasia untuk `GET /api/plc/bridge-devices?token=...` (dipakai Python agar konfigurasi IP/port/unit_id mengikuti **Settings**) |
| `PLC_WEBHOOK_SECRET` | (Opsional) Jika diisi, webhook Python harus mengirim header `X-PLC-Secret` dengan nilai sama |

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

| Variabel | Contoh | Keterangan |
|----------|--------|------------|
| `LARAVEL_WEBHOOK_URL` | `http://127.0.0.1:8000/api/plc/webhook` | Target POST hasil polling |
| `POLL_INTERVAL_SEC` | `5` | Interval antar siklus polling (detik) |
| `ENABLE_POLLING` | `1` | Aktifkan loop polling saat FastAPI startup |
| `MODBUS_PORT` | `502` | Port Modbus TCP default per device (bisa di-override dari Laravel jika pakai bridge) |
| `PLC_WEBHOOK_SECRET` | (sama dengan Laravel) | Jika Laravel memvalidasi header webhook |
| `LARAVEL_BRIDGE_DEVICES_URL` | `http://127.0.0.1:8000/api/plc/bridge-devices?token=...` | **Opsional:** samakan `token` dengan `PLC_BRIDGE_TOKEN` di Laravel agar IP/port/**unit_id** mengikuti **Settings** |
| `PLC_DEFAULT_UNIT_ID` | `1` | Fallback unit/slave jika tidak pakai bridge (map statis `config.py`) |

Tanpa `LARAVEL_BRIDGE_DEVICES_URL`, Python memakai **`config.py` / env `PLC_01_IP` …** yang harus Anda jaga konsisten dengan database secara manual.

### 7.3 Menjalankan uvicorn

```powershell
cd c:\Users\user\spm-scada\python-modbus
.\.venv\Scripts\Activate.ps1
uvicorn main:app --host 0.0.0.0 --port 8001
```

Port 8001 hanya untuk **API bridge** (health, endpoint PLC lokal); traffic Modbus ke PLC tetap ke **IP:502** per device.

---

## Tahap 8 — Jaringan dan PLC

1. **PLC atau simulator Modbus TCP** harus dapat dijangkau dari mesin yang menjalankan Python (firewall, subnet, routing).
2. Di **Settings → PLC** di website, set **IP, port (biasanya 502), unit_id (slave)** agar sama dengan konfigurasi fisik PLC.
3. **Unit ID** di PLC harus sama dengan yang tersimpan di Laravel (`plc_devices.unit_id`) dan yang dibaca Python (via bridge atau `PLC_DEFAULT_UNIT_ID`).
4. **Peta register** di PLC harus selaras dengan `python-modbus/modbus/register_map.py` (holding **40001–40012**, blok 12 word dari address PDU **0**).

---

## Tahap 9 — Verifikasi end-to-end

1. Buka aplikasi web → login sebagai admin atau operator.
2. Buka **Dashboard** — data ruang seharusnya ter-load dari API (`GET /api/dashboard`).
3. Buka **Settings → PLC** — pastikan device enabled dan parameter jaringan benar.
4. Pastikan proses **uvicorn** berjalan dan log menunjukkan polling (tanpa error koneksi jika PLC hidup).
5. Di Laravel, cek cache/API `GET /api/plc/{room_id}/data` — setelah webhook berhasil, status/snapshot dapat terisi.
6. Jika **Pusher/Reverb** dikonfigurasi, pilih satu ruang di dashboard — event `PlcRoomUpdated` dapat memperbarui UI; jika tidak, UI dapat mengandalkan polling API atau refresh.

---

## Urutan start yang disarankan (development)

1. `php artisan serve`
2. `npm run dev` (jika mengembangkan UI)
3. `uvicorn main:app ...` di `python-modbus`

Matikan dengan `Ctrl+C` pada masing-masing terminal.

---

## Troubleshooting singkat

| Gejala | Periksa |
|--------|---------|
| 403 pada bridge devices | `PLC_BRIDGE_TOKEN` Laravel sama dengan `?token=` di URL Python |
| 401 pada webhook | `PLC_WEBHOOK_SECRET` cocok di kedua sisi (atau kosongkan keduanya untuk dev) |
| PLC timeout | IP/port, kabel/jaringan, PLC program run, unit_id |
| Dashboard kosong / 401 API | Login ulang; token Sanctum; cookie/session |
| Python tidak ikut Settings | Set `LARAVEL_BRIDGE_DEVICES_URL`; pastikan device `is_enabled` di DB |

---

## Dokumen terkait

- **`docs/PENJELASAN_CARA_KERJA_SISTEM.md`** — alur kerja dari sisi tampilan dan sisi kode secara detail.
