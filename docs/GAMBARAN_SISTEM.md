# Gambaran Sistem — Testing Bay Dashboard

Dokumen ini menjelaskan **apa yang dilakukan sistem**, **fitur utama**, dan **peran pengguna** dalam bahasa yang mudah dipahami tim operasional maupun pengembang. Untuk instalasi teknis, lihat [`PANDUAN_MENJALANKAN_SISTEM.md`](PANDUAN_MENJALANKAN_SISTEM.md).

---

## Apa yang dilakukan sistem ini

Repositori ini berisi **sistem dan dashboard monitoring** untuk area **testing bay**: pengawas melihat kondisi tiap **test pit** dan **test cell** dari browser, tanpa harus berdiri di depan panel PLC.

Aplikasi web dibangun dengan **Laravel** (backend + API) dan **JavaScript/Vite** (tampilan dashboard). Data dari PLC di lapangan masuk ke server melalui **jembatan polling** (`python-modbus/`) yang mengirim hasil baca Modbus ke aplikasi. **Kamera CCTV** (opsional) diproses oleh **Jetson Nano** yang menjalankan layanan **Computer Vision** (`camera/`) dengan deteksi manusia YOLOv8.

---

## Ringkasan kemampuan

- Menyatukan tampilan **beberapa control room** dan **ruang uji** (pit/cell) dalam satu halaman utama.
- Menampilkan **status koneksi PLC** per ruang (online/offline/error).
- Memuat **tekanan**, **posisi atap**, **mode panel**, **status uji**, **pintu**, dan **kondisi darurat** dari register PLC.
- Menampilkan **alarm aktif** (darurat, tekanan, motor, deteksi manusia CV) di sidebar.
- **Dua kamera RTSP per ruang** — konfigurasi di Settings → Camera configuration; stream MJPEG + alarm human dari Jetson Nano.
- **Log peristiwa** ringkas per ruang di panel detail.
- Pembaruan data berkala dari API dan, bila dikonfigurasi, **realtime** lewat Pusher/Echo.
- **Mode dummy** (`VITE_DASHBOARD_USE_DUMMY=true`): uji UI tanpa PLC/CV live — panel **Demo roof** dan **Demo CV** (admin).

---

## Fitur utama (sisi pengguna)

| Area                | Fitur                                                                                                     |
| ------------------- | --------------------------------------------------------------------------------------------------------- |
| **Dashboard**       | Peta fasilitas **3D**; pemilihan ruang; panel kanan: kamera, tekanan, atap, status operasi, alarm ruangan |
| **Navigasi**        | **Admin**: hierarki control room + daftar ruang. **Operator/viewer**: daftar ruang sesuai hak akses       |
| **Settings PLC**    | IP, port, unit ID, enable polling per ruang (`/settings/plc`)                                             |
| **Settings kamera** | RTSP host/port/path, kredensial, enable stream per slot kamera (`/settings/cameras`)                      |
| **Akun**            | Login/logout; ganti password; admin mengelola operator dan log aktivitas                                  |
| **API**             | Endpoint Sanctum: dashboard, PLC, alarm, log; bridge token untuk Python                                   |

---

## Computer Vision (CV)

Layanan CV berjalan di **Jetson Nano P3450** (JetPack 4.6) yang terhubung ke jaringan LAN yang sama dengan server dan PLC.

| Mode      | Kondisi                                                               | Perilaku                                                                         |
| --------- | --------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| **Live**  | `VITE_CV_ENABLED=true` + Flask `camera/` jalan di Jetson (port 5001) | Stream `/stream/{room_id}/{slot}`, alarm dari `/alarm_status`                    |
| **Dummy** | `VITE_DASHBOARD_USE_DUMMY=true`                                       | Feed demo di UI, alarm human contoh (Test Cell 1 / Kamera 1), panel **Demo CV** |
| **Off**   | Keduanya false                                                        | Kotak "CV disabled" di panel kamera                                              |

Konfigurasi RTSP disimpan di tabel **`room_cameras`** (2 slot per `testing_room`). Jetson memuat daftar enabled lewat `GET /api/cv/bridge-cameras?token=` (token sama dengan `PLC_BRIDGE_TOKEN`).

### Skema hybrid: CV → PLC → Dashboard

Selain jalur langsung `/alarm_status`, deteksi orang juga ditulis ke **Holding Register 10** PLC (`%MW9`, address 40010) via Modbus TCP FC6 (`plc_writer.py`, pymodbus 2.5.3). Register ini dibaca `python-modbus`, dikirim ke webhook Laravel, dan dicatat sebagai alarm `CV_PERSON_DETECTED` di `alarm_logs`.

---

## Peran pengguna

- **Admin** — seluruh area; kelola akun; Settings PLC & kamera; panel demo (dummy mode).
- **Operator** — ruang yang ditugaskan; Settings PLC & kamera untuk ruang/control room sendiri.
- **Viewer** — hanya memantau; tidak membuka settings.

---

## Isi repositori (gambaran)

| Folder                          | Fungsi                                                         |
| ------------------------------- | -------------------------------------------------------------- |
| `app/`, `routes/`, `resources/` | Aplikasi web Laravel + dashboard Vite                          |
| `python-modbus/`                | Polling Modbus TCP → webhook Laravel (jalan di server PC)      |
| `camera/`                       | Flask: RTSP multi-room, YOLO, MJPEG stream (deploy ke Jetson) |
| `docs/`                         | Panduan instalasi, arsitektur, indeks file                     |

---

## Menyiapkan lingkungan (ringkas)

1. Salin `.env` dari `.env.example`; `php artisan key:generate`; `php artisan migrate`; `php artisan db:seed`.
2. `composer install` dan `npm install`.
3. Development: `composer run dev` (Laravel + Vite + queue + logs).
4. PLC bridge: konfigurasi `python-modbus/.env` + `uvicorn`.
5. CV (opsional, di Jetson Nano): buat `camera/.env` dari `.env.example`, `pip install -r requirements.txt`, `python camera_stream.py`.
6. Dummy UI saja: `VITE_DASHBOARD_USE_DUMMY=true` — tidak perlu PLC/CV.

Detail lengkap: [`PANDUAN_MENJALANKAN_SISTEM.md`](PANDUAN_MENJALANKAN_SISTEM.md).

---

## Dokumentasi teknis

| File                                                                           | Isi                                              |
| ------------------------------------------------------------------------------ | ------------------------------------------------ |
| [`SISTEM_CARA_KERJA_DAN_INDEKS_FILE.md`](SISTEM_CARA_KERJA_DAN_INDEKS_FILE.md) | Arsitektur, login, PLC, CV, diagram, indeks file |
| [`PANDUAN_MENJALANKAN_SISTEM.md`](PANDUAN_MENJALANKAN_SISTEM.md)               | Instalasi langkah demi langkah                   |
| [`PENJELASAN_CARA_KERJA_SISTEM.md`](PENJELASAN_CARA_KERJA_SISTEM.md)           | Alur pengguna dan rantai kode                    |
| [`../camera/PANDUAN_CV.md`](../camera/PANDUAN_CV.md)                           | Panduan lengkap CV service (Jetson Nano)         |
| [`../ROADMAP.md`](../ROADMAP.md)                                               | Rencana penyempurnaan                            |

---

## Uji & gaya kode PHP

```bash
composer test
vendor/bin/pint
```

---

## Lisensi

Kerangka aplikasi mengikuti lisensi **Laravel** ([MIT](https://opensource.org/licenses/MIT)). Lisensi produk lengkap mengikuti kebijakan pemilik repositori.
