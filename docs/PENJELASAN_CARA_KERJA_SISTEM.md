# Penjelasan Cara Kerja Sistem Testing Bay (tampilan & internals)

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
  subgraph laravel [Laravel — Server PC]
    Web[Web Controllers + Blade]
    API[API + Sanctum]
    DB[(SQL Server)]
    Cache[(Cache)]
    BC[Broadcast / Echo]
  end
  subgraph python [python-modbus — Server PC]
    FA[FastAPI main.py]
    POL[poller.py]
    CLI[client.py Modbus TCP]
  end
  subgraph jetson [camera — Jetson Nano P3450]
    FL[camera_stream.py]
    YOLO[yolo_detector.py]
    PLCw[plc_writer.py FC6]
  end
  PLC[PLC Schneider M221 — 192.168.1.4:502]
  RTSP[Hikvision RTSP]

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
  YOLO -->|deteksi orang| PLCw
  PLCw -->|FC6 Holding Reg 10| PLC
  UI -->|stream / alarm_status| FL
```

- **Parameter PLC** disimpan di tabel **`plc_devices`** (Settings → PLC configuration).
- **Parameter kamera RTSP** disimpan di **`room_cameras`** — 2 slot per ruang (Settings → Camera configuration).
- **python-modbus** memuat PLC enabled lewat **`GET /api/plc/bridge-devices`**; fallback `config.py`.
- **camera/** memuat RTSP enabled lewat **`GET /api/cv/bridge-cameras`** (token sama dengan `PLC_BRIDGE_TOKEN`).
- **plc_writer.py** menulis hasil deteksi ke Holding Register 10 (`%MW9`, address 40010) via Modbus TCP FC6.

---

## 2. Sisi luar: alur pengguna (start → finish)

### 2.1 Masuk ke sistem

1. Pengguna membuka URL aplikasi (mis. `http://127.0.0.1:8000` atau IP server).
2. Jika belum login, middleware Laravel mengarahkan ke **`/login`**.
3. Form login memposting kredensial; setelah sukses, sesi web aktif dan pengguna diarahkan ke **dashboard** (`/`).

**File terkait (web):**

- `routes/web.php` — rute `login`, `logout`, `/`, `/settings/plc`, `/settings/cameras`.
- `app/Http/Controllers/Web/Guest/AuthController.php` — proses login/logout web.

### 2.2 Dashboard (tampilan utama)

1. Rute **`/`** memuat halaman dashboard (Blade + aset Vite).
2. JavaScript memuat data agregat ruang lewat **API** (`GET /api/dashboard`) menggunakan token **Laravel Sanctum**.

**Perilaku UI utama:**

- Pengguna memilih **control room** dan **testing room** (pit/cell).
- Panel kanan menampilkan status, log peristiwa, kamera, dan visualisasi Three.js.
- Jika `VITE_PUSHER_APP_KEY` (dan konfigurasi Echo) aktif, frontend berlangganan channel **`plc.room.{roomId}`** → event **`.PlcRoomUpdated`** memperbarui data real-time tanpa reload.

**File terkait (frontend):**

- `resources/views/shared/dashboard.blade.php` — kerangka HTML.
- `resources/js/dashboard/main.js` — tab, ruang, kamera/CV, `bindPlcEchoForRoom`, alarm gabungan.
- `resources/js/dashboard/demoCvControls.js` / `demoRoofControls.js` — panel demo (dummy mode).
- `resources/js/dashboard/init.js` — inisialisasi Three.js / peta.
- `resources/js/services/dashboardData.js` — `getDashboardData()`, `PlcAPI.getRoomData`.
- `resources/js/services/authService.js` — token / pemanggilan API.

### 2.3 Settings — PLC dan kamera

**PLC (`/settings/plc`):**

1. Kartu per device: IP, port, unit ID, enable polling.
2. **PUT** ke Laravel → tabel **`plc_devices`**.
3. python-modbus refresh map ~15 detik jika `LARAVEL_BRIDGE_DEVICES_URL` diisi.

**Kamera (`/settings/cameras`):**

1. Dua slot RTSP per ruang uji: host, port, path, user/password, enable stream.
2. **PUT** per slot → tabel **`room_cameras`**.
3. Jetson Nano refresh ~15 detik lewat `LARAVEL_BRIDGE_CAMERAS_URL`.

Untuk kamera Hikvision, format URL di database:
```
rtsp://admin:password@192.168.1.64:554/Streaming/Channels/102
```

**File terkait:**

- `app/Http/Controllers/Web/Operator/PlcConnectionController.php`
- `app/Http/Controllers/Web/Operator/CameraConnectionController.php`
- `resources/views/operator/plc-connection.blade.php`
- `resources/views/operator/camera-connection.blade.php`

### 2.3b Kamera di dashboard & mode dummy

- **Live:** `VITE_CV_ENABLED=true` → stream `{VITE_CV_BASE_URL}/stream/{room_id}/{slot}` dari Jetson (port 5001); alarm human di sidebar.
- **Dummy:** `VITE_DASHBOARD_USE_DUMMY=true` → feed DEMO, alarm contoh (Test Cell 1), panel **Demo CV** (admin).

### 2.4 Peran operator vs admin

- **Middleware `role`** membatasi aksi admin-only (mis. alarm acknowledge, manage users).
- **Middleware `room.access`** membatasi akses data ruang sesuai assignment user.

---

## 3. Sisi dalam: rantai data PLC (start → finish)

### 3.1 Konfigurasi device di database

- Model **`App\Models\PlcDevice`** merepresentasikan satu PLC per **`testing_room_id`**.
- Field penting: `ip_address`, `port`, `unit_id`, `is_enabled`, `status`, `last_seen_at`.
- PLC Schneider M221 TM221CE24R: IP 192.168.1.4, port 502, unit_id 1.

### 3.2 Startup bridge Python

1. **`python-modbus/main.py`** memuat **`.env`** dari folder `python-modbus`.
2. Jika **`ENABLE_POLLING`** aktif, pada lifespan FastAPI:
    - **`load_plc_devices_sync()`** (`modbus/device_loader.py`): HTTP GET ke Laravel → JSON `{ ok, devices: { "1": { ip, port, unit_id, name }, ... } }` hanya untuk `is_enabled=true`.
    - Fallback ke `config.PLC_DEVICES` jika URL kosong atau gagal.
    - **`start_polling()`** memulai loop async.

### 3.3 Polling Modbus

1. **`poll_all()`** memanggil **`poll_single(room_id)`** untuk setiap PLC secara paralel.
2. **`read_plc_registers`** (`modbus/client.py`):
    - Membuka `AsyncModbusTcpClient` ke `plc_config["ip"]` dan port 502.
    - Membaca **holding registers** mulai **PDU address 0** sebanyak **14 word** (REGISTER_COUNT=14) dengan unit_id=1.
    - Memetakan ke alamat logis **40001–40012 + 40010 CV** lewat **`REGISTER_MAP`** di `modbus/register_map.py`.
3. Hasil per ruang: status `success` / `timeout` / `error`, plus `data` berisi nilai ter-decode per register.

### 3.4 Register Map (ringkasan)

| Address | PDU | Nama            | Tipe    | Keterangan                                               |
| ------- | --- | --------------- | ------- | -------------------------------------------------------- |
| 40001   | 0   | `auto_mode`     | bool    | Mode otomatis panel                                      |
| 40002   | 1   | `alarm_alert`   | bool    | Alarm darurat / emergency                                |
| ...     | ... | ...             | ...     | Lihat `python-modbus/modbus/register_map.py`             |
| 40010   | 9   | `cv_person_detected` | bool | CV: ditulis Jetson Nano via FC6 (Holding Register 10 = %MW9) |
| 40011   | 10–11 | `pressure_1`  | float32 | Tekanan 1 (2 word)                                       |
| 40012   | 12–13 | `pressure_2`  | float32 | Tekanan 2 (2 word)                                       |

Total: **14 word** (PDU 0–13).

### 3.5 Deteksi perubahan dan webhook

1. **`detect_changes`** (`poller.py`) membandingkan snapshot register dengan state sebelumnya.
2. **`send_to_laravel`** mengirim **POST** ke **`LARAVEL_WEBHOOK_URL`** dengan JSON:
    - `room_id`, `polled_at`, `status`, `snapshot`, `changes`, `error`.
3. Header **`X-PLC-Secret`** ikut dikirim jika `PLC_WEBHOOK_SECRET` diisi.

### 3.6 Pemrosesan webhook di Laravel

**Rute:** `POST /api/plc/webhook` — **`App\Http\Controllers\Api\PlcController::webhook`**

Urutan logika:

1. Validasi opsional `X-PLC-Secret`.
2. Validasi payload (`room_id`, `changes`, `snapshot`, `status`, `polled_at`).
3. Cari `TestingRoom` dan `PlcDevice` untuk `room_id`.
4. Update `plc_devices`: `status`, `last_seen_at`.
5. Tulis **cache** `plc_snapshot_{roomId}` (TTL 30 detik).
6. **Broadcast** event `PlcRoomUpdated` (channel `plc.room.{id}`).
7. Simpan baris `plc_snapshots`.
8. Untuk setiap item di `changes`, simpan `plc_register_logs`.
9. Khusus **register 40002** (`alarm_alert`): buat/resolve `AlarmLog` dengan kode `ALARM_ALERT`.
10. Khusus **register 40010** (`cv_person_detected`): buat/resolve `AlarmLog` dengan kode `CV_PERSON_DETECTED`.

### 3.7 Alur CV → PLC → Dashboard (skema hybrid)

```
Jetson Nano: deteksi orang → plc_writer.py → FC6 Write → PLC %MW9 (Holding Reg 10)
                                                           ↓
python-modbus: read FC3 register 40010 → webhook POST → Laravel PlcController
                                                           ↓
                                              AlarmLog CV_PERSON_DETECTED → dashboard alarm
```

Jalur langsung `/alarm_status` dari Jetson ke browser tetap aktif bersamaan (skema hybrid, latensi <1 detik).

### 3.8 API yang dibaca frontend

| Metode | Path                                | Fungsi                                                  |
| ------ | ----------------------------------- | ------------------------------------------------------- |
| GET    | `/api/dashboard`                    | Payload dashboard untuk user yang login                 |
| GET    | `/api/plc/{room_id}/data`           | Snapshot cache terakhir untuk satu ruang                |
| GET    | `/api/plc/{room_id}/logs`           | Riwayat perubahan register                              |
| GET    | `/api/plc/bridge-devices?token=...` | Daftar PLC untuk python-modbus                          |
| GET    | `/api/cv/bridge-cameras?token=...`  | Daftar RTSP enabled untuk Jetson                        |
| POST   | `/api/plc/webhook`                  | Masukan polling dari python-modbus                      |

---

## 4. Alur CV (Jetson Nano)

```
RTSP Hikvision → rtspsrc (GStreamer) → avdec_h264 (software H.264 decode) → frame BGR
      ↓
  yolo_detector.py (YOLOv8n, cuda:0, FP16)
      ↓
  person_detected = True / False
      ↓ (parallel)
  ┌──────────────────────────────┐   ┌─────────────────────────────────────┐
  │ /alarm_status JSON (1 detik) │   │ plc_writer.py FC6 → PLC %MW9 (40010)│
  │ → polling browser dashboard  │   │ → python-modbus → webhook → AlarmLog │
  └──────────────────────────────┘   └─────────────────────────────────────┘
```

GStreamer pipeline (`gst_rtsp` mode):
```
rtspsrc latency=0 → rtph264depay → h264parse → avdec_h264 → videoconvert → BGR → appsink
```
`avdec_h264` adalah software decoder (bukan `nvv4l2decoder` hardware) — lebih kompatibel dengan stream Hikvision.

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
| CV Flask (Jetson)        | `camera/camera_stream.py`                          |
| CV loader                | `camera/camera_loader.py`                          |
| YOLO detector            | `camera/yolo_detector.py`                          |
| PLC writer (CV→PLC)      | `camera/plc_writer.py`                             |
| Model kamera             | `app/Models/RoomCamera.php`                        |
| Dashboard JS             | `resources/js/dashboard/main.js`                   |
| Echo / Pusher            | `resources/js/bootstrap.js`                        |

---

## 6. Alur waktu (ringkas)

1. **Operator** login → buka dashboard → (opsional) ubah PLC di Settings → simpan ke DB.
2. **python-modbus** (PC server) membaca daftar device → polling **Modbus TCP** periodik tiap 5 detik.
3. Setiap siklus: baca 14 register → bandingkan dengan state → **POST webhook**.
4. **Laravel** validasi → update DB + cache → **broadcast** (jika dikonfigurasi).
5. **Browser** menerima via Echo atau saat memanggil `/api/plc/.../data`.
6. **Jetson Nano** secara paralel: stream RTSP → YOLO deteksi → alarm via `/alarm_status` + tulis ke PLC register 40010.

---

Untuk gambaran produk: **`docs/GAMBARAN_SISTEM.md`**. Untuk instalasi: **`docs/PANDUAN_MENJALANKAN_SISTEM.md`**. Untuk panduan CV detail: **`camera/PANDUAN_CV.md`**.
