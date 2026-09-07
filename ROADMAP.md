# Roadmap Penyempurnaan Testing Bay

Dokumen ini merapikan rencana perbaikan agar transisi dari mode simulasi ke mode produksi berjalan aman dan bertahap.

## 1) Security Baseline (prioritas tinggi)

- [x] Tambahkan validasi `PLC_WEBHOOK_SECRET` pada endpoint `POST /api/plc/webhook`.
- [x] Kirim header `X-PLC-Secret` dari service Python (`python-modbus`) jika secret diisi.
- [x] Logout web menghapus token Sanctum aktif.
- [ ] Tambahkan rate-limit khusus webhook via route middleware terpisah (`throttle:plc-webhook`).
- [ ] Tambahkan allowlist IP untuk host PLC bridge (opsional jika infra mendukung).

## 2) Data Integrity PLC

- [ ] Standarisasi register map menjadi satu file referensi (`docs/plc-register-map.md`).
- [ ] Terapkan schema validasi item `changes[*]` (register_address, old/new value, register_name).
- [ ] Tandai `poll_status=error` + simpan `error_message` saat polling gagal.
- [ ] Tambahkan mekanisme dedup jika payload yang sama terkirim berulang.

## 3) Realtime & Frontend

- [ ] Ganti loop random `tickRoom()/tickUtil()` dengan polling API room aktif.
- [ ] Tambahkan fallback UI jika snapshot room kosong (`status: no_data`).
- [x] Integrasikan broadcast (Echo/Pusher) — event `PlcRoomUpdated` + subscribe channel `plc.room.{id}` (isi `VITE_PUSHER_*` + `BROADCAST_CONNECTION=pusher`).
- [ ] Tambahkan badge role di UI untuk membedakan `admin/operator/viewer`.

## 3b) Computer Vision & Kamera

- [x] Tabel `room_cameras` (2 slot per ruang) + seeder + Settings `/settings/cameras`.
- [x] Bridge API `GET /api/cv/bridge-cameras` untuk service Python `camera/`.
- [x] Flask multi-room: RTSP, YOLO, MJPEG `/stream/{room}/{slot}`, `/alarm_status`.
- [x] Dashboard: `VITE_CV_*`, stream per ruang, alarm human di sidebar.
- [x] Mode dummy: feed DEMO, panel Demo CV, alarm contoh + ACK.
- [x] Tulis hasil deteksi CV ke Holding Register 10 PLC (`plc_writer.py` via FC6, pymodbus 2.5.3).
- [x] Register 40010 (`cv_person_detected`) ditangkap webhook Laravel → `AlarmLog` `CV_PERSON_DETECTED`.
- [x] GStreamer pipeline `avdec_h264` (software decoder, kompatibel Hikvision) di `camera_manager.py`.
- [ ] Status koneksi RTSP per kamera di Settings (online/offline).

## 4) Auth & Authorization

- [ ] Tambahkan auto-expire token + kebijakan rotasi token.
- [ ] Pastikan semua endpoint mutasi memakai middleware role eksplisit.
- [ ] Tambahkan audit log untuk aksi admin (`create/update/delete/toggle user`, `ack/resolve alarm`).

## 5) Observability & Ops

- [ ] Dashboard health untuk Laravel + Python bridge + koneksi PLC.
- [ ] Structured logging (`request_id`, `room_id`, `poll_status`) di webhook.
- [ ] Job pruning berkala untuk `plc_snapshots` dan `plc_register_logs` (retensi data).
- [ ] Backup strategy untuk database produksi.

## 6) Testing & CI

- [ ] Feature test: webhook valid/invalid secret.
- [ ] Feature test: `room.access` untuk admin/operator/viewer.
- [ ] Feature test: `AlarmController` ack/resolve permissions.
- [ ] Unit test: mapping payload `PlcDataService`.

## 7) Deployment Plan Bertahap

1. Stabilkan security baseline + test.
2. Aktifkan polling PLC nyata di Python.
3. Migrasi UI dari dummy/random ke API room aktif.
4. Aktifkan broadcast realtime.
5. Terapkan pruning + monitoring produksi.
