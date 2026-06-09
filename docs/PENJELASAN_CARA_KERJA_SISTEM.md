# Penjelasan cara kerja sistem SPM SCADA (tampilan & internals)

Dokumen ini menjelaskan **alur dari awal sampai akhir** dalam dua sudut pandang:

1. **Sisi luar (website / pengguna)** — apa yang dilihat dan dilakukan operator.
2. **Sisi dalam (kode & sintaks)** — file, endpoint, dan rantai eksekusi utama.

---

## 1. Gambaran arsitektur

```mermaid
flowchart LR
  subgraph browser [Browser]
    UI[Dashboard + Settings]
  end
  subgraph laravel [Laravel]
    Web[Web Controllers + Blade]
    API[API + Sanctum]
    DB[(Database)]
    Cache[(Cache)]
    BC[Broadcast / Echo]
  end
  subgraph python [python-modbus]
    FA[FastAPI main.py]
    POL[poller.py]
    CLI[client.py Modbus TCP]
  end
  subgraph cv [camera]
    FL[camera_stream.py]
    YOLO[yolo_detector.py]
  end
  PLC[PLC Modbus TCP]
  RTSP[RTSP cameras]

  UI --> Web
  UI --> API
  Web --> DB
  API --> DB
  API --> Cache
  API --> BC
  BC -.-> UI
  FA --> POL
  POL --> CLI
  CLI --> PLC
  POL -->|POST webhook JSON| API
  FA -->|GET bridge-devices| API
  FL -->|GET bridge-cameras| API
  FL --> RTSP
  FL --> YOLO
  UI -->|stream / alarm_status| FL
  API --> DB
```

- **Parameter PLC** disimpan di tabel **`plc_devices`** (Settings → PLC configuration).
- **Parameter kamera RTSP** disimpan di **`room_cameras`** — 2 slot per ruang (Settings → Camera configuration).
- **python-modbus** memuat PLC enabled lewat **`GET /api/plc/bridge-devices`**; fallback `config.py`.
- **camera/** memuat RTSP enabled lewat **`GET /api/cv/bridge-cameras`** (token sama dengan `PLC_BRIDGE_TOKEN`).

---

## 2. Sisi luar: alur pengguna (start → finish)

### 2.1 Masuk ke sistem

1. Pengguna membuka URL aplikasi (mis. `http://127.0.0.1:8000` atau via dev server Vite).
2. Jika belum login, middleware Laravel mengarahkan ke **`/login`** (`routes/web.php`, grup `guest`).
3. Form login mem-post kredensial; setelah sukses, sesi web aktif dan pengguna diarahkan ke **dashboard** (`/`).

**File terkait (web):**

- `routes/web.php` — rute `login`, `logout`, `/`, `/settings/plc`, `/settings/cameras`.
- `app/Http/Controllers/Web/Guest/AuthController.php` — proses login/logout web.

### 2.2 Dashboard (tampilan utama)

1. Setelah login, rute **`/`** memuat halaman dashboard (Blade + aset Vite).
2. JavaScript memuat data agregat ruang kontrol dan utilitas lewat **API** (`GET /api/dashboard`) menggunakan token **Laravel Sanctum** (biasanya dari cookie/session SPA atau mekanisme yang dipakai `AuthService`).

**Perilaku UI utama:**

- Pengguna memilih **control room** dan **testing room** (pit/cell).
- Panel kanan menampilkan status, log peristiwa, dan visualisasi (termasuk adegan Three.js pada elemen peta).
- Jika variabel lingkungan **`VITE_PUSHER_APP_KEY`** (dan konfigurasi Echo) aktif, frontend berlangganan channel **`plc.room.{roomId}`** dan mendengarkan event **`.PlcRoomUpdated`** untuk memperbarui data PLC tanpa memuat ulang seluruh halaman.

**File terkait (frontend):**

- `resources/views/shared/dashboard.blade.php` — kerangka HTML dashboard.
- `resources/js/dashboard/main.js` — tab, ruang, kamera/CV, **`bindPlcEchoForRoom`**, alarm gabungan.
- `resources/js/dashboard/demoCvControls.js` / `demoRoofControls.js` — panel demo (dummy mode).
- `resources/js/dashboard/init.js` — inisialisasi (termasuk Three.js / peta).
- `resources/js/services/dashboardData.js` — **`getDashboardData()`**, **`PlcAPI.getRoomData`**.
- `resources/js/services/authService.js` — token / pemanggilan API.
- `resources/css/dashboard.css` — gaya termasuk halaman Settings.

### 2.3 Settings — PLC dan kamera

**PLC (`/settings/plc`):**

1. Kartu per device: IP, port, unit ID, enable polling.
2. **PUT** ke Laravel → tabel **`plc_devices`**.
3. python-modbus refresh map ~15 detik jika `LARAVEL_BRIDGE_DEVICES_URL` diisi.

**Kamera (`/settings/cameras`):**

1. Dua slot RTSP per ruang uji: host, port, path, user/password, enable stream.
2. **PUT** per slot → tabel **`room_cameras`**.
3. Service `camera/` refresh ~15 detik lewat `LARAVEL_BRIDGE_CAMERAS_URL`.

**File terkait:**

- `app/Http/Controllers/Web/Operator/PlcConnectionController.php`
- `app/Http/Controllers/Web/Operator/CameraConnectionController.php`
- `resources/views/operator/plc-connection.blade.php`
- `resources/views/operator/camera-connection.blade.php`

### 2.3b Kamera di dashboard & mode dummy

- **Live:** `VITE_CV_ENABLED=true` → stream `{VITE_CV_BASE_URL}/stream/{room_id}/{slot}`; alarm human di sidebar.
- **Dummy:** `VITE_DASHBOARD_USE_DUMMY=true` → feed DEMO, alarm contoh (Test Cell 1), panel **Demo CV** (admin).

### 2.4 Peran operator vs admin

- **Middleware `role`** pada beberapa rute API (mis. alarm acknowledge, admin users) membatasi aksi.
- **Middleware `room.access`** membatasi akses data ruang tertentu sesuai assignment user (control room).

**File terkait:**

- `app/Http/Middleware/RoleMiddleware.php`
- `app/Http/Middleware/RoomAccessMiddleware.php`
- `bootstrap/app.php` — registrasi alias middleware.

---

## 3. Sisi dalam: rantai data PLC (start → finish)

### 3.1 Konfigurasi device di database

- Model **`App\Models\PlcDevice`** merepresentasikan satu PLC per **`testing_room_id`** (isi awal dari **`database/seeders/DatabaseSeeder.php`**).
- Field penting: `ip_address`, `port`, `unit_id`, `is_enabled`, `status`, `last_seen_at`.

### 3.2 Startup bridge Python

1. **`python-modbus/main.py`** memuat **`.env`** (path eksplisit di folder `python-modbus`).
2. Jika **`ENABLE_POLLING`** aktif, pada **lifespan** FastAPI:
    - **`load_plc_devices_sync()`** (`modbus/device_loader.py`):
        - Jika **`LARAVEL_BRIDGE_DEVICES_URL`** ada: **HTTP GET** ke Laravel → JSON `{ ok, devices: { "1": { ip, port, unit_id, name }, ... } }` hanya untuk **`is_enabled = true`**.
        - Jika gagal atau URL kosong: fallback ke **`config.PLC_DEVICES`**.
    - **`set_plc_devices(...)`** (`modbus/poller.py`) mengisi map in-memory **`_plc_devices`**.
    - **`start_polling()`** memulai loop async.

### 3.3 Polling Modbus

1. **`poll_all()`** memanggil **`poll_single(room_id)`** untuk setiap kunci di **`_plc_devices`** secara paralel.
2. **`read_plc_registers`** (`modbus/client.py`):
    - Membuka **AsyncModbusTcpClient** ke **`plc_config["ip"]`** dan **`plc_config["port"]`**.
    - Membaca **holding registers** mulai **PDU address `REGISTER_START` (0)** sebanyak **`REGISTER_COUNT` (12)** dengan **slave/unit = `plc_config["unit_id"]`**.
    - Memetakan word ke alamat logis **40001–40012** lewat **`REGISTER_MAP`** dan **`address_to_index`** di **`modbus/register_map.py`**.
3. Hasil per ruang: status `success` / `timeout` / `error`, plus struktur **`data`** berisi nilai ter-decode per register.

### 3.4 Deteksi perubahan dan webhook

1. **`detect_changes`** (`poller.py`) membandingkan snapshot register dengan state sebelumnya per **`room_id`**.
2. **`send_to_laravel`** mengirim **POST** ke **`LARAVEL_WEBHOOK`** (env `LARAVEL_WEBHOOK_URL`) dengan JSON:
    - `room_id`, `polled_at`, `status`, `snapshot`, `changes`, `error`.
3. Jika **`PLC_WEBHOOK_SECRET`** di Python diisi, header **`X-PLC-Secret`** ikut dikirim agar cocok dengan validasi di Laravel.

### 3.5 Pemrosesan webhook di Laravel

**Rute:** `POST /api/plc/webhook` — **`App\Http\Controllers\Api\PlcController::webhook`**

Urutan logika utama:

1. Validasi opsional **`X-PLC-Secret`** terhadap **`PLC_WEBHOOK_SECRET`** di `.env`.
2. Validasi payload (`room_id`, `changes`, `snapshot`, `status`, `polled_at`).
3. Cari **`TestingRoom`** dan **`PlcDevice`** untuk `room_id`; jika tidak ada → **404**.
4. Update **`plc_devices`**: `status` (online jika sukses), `last_seen_at`.
5. Tulis **cache** `plc_snapshot_{roomId}` untuk dibaca API cepat.
6. **Broadcast** event **`PlcRoomUpdated`** (channel `plc.room.{id}`) untuk klien Echo.
7. Simpan baris **`plc_snapshots`**.
8. Untuk setiap item di **`changes`**, simpan **`plc_register_logs`** (dan alarm terkait register tertentu).

### 3.6 API yang dibaca frontend / integrasi lain

| Metode | Path                                | Fungsi                                                                                  |
| ------ | ----------------------------------- | --------------------------------------------------------------------------------------- |
| GET    | `/api/dashboard`                    | Payload dashboard untuk user yang login (`DashboardController` + **`PlcDataService`**). |
| GET    | `/api/plc/{room_id}/data`           | Snapshot cache terakhir untuk satu ruang.                                               |
| GET    | `/api/plc/{room_id}/logs`           | Riwayat perubahan register.                                                             |
| GET    | `/api/plc/bridge-devices?token=...` | Daftar PLC untuk python-modbus.                                                         |
| GET    | `/api/cv/bridge-cameras?token=...`  | Daftar RTSP enabled untuk `camera/`.                                                    |
| POST   | `/api/plc/webhook`                  | Masukan polling dari python-modbus.                                                     |

**Autentikasi:** sebagian besar rute API di **`routes/api.php`** memakai **`auth:sanctum`**; webhook dan bridge memakai **secret/token** terpisah, bukan session browser.

### 3.7 Penyusun data dashboard

**`App\Services\PlcDataService`** (dipakai `DashboardController`) menggabungkan data domain (ruang kontrol, ruang uji, utilitas, snapshot PLC dari cache/database) menjadi bentuk JSON yang diharapkan frontend (**`normalizeDashboardPayload`** di `dashboardData.js`).

---

## 4. Peta register (referensi singkat)

Logika decoding ada di **`python-modbus/modbus/register_map.py`**. Contoh:

- **40001** — uint16 (tekanan masuk).
- **40002–40009** — boolean (alarm, maintenance, roof, pintu, testing, dll.).
- **40011–40012** — float32 (dua register per nilai).

Jika urutan atau tipe di PLC berbeda, **sesuaikan PLC atau file map** agar konsisten.

---

## 5. File dan direktori rujukan cepat

| Area                     | Lokasi                                             |
| ------------------------ | -------------------------------------------------- |
| Rute web                 | `routes/web.php`                                   |
| Rute API                 | `routes/api.php`                                   |
| Webhook & bridge         | `app/Http/Controllers/Api/PlcController.php`       |
| Dashboard API            | `app/Http/Controllers/Api/DashboardController.php` |
| Agregasi dashboard       | `app/Services/PlcDataService.php`                  |
| Event realtime           | `app/Events/PlcRoomUpdated.php`                    |
| Model PLC                | `app/Models/PlcDevice.php`                         |
| Seed data                | `database/seeders/DatabaseSeeder.php`              |
| Entry FastAPI            | `python-modbus/main.py`                            |
| Load device dari Laravel | `python-modbus/modbus/device_loader.py`            |
| Loop polling             | `python-modbus/modbus/poller.py`                   |
| Client Modbus            | `python-modbus/modbus/client.py`                   |
| Map register             | `python-modbus/modbus/register_map.py`             |
| Default PLC statis       | `python-modbus/config.py`                          |
| CV Flask                 | `camera/camera_stream.py`                          |
| CV loader                | `camera/camera_loader.py`                          |
| Model kamera             | `app/Models/RoomCamera.php`                        |
| Dashboard JS             | `resources/js/dashboard/main.js`                   |
| Echo / Pusher            | `resources/js/bootstrap.js`                        |

---

## 6. Alur waktu (ringkas)

1. **Operator** login → buka dashboard → (opsional) ubah PLC di Settings → simpan ke DB.
2. **Python** (setelah start) membaca daftar device → polling **Modbus** periodik.
3. Setiap siklus: baca register → bandingkan dengan state → **POST webhook**.
4. **Laravel** validasi → update DB + cache → **broadcast** (jika dikonfigurasi).
5. **Browser** menerima pembaruan via Echo atau saat memanggil **`/api/plc/.../data`** / reload dashboard.

---

Untuk gambaran produk: **`docs/GAMBARAN_SISTEM.md`**. Untuk instalasi: **`docs/PANDUAN_MENJALANKAN_SISTEM.md`**.
