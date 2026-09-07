# Proposal Perangkat Lunak
# Testing Bay Monitoring Dashboard

---

**Nama Sistem** : Testing Bay Monitoring Dashboard  
**Jenis Sistem** : Web-based SCADA Dashboard  
**Platform**     : Browser — LAN / Intranet  
**Pengguna**     : Administrator · Operator  
**Versi Dokumen**: 1.0  
**Tanggal**      : Agustus 2026  

---

## Daftar Isi

1. [Latar Belakang](#1-latar-belakang)
2. [Tujuan Sistem](#2-tujuan-sistem)
3. [Fungsi Utama Sistem](#3-fungsi-utama-sistem)
4. [Data dan Informasi yang Ditampilkan](#4-data-dan-informasi-yang-ditampilkan)
5. [Peran Pengguna](#5-peran-pengguna)
6. [Alur Operasional dari Sisi Pengguna](#6-alur-operasional-dari-sisi-pengguna)
7. [Komponen Sistem](#7-komponen-sistem)
8. [Spesifikasi Teknis Ringkas](#8-spesifikasi-teknis-ringkas)

---

## 1. Latar Belakang

Fasilitas **Testing Bay SPM Oil & Gas** memiliki beberapa ruang pengujian yang terdiri dari **Test Pit** dan **Test Cell**. Setiap ruang dilengkapi dengan **PLC (Programmable Logic Controller) Schneider M221** sebagai pengendali mesin dan peralatan — mengatur tekanan, posisi atap hidrolik, sistem pintu, serta indikator darurat.

Sebelum sistem ini dibangun, kondisi operasional setiap ruang hanya dapat dipantau secara langsung di depan panel PLC masing-masing. Hal ini menyebabkan beberapa kendala:

- Operator tidak dapat memantau lebih dari satu ruang secara bersamaan
- Tidak ada pencatatan otomatis riwayat perubahan kondisi mesin
- Kejadian alarm dan darurat tidak terdokumentasi secara sistematis
- Tidak ada cara untuk memantau keberadaan orang di area pengujian dari jarak jauh

Sistem ini dibangun untuk menjawab seluruh kendala tersebut melalui sebuah antarmuka web terpusat yang dapat diakses dari mana saja di jaringan internal fasilitas.

---

## 2. Tujuan Sistem

| No | Tujuan |
|----|--------|
| 1 | Menyediakan tampilan terpusat untuk memantau semua ruang uji secara bersamaan dari satu browser |
| 2 | Menampilkan data kondisi PLC secara real-time (interval 1 detik) tanpa perlu refresh manual |
| 3 | Merekam seluruh perubahan kondisi dan kejadian alarm secara otomatis ke database |
| 4 | Mengintegrasikan feed kamera live beserta deteksi keberadaan manusia berbasis Computer Vision |
| 5 | Menyediakan sistem alarm yang memberi notifikasi segera kepada operator saat kondisi berbahaya terdeteksi |
| 6 | Memberikan kontrol akses berbasis peran sehingga setiap pengguna hanya mengakses data ruang yang menjadi tanggung jawabnya |

---

## 3. Fungsi Utama Sistem

### 3.1 Pemantauan PLC Real-time (Modbus TCP)

Sistem membaca data dari PLC di setiap ruang uji secara otomatis menggunakan protokol **Modbus TCP** melalui layanan `python-modbus` yang berjalan di background. Data dibaca setiap **1 detik** dan langsung diteruskan ke server web untuk ditampilkan di dashboard. Jika ada nilai register yang berubah, sistem mencatatnya ke riwayat secara permanen.

Register yang dibaca mencakup: tekanan (2 sensor), posisi atap (4 register), mode panel, status pengujian, status pintu, status emergency, dan status deteksi kamera CV.

### 3.2 Pemantauan Kamera Live & Deteksi Manusia (Computer Vision)

Setiap ruang uji dapat dikonfigurasi dengan hingga **2 slot kamera CCTV**. Kamera IP Hikvision terhubung ke **Jetson Nano P3450** yang menjalankan program deteksi manusia berbasis AI (YOLOv8). Hasil stream video dikirim dalam format MJPEG melalui HTTP dan ditampilkan langsung di panel detail ruang.

Ketika kamera mendeteksi keberadaan orang di area pengujian, sistem otomatis:
1. Menulis ke register PLC 40010
2. Memunculkan alarm `CV_PERSON_DETECTED` di database
3. Menampilkan notifikasi **"MANUSIA TERDETEKSI"** di sidebar alarm dashboard

### 3.3 Sistem Alarm

Sistem memiliki dua sumber alarm yang diproses dan ditampilkan di sidebar kiri dashboard:

| Sumber Alarm | Kode | Keterangan |
|---|---|---|
| Register PLC 40002 (Emergency) | `ALARM_ALERT` | Alarm darurat dari PLC aktif |
| Register PLC 40010 (Camera CV) | `CV_PERSON_DETECTED` | Kamera mendeteksi keberadaan manusia di area |

Setiap alarm memiliki siklus lengkap: `active` → `acknowledged` → `resolved`, dan seluruh riwayat tersimpan permanen di database.

### 3.4 Pencatatan Data Otomatis (Data Logging)

Semua perubahan kondisi dan kejadian alarm dicatat ke tiga tabel database:

| Tabel | Isi | Kapan Ditulis |
|---|---|---|
| `plc_register_logs` | Nilai register sebelum dan sesudah perubahan, dengan cap waktu | Setiap kali ada register yang berubah nilainya |
| `alarm_logs` | Riwayat lengkap siklus alarm (aktif, acknowledged, resolved) | Setiap perubahan status alarm |
| `plc_snapshots` | Snapshot seluruh register satu ruang pada satu waktu | Setiap polling (1 detik sekali) |

Data ini dapat digunakan untuk keperluan audit, pelaporan kejadian, atau analisis tren kondisi mesin.

### 3.5 Visualisasi Peta 3D Fasilitas

Dashboard menampilkan denah tiga dimensi interaktif seluruh fasilitas testing bay menggunakan teknologi WebGL (Three.js). Pengguna dapat mengklik bangunan Test Pit atau Test Cell langsung dari peta 3D untuk membuka panel detail ruang tersebut.

### 3.6 Konfigurasi Mandiri oleh Pengguna

Pengaturan perangkat dapat dilakukan langsung dari browser tanpa perlu mengubah kode program:

- **Konfigurasi PLC**: IP address, port, unit ID per ruang
- **Konfigurasi Kamera**: URL stream HTTP per slot kamera (1–2 slot per ruang), aktif/nonaktif per slot

---

## 4. Data dan Informasi yang Ditampilkan

Berikut seluruh data yang ditampilkan di dashboard saat pengguna memilih sebuah ruang uji:

| No | Data / Informasi | Sumber | Keterangan |
|----|---|---|---|
| 1 | **Status Koneksi PLC** | Modbus TCP | Online / Offline / Error |
| 2 | **Tekanan 1** | Register 40011 | Nilai sensor tekanan pertama dalam PSI |
| 3 | **Tekanan 2** | Register 40012 | Nilai sensor tekanan kedua dalam PSI |
| 4 | **Posisi Atap** | Register 40004–40007 | Open / Close / Moving Open / Moving Close |
| 5 | **Mode Panel** | Register 40003 | Auto (normal) / Maintenance (perawatan) |
| 6 | **Status Pengujian** | Register 40009 | Running (berjalan) / Standby (siaga) |
| 7 | **Status Pintu** | Register 40008 | Locked / Unlocked |
| 8 | **Status Emergency** | Register 40002 | ON / OFF — alarm darurat aktif atau tidak |
| 9 | **Feed Kamera Live** | Jetson Nano (HTTP MJPEG) | Stream video langsung dari kamera CCTV (1–2 kamera/ruang) |
| 10 | **Alarm Aktif Ruang** | `alarm_logs` DB | Daftar alarm yang sedang aktif di ruang tersebut |
| 11 | **Notifikasi Manusia Terdeteksi** | CV + Register 40010 | "MANUSIA TERDETEKSI" saat kamera CV mendeteksi orang |
| 12 | **Event Log** | `plc_register_logs` DB | 8 perubahan register PLC terakhir dengan waktu dan deskripsi |
| 13 | **Peta 3D Fasilitas** | Three.js (WebGL) | Denah interaktif seluruh Test Pit dan Test Cell |

> **Catatan**: Semua data diperbarui otomatis setiap **1 detik** tanpa perlu refresh halaman.

---

## 5. Peran Pengguna

Sistem memiliki dua peran dengan hak akses yang berbeda:

### Administrator

- Akses penuh ke semua Control Room dan seluruh ruang uji
- Kelola akun pengguna (tambah, edit, nonaktifkan)
- Konfigurasi PLC: IP address, port, unit ID per ruang
- Konfigurasi kamera: URL stream per slot, aktif/nonaktif
- Lihat semua data log, alarm, dan riwayat perubahan register
- Akses mode demo untuk keperluan presentasi atau pengujian tampilan

### Operator

- Akses hanya ke ruang uji yang ditugaskan (berdasarkan Control Room atau Testing Room spesifik)
- Pantau status PLC ruang sendiri secara real-time
- Lihat feed kamera live ruang sendiri
- Terima notifikasi alarm dari ruang yang menjadi tanggung jawabnya
- Konfigurasi kamera dan PLC untuk ruang sendiri
- Lihat event log (riwayat perubahan) ruang sendiri

---

## 6. Alur Operasional dari Sisi Pengguna

Berikut tahapan yang dilalui pengguna saat menggunakan sistem, dari membuka browser hingga menangani alarm:

```
┌─────────────────────────────────────────────────────────┐
│  LANGKAH 1 — Akses Sistem                               │
│  Buka browser → ketik URL server di jaringan internal   │
│  (tidak perlu instalasi aplikasi apapun)                 │
└──────────────────────┬──────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────┐
│  LANGKAH 2 — Login                                      │
│  Masukkan username & password                           │
│  Sistem mengarahkan ke dashboard sesuai peran           │
└──────────────────────┬──────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────┐
│  LANGKAH 3 — Dashboard Utama                            │
│                                                         │
│  ┌─────────────────┐  ┌───────────────────────────────┐ │
│  │  Sidebar Kiri   │  │   Area Tengah — Peta 3D       │ │
│  │                 │  │                               │ │
│  │ • Daftar ruang  │  │  Visualisasi 3D seluruh       │ │
│  │   uji aktif     │  │  Test Pit & Test Cell         │ │
│  │ • Active Alarms │  │                               │ │
│  │   (semua ruang) │  │  Klik bangunan untuk          │ │
│  │                 │  │  membuka detail ruang         │ │
│  └─────────────────┘  └───────────────────────────────┘ │
└──────────────────────┬──────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────┐
│  LANGKAH 4 — Pilih Ruang Uji                            │
│  Klik nama ruang di sidebar kiri                        │
│  — atau —                                               │
│  Klik bangunan di peta 3D                               │
└──────────────────────┬──────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────┐
│  LANGKAH 5 — Panel Detail Ruang (sisi kanan layar)      │
│                                                         │
│  ┌─────────────────────┐  ┌──────────────────────────┐  │
│  │   Tab: Overview     │  │   Tab: Event Log         │  │
│  │                     │  │                          │  │
│  │ • Feed Kamera Live  │  │ • 8 perubahan register   │  │
│  │   (1–2 kamera)      │  │   PLC terakhir           │  │
│  │ • Tekanan 1 & 2     │  │ • Waktu kejadian         │  │
│  │ • Posisi Atap       │  │ • Deskripsi perubahan    │  │
│  │ • Mode Panel        │  │                          │  │
│  │ • Status Pengujian  │  └──────────────────────────┘  │
│  │ • Status Pintu      │                                │
│  │ • Emergency Status  │                                │
│  │ • Alarm Ruang Ini   │                                │
│  │ • Badge PLC Status  │                                │
│  └─────────────────────┘                                │
└──────────────────────┬──────────────────────────────────┘
                       │
          ┌────────────┴────────────┐
          │                         │
          ▼                         ▼
┌──────────────────┐   ┌──────────────────────────────────┐
│  Kondisi Normal  │   │  Kondisi Alarm                   │
│                  │   │                                  │
│ • Pantau data    │   │ • Alarm muncul otomatis          │
│   PLC secara     │   │   di sidebar kiri                │
│   berkala        │   │ • Buka ruang yang bersangkutan   │
│ • Lihat kamera   │   │ • Cek detail di panel kanan      │
│   live           │   │ • Lakukan tindakan lapangan      │
│ • Baca event log │   │                                  │
└──────────────────┘   └──────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────┐
│  LANGKAH 6 — Konfigurasi (Admin / Operator)             │
│                                                         │
│  Menu Settings (klik ikon profil kanan atas):           │
│  • PLC Configuration — ubah IP, port, unit ID          │
│  • Camera Configuration — atur URL stream per slot      │
└──────────────────────┬──────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────┐
│  LANGKAH 7 — Selesai / Logout                           │
│  Klik profil → Sign out                                 │
└─────────────────────────────────────────────────────────┘
```

### Catatan Alarm Otomatis

Sistem memantau PLC di latar belakang secara terus-menerus. Ketika kondisi berbahaya terdeteksi — alarm emergency aktif, atau kamera CV mendeteksi orang — notifikasi muncul **otomatis di sidebar kiri** tanpa operator perlu melakukan apapun atau me-refresh halaman.

---

## 7. Komponen Sistem

| Komponen | Fungsi (dari sisi pengguna) | Lokasi |
|---|---|---|
| **Aplikasi Web Dashboard** | Tampilan utama yang dibuka melalui browser — peta 3D, panel detail ruang, alarm, settings | PC Server |
| **Bridge Modbus (`python-modbus`)** | Membaca data dari PLC setiap 1 detik dan mengirimkannya ke sistem web — berjalan otomatis di background | PC Server |
| **PLC Schneider M221** | Pengendali mesin di lapangan — menyimpan semua data kondisi (tekanan, atap, alarm, dll.) | Panel Lapangan |
| **Kamera IP Hikvision** | Kamera CCTV yang mengambil gambar area pengujian via RTSP | Area Pengujian |
| **Jetson Nano P3450** | Komputer kecil yang memproses video secara real-time, mendeteksi keberadaan manusia (YOLOv8), dan mengirim stream MJPEG ke dashboard | Area Pengujian |
| **Database SQL Server** | Menyimpan semua log, alarm, konfigurasi, dan akun pengguna secara permanen | PC Server |

---

## 8. Spesifikasi Teknis Ringkas

| Aspek | Detail |
|---|---|
| **Framework Backend** | Laravel 12 (PHP) |
| **Frontend** | Blade Template + Vanilla JavaScript + Vite |
| **Visualisasi 3D** | Three.js (WebGL) |
| **Protokol PLC** | Modbus TCP |
| **Library Modbus** | pymodbus 3.14.0 (Python) |
| **Database** | Microsoft SQL Server |
| **Kamera Stream** | MJPEG over HTTP (port 5001) dari Jetson Nano |
| **Deteksi Manusia** | YOLOv8 (Ultralytics) — berjalan di Jetson Nano JetPack 4.6 |
| **Interval Polling PLC** | 1 detik (dapat diatur via environment variable `POLL_INTERVAL_SEC`) |
| **Akses** | Browser di jaringan LAN/Intranet — tidak memerlukan koneksi internet |
| **Jumlah Ruang Uji** | 6 Test Pit + 5 Test Cell = 11 ruang |
| **Kamera per Ruang** | Hingga 2 slot kamera per ruang (22 slot total) |

---

*Dokumen ini menggambarkan sistem Testing Bay Monitoring Dashboard versi 1.0 — Agustus 2026.*
