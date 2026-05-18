# Sistem / dashboard monitoring pada testing bay

Repositori ini berisi **sistem dan dashboard monitoring** untuk area **testing bay**: pengawas melihat kondisi tiap **test pit** dan **test cell** dari browser, tanpa harus berdiri di depan panel PLC.

Aplikasi web dibangun dengan **Laravel** (backend + API) dan **JavaScript/Vite** (tampilan dashboard). Data dari PLC di lapangan masuk ke server melalui **jembatan polling** (service Python di folder `python-modbus/`) yang mengirim hasil baca ke aplikasi.

---

## Apa yang dilakukan sistem ini

- Menyatukan tampilan **beberapa control room** dan **ruang uji** (pit/cell) dalam satu halaman utama.
- Menampilkan **status koneksi** per ruang terhadap perangkat PLC (online/offline) agar operator tahu apakah angka di layar masih hidup dari lapangan.
- Memuat **tekanan**, **posisi atap**, **mode panel**, **status uji**, **pintu**, dan **kondisi darurat** sesuai data yang diterima dari PLC (detail register dan pemetaannya ada di dokumentasi teknis, bukan di README ini).
- Menampilkan **alarm aktif** (termasuk darurat dan gangguan motor) di sidebar agar cepat terlihat.
- Menyediakan **log peristiwa** ringkas per ruang di panel detail.
- Mendukung **pembaruan data secara berkala** dari server dan, bila dikonfigurasi, **pembaruan hampir realtime** lewat saluran broadcast ke browser.

---

## Fitur utama (sisi pengguna)

| Area | Fitur |
|------|--------|
| **Dashboard** | Peta fasilitas **3D**; pemilihan ruang; ringkasan status; panel kanan dengan ringkasan tekanan, atap, status operasi, dan alarm ruangan |
| **Navigasi** | **Admin**: hierarki control room lalu daftar ruang. **Operator / viewer**: daftar ruang uji saja (diurutkan nama), sesuai hak akses |
| **Pengaturan PLC** | Halaman untuk mengatur koneksi per perangkat (alamat, port, unit, aktif/nonaktif) per ruang uji — nilai disimpan di server |
| **Akun** | Login/logout; **ganti kata sandi** sendiri dari menu profil; **admin** dapat mengelola operator dan melihat log aktivitas lewat area admin |
| **API** | Endpoint terautentikasi untuk dashboard, data ruang, alarm, dan log — dipakai oleh antarmuka web dan dapat dipakai integrasi lain (token **Laravel Sanctum**) |

---

## Peran pengguna

- **Admin** — melihat seluruh area, mengelola akun operator, serta fitur administrasi lain di panel admin.
- **Operator** — memantau dan berinteraksi dengan ruang yang ditugaskan (termasuk pengaturan PLC untuk ruang itu, sesuai aturan di aplikasi); dapat mengakui/menyelesaikan alarm lewat API bila tersedia.
- **Viewer** — memantau ruang yang ditugaskan tanpa peran mengubah konfigurasi seperti operator.

Hak akses per ruang diatur di data pengguna (control room / ruang uji), bukan di README.

---

## Isi repositori (gambaran)

- **Kode aplikasi web & API** — folder `app/`, `routes/`, `resources/`, konfigurasi Laravel standar.
- **`python-modbus/`** — service polling Modbus ke PLC dan pengiriman hasil ke aplikasi Laravel.
- **`docs/`** — panduan menjalankan sistem dari nol dan penjelasan alur data (cocok untuk tim operasional dan pengembang).

---

## Menyiapkan lingkungan pengembangan

Ringkasan; langkah lengkap dan penyesuaian lingkungan ada di **`docs/PANDUAN_MENJALANKAN_SISTEM.md`**.

1. Salin `.env` dari `.env.example`, atur database, jalankan `php artisan key:generate`, `php artisan migrate`, lalu **`php artisan db:seed`** bila membutuhkan data demo (control room, ruang uji, akun).
2. Pasang dependensi: `composer install` dan `npm install`.
3. Untuk satu perintah awal bawaan proyek: **`composer run setup`** (lihat skrip di `composer.json`).
4. Saat pengembangan aktif: **`composer run dev`** menjalankan server Laravel, Vite, dan proses pendamping yang sudah didefinisikan di `composer.json`.

---

## Dokumentasi teknis

| File | Isi |
|------|-----|
| `docs/SISTEM_CARA_KERJA_DAN_INDEKS_FILE.md` | **Cara kerja internal** (login web/API, logout, dashboard, PLC), **diagram** (alur, ERD), **indeks semua file `app/`** dan modul utama untuk cross-check saat update |
| `docs/PANDUAN_MENJALANKAN_SISTEM.md` | Urutan instalasi, database, frontend, dan bridge hingga siap dipakai |
| `docs/PENJELASAN_CARA_KERJA_SISTEM.md` | Alur dari klik pengguna sampai data PLC, webhook, cache, dan broadcast |

---

## Uji & gaya kode PHP

```bash
composer test
vendor/bin/pint
```

---

## Lisensi

Kerangka aplikasi mengikuti lisensi **Laravel** ([MIT](https://opensource.org/licenses/MIT)). Lisensi produk lengkap mengikuti kebijakan pemilik repositori.
