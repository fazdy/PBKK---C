# Tugas Pertemuan 9 — Pengenalan Framework CodeIgniter

| | |
|---|---|
| **Nama** | Faizal Aldy Armiriawan |
| **NRP** | 5025231266 |
| **Kelas** | PBKK (C) |
| **Materi** | PBKK-2 Pengenalan Framework CodeIgniter (MVC) |

Folder ini berisi tiga aplikasi web **CodeIgniter 3.1.13** beserta database dan laporannya.

| Latihan | Folder | Isi | Database |
|---|---|---|---|
| Latihan 1 (PBKK 2-1 s.d. 2-7) | [`tokobuah/`](tokobuah/) | Tutorial Tokobuah: instalasi, router & controller, Bootstrap, template SB Admin, model & CRUD, upload file, akses user | `tokobuah` |
| Latihan 2.1 | [`siakad/`](siakad/) | Sistem Informasi Akademik: master data, jadwal kelas, KRS, input nilai, KHS/IPK dengan 3 role (admin, dosen, mahasiswa) | `siakad` |
| Latihan 2.2 | [`tokoonline/`](tokoonline/) | Toko online **KampusMart**: katalog, pencarian, keranjang, checkout, riwayat pesanan, dan panel admin | `tokoonline` |

Laporan lengkap untuk Blogspot ada di [`docs/Laporan-Blogspot.md`](docs/Laporan-Blogspot.md) (versi HTML siap tempel: [`docs/Laporan-Blogspot.html`](docs/Laporan-Blogspot.html)).

## Struktur Folder

```
Tugas Pertemuan 9/
├── README.md
├── database/                 # File SQL (struktur + data contoh) untuk ketiga aplikasi
│   ├── tokobuah.sql
│   ├── siakad.sql
│   └── tokoonline.sql
├── tokobuah/                 # Latihan 1  (template SB Admin 5.1.1, Bootstrap 4)
├── siakad/                   # Latihan 2.1 (template SB Admin 2 4.1.4, Bootstrap 4)
├── tokoonline/               # Latihan 2.2 (Bootstrap 5.3.3 + Bootstrap Icons)
├── docs/
│   ├── Laporan-Blogspot.md   # Sumber laporan
│   ├── Laporan-Blogspot.html # HTML siap tempel ke Blogger
│   └── screenshots/          # t = Tokobuah, s = SIAKAD, k = KampusMart, d = database
└── scripts/
    └── buat-html-blogspot.py # Mengubah laporan Markdown menjadi HTML Blogger
```

Setiap aplikasi adalah proyek CodeIgniter 3 utuh (`application/`, `system/`, `index.php`). Kode yang ditulis sendiri ada di `application/` (controllers, models, views, core, helpers, libraries, language).

## Kebutuhan

- XAMPP 8.x (PHP 8.0 s.d. 8.2 + MariaDB/MySQL + phpMyAdmin). Diuji dengan **PHP 8.2.4** dari XAMPP.
- Browser modern (Chrome/Firefox/Edge).

## Cara Menjalankan dengan XAMPP

1. Buka XAMPP Control Panel, lalu **Start** Apache dan MySQL.
2. Salin folder `tokobuah`, `siakad`, dan `tokoonline` ke folder `htdocs`
   (Windows: `C:\xampp\htdocs\`, macOS: `/Applications/XAMPP/htdocs/`).
3. Buka `http://localhost/phpmyadmin`, pilih tab **Import**, lalu impor ketiga file di folder `database/` satu per satu.
   File SQL sudah berisi `CREATE DATABASE`, jadi database tidak perlu dibuat manual.
4. Buka aplikasi di browser:
   - Tokobuah: `http://localhost/tokobuah/` (panel admin: `http://localhost/tokobuah/index.php/admin`)
   - SIAKAD: `http://localhost/siakad/`
   - KampusMart: `http://localhost/tokoonline/` (panel admin: `http://localhost/tokoonline/index.php/admin`)

`base_url` dibuat otomatis dari alamat yang diakses, jadi nama folder di `htdocs` boleh diganti tanpa mengubah konfigurasi.
Koneksi database memakai `127.0.0.1`, user `root`, tanpa password (bawaan XAMPP). Jika berbeda, ubah `application/config/database.php` di masing-masing aplikasi.

### Alternatif tanpa Apache (built-in server PHP)

Dari folder `Tugas Pertemuan 9`, jalankan:

```bash
php -S localhost:8080
```

Lalu buka `http://localhost:8080/tokobuah/`, `http://localhost:8080/siakad/`, atau `http://localhost:8080/tokoonline/`. Cara ini dipakai saat membuat screenshot di macOS.

## Akun Uji

Semua akun di bawah ini hanya data contoh dari file SQL.

| Aplikasi | Username / Email | Password | Role |
|---|---|---|---|
| Tokobuah | `admin` atau `admin@tokobuah.test` | `admin123` | admin (boleh masuk panel) |
| Tokobuah | `budi` | `budi123` | customer (ditolak di panel admin) |
| SIAKAD | `admin` | `admin123` | admin |
| SIAKAD | `198001012005011001` (NIP) | `dosen123` | dosen |
| SIAKAD | `5025231001` (NRP) | `mhs123` | mahasiswa |
| KampusMart | `admin@kampusmart.test` | `admin123` | admin |
| KampusMart | `budi@kampusmart.test` | `budi123` | pelanggan |
| KampusMart | `siti@kampusmart.test` | `siti123` | pelanggan |

Mahasiswa yang ditambahkan admin SIAKAD memakai NRP sebagai password awal.

## Fitur Utama

**Tokobuah (Latihan 1)**
- Route kustom (`/about`, `/contact`, `/admin`) dan controller dengan parameter URL (`/page/hello/Nama`).
- Template SB Admin dipecah menjadi partial view (head, navbar, sidebar, breadcrumb, footer, modal, js).
- CRUD produk dengan Form Validation dan upload foto (jpg/png/gif, maks. 1 MB). Foto lama ikut dihapus saat produk dihapus atau fotonya diganti.
- Login admin (email atau username), password `password_hash()`, role `customer` ditolak, semua halaman admin dilindungi `Admin_Controller`.

**SIAKAD (Latihan 2.1)**
- Login satu pintu, menu dan hak akses berbeda untuk admin, dosen, dan mahasiswa (role salah mendapat halaman 403).
- Admin: CRUD mahasiswa, dosen, mata kuliah, serta kelas & jadwal dengan cek bentrok ruang/dosen.
- Mahasiswa: isi KRS dengan aturan kuota, bentrok jadwal, mata kuliah ganda, dan batas 24 SKS; lihat KHS, IP semester, dan IPK.
- Dosen: input nilai angka, nilai huruf dihitung otomatis oleh helper `nilai_huruf()`.

**KampusMart (Latihan 2.2)**
- Katalog dengan kategori, pencarian, pengurutan, dan library Pagination.
- Keranjang berbasis session (library `Keranjang` buatan sendiri) yang selalu mengecek stok terbaru.
- Checkout dalam satu transaksi database (stok dikunci dan dikurangi), metode bayar transfer/COD, pembatalan mengembalikan stok.
- Panel admin: dashboard (omzet, grafik 7 hari, produk terlaris, stok menipis), CRUD produk dengan upload + resize gambar, kategori, kelola status pesanan, daftar pelanggan.

## Catatan Teknis

- CodeIgniter 3.1.13 memunculkan pesan *deprecated* "Creation of dynamic property" di PHP 8.2. Pada `index.php` mode `development`, `E_DEPRECATED` disembunyikan; error lain tetap tampil.
- CSRF protection aktif. Semua aksi hapus dan ubah data memakai form POST.
- Zona waktu PHP disetel ke `Asia/Jakarta` di `index.php` (XAMPP bawaan memakai `Europe/Berlin`).
- Pesan validasi dan upload berbahasa Indonesia lewat `application/language/indonesian/`.
- File SQL memakai collation `utf8mb4_general_ci` agar bisa diimpor ke MariaDB 10.4 (XAMPP) maupun MySQL 8/9.

## Referensi

- [CodeIgniter 3 User Guide](https://codeigniter.com/userguide3/)
- [Petani Kode: Tutorial CodeIgniter untuk Pemula](https://www.petanikode.com/codeigniter-pemula/)
- [petanikode/tutorial-codeigniter (GitHub)](https://github.com/petanikode/tutorial-codeigniter/tree/tutorial-7)
- [Start Bootstrap SB Admin](https://github.com/StartBootstrap/startbootstrap-sb-admin) dan [SB Admin 2](https://github.com/StartBootstrap/startbootstrap-sb-admin-2)
