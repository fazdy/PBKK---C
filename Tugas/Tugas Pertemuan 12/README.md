# Tugas Pertemuan 12 — Manajemen Produk ShopEase

Faizal Aldy Armiriawan · NRP 5025231266 · Pemrograman Berbasis Kerangka Kerja, kelas C.

Aplikasi CodeIgniter 4.7.6 untuk mengelola kategori, produk, gambar, dan inventori ShopEase. Admin dan operator bekerja di panel pengelolaan. Katalog publik membaca harga, status, stok, dan sampul dari database yang sama.

[Laporan siap tempel ke Blogspot](docs/Laporan-Blogspot-ShopEase-Pertemuan-12.html) · [25 screenshot Chrome](docs/screenshots/) · [ERD](docs/erd-katalog.png) · [Hasil pengujian MySQL](docs/verifikasi.json)

## Menjalankan pada MacBook Pro 2020 Intel

Aplikasi memakai PHP, Bootstrap 5.3.3 lokal, dan MySQL sesuai HOL. Tersedia mode SQLite satu file untuk demo di Mac dengan RAM 8 GB. Framework, CSS, dan gambar awal sudah berada di proyek. Untuk menjalankan aplikasi cukup gunakan PHP dan driver database yang dipilih. Node.js dipakai saat mengulang tes browser otomatis.

Dari checkout repositori:

```bash
cd "Tugas/Tugas Pertemuan 12"
php -v
php -m
```

Gunakan PHP 8.2 atau lebih baru dengan `intl`, `mbstring`, dan `fileinfo`. Periksa `mysqli` untuk MySQL atau `sqlite3` untuk SQLite. Instalasi lokal diperiksa pada macOS 15.8.3 Intel dengan PHP 8.4.12. Jika perlu memasang dependensi melalui Homebrew, jalankan `brew install php mysql` untuk MySQL atau `brew install php` untuk SQLite.

### Opsi A. MySQL sesuai panduan dosen

1. Jalankan MySQL lokal, misalnya dengan `brew services start mysql`. Masuk sebagai akun yang dapat membuat database melalui `mysql -u root -p`.
2. Buat database praktikum baru dan akun aplikasi. Ganti contoh password dengan nilai lokal Anda.

   ```sql
   CREATE DATABASE IF NOT EXISTS db_shopease
     CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER IF NOT EXISTS 'shopease'@'127.0.0.1'
     IDENTIFIED BY 'GantiDenganPasswordLokal';
   GRANT ALL PRIVILEGES ON db_shopease.*
     TO 'shopease'@'127.0.0.1';
   ```

3. Dari folder tugas, salin `shopease/env` menjadi `shopease/.env` jika file belum ada. Masukkan `database.default.password` yang sesuai. Template sudah mengarah ke pengguna `shopease`, database `db_shopease`, host `127.0.0.1`, dan port `3306`. Pada koneksi atau instalasi lain, sesuaikan nilainya. Jika MySQL Pertemuan 11 sudah memakai migration CI4 yang sama, gunakan konfigurasi database itu untuk melanjutkan akun yang ada.
4. Jalankan migrasi, isi data awal, lalu buka server.

   ```bash
   bash scripts/setup.sh mysql
   cd shopease
   php spark serve
   ```

### Opsi B. SQLite untuk demo ringan

Pada checkout baru, dari folder tugas:

```bash
bash scripts/setup.sh sqlite
cd shopease
php spark serve
```

Setup membuat `.env` bila belum ada. Untuk berganti driver, sesuaikan `.env` dengan `env` atau `env.sqlite` lalu ulangi setup. Kedua driver memiliki data masing-masing. Pada mode SQLite file database berada di `shopease/writable/db_shopease.sqlite`.

Buka [halaman masuk lokal](http://localhost:8080/login), login, lalu pilih **Kelola katalog produk**. Anda juga dapat langsung membuka [ringkasan katalog](http://localhost:8080/admin/catalog-dashboard) setelah login. Tekan **Control+C** pada Terminal server untuk berhenti.

| Akun | Email | Kata sandi demo | Halaman setelah login |
|---|---|---|---|
| Admin | `admin@shopease.test` | `ShopEase!2026` | `/admin` |
| Operator | `operator@shopease.test` | `ShopEase!2026` | `/operator` |
| Customer | `customer@shopease.test` | `ShopEase!2026` | `/` |

Seeder dibatasi untuk development/testing. Registrasi dari halaman publik membuat akun customer. Data awal memuat 4 kategori, 8 produk, dan 8 gambar. Seeder melewati akun dan slug yang sudah ada.

## Struktur implementasi

| Lokasi di dalam `shopease/` | Kegunaan |
|---|---|
| `app/Database/Migrations/` dan `app/Database/Seeds/` | Skema pengguna dan katalog, dua foreign key, akun demo, data beserta gambar awal. |
| `app/Models/CategoryModel.php`, `ProductModel.php`, `ProductImageModel.php` | Akses per entitas, filter, pagination, jumlah, dan sampul katalog. |
| `app/Controllers/Admin/` | Form, validasi, CRUD, inventori, dan ringkasan. |
| `app/Libraries/ProductCatalog.php` | Transaksi produk dan gambar, validasi upload, pemilihan sampul, serta pembersihan file. |
| `app/Config/Routes.php` dan `app/Filters/AuthFilter.php` | Pemetaan GET/POST dan pemeriksaan session serta role. |
| `app/Views/admin/`, `app/Views/layouts/management.php`, `public/assets/` | Panel admin/operator dan antarmuka responsif. |
| `app/Controllers/HomeController.php` dan `app/Views/frontend/` | Beranda dan katalog publik yang membaca database. |
| `public/uploads/products/` | File JPG/PNG/WebP pilihan pengguna, dibuat saat runtime dan dikecualikan dari Git. |

## Mencoba fitur

| Aksi | Halaman / langkah |
|---|---|
| Mengelola kategori | `/admin/categories` → Tambah kategori, Edit, atau Hapus. Kategori yang sedang dipakai tetap tersimpan. |
| Mengelola produk | `/admin/products` → Tambah produk, buka nama produk untuk melihat detail, lalu Edit atau Hapus. Stok 0 diterima. |
| Mencari dan menyaring | Masukkan kata pada kolom nama, pilih kategori, lalu Terapkan. Lima produk per halaman dengan filter yang dipertahankan saat berpindah halaman. |
| Mengelola galeri | Pilih satu atau beberapa file pada form. Pada halaman edit, gunakan **Jadikan utama** atau **Hapus gambar**. Sampul pengganti dipilih jika gambar utama dihapus. |
| Memeriksa inventori | `/admin/inventory` → seluruh stok, rendah (1–5 unit), atau habis (0 unit). Ubah jumlah lalu pilih Simpan. |
| Melihat ringkasan | `/admin/catalog-dashboard` → jumlah semua produk, kategori, produk tersedia/tidak tersedia, distribusi kategori, dan produk yang perlu diisi ulang. |
| Memeriksa hasil pelanggan | `/catalog` → cari produk berstatus tersedia, lalu cocokkan harga dan sampul dengan panel admin. |

Batas upload adalah 2 MiB per file, enam gambar per produk. Pastikan PHP mengizinkan unggahan 2 MiB per file. Untuk mengirim enam gambar mendekati batas sekaligus, gunakan `post_max_size = 16M` atau lebih di `php.ini`, lalu mulai ulang server. Nama file dipilih server dan setiap perubahan data membutuhkan CSRF.

## Pengujian dan dokumentasi

- MySQL 8.0.46, PHP 8.3.6, Chrome 157.0.8103.78. **24 pemeriksaan** lulus melalui HTTP, mencakup **10/10 skenario HOL** dan empat tugas pengembangan. [Run MySQL](https://github.com/fazdy/PBKK---C/actions/runs/44912481990) · [Data verifikasi](docs/verifikasi.json)
- macOS 15.7.9 Intel x86_64, PHP 8.5.9, SQLite 3.53.4, dan Chrome 151.0.7922.174. **24/24 pemeriksaan** yang sama lulus melalui HTTP. [Run macOS Intel](https://github.com/fazdy/PBKK---C/actions/runs/44912743545) · [Ringkasan lingkungan Mac](docs/verifikasi-mac.json)\n- Uji CGI lokal di Mac Intel memeriksa rute publik dan privat, kedua peran pengelola, penolakan customer, CRUD, stok nol, foreign key, upload dua gambar, pergantian sampul, dan penghapusan file.
- Screenshot 01–24 merekam halaman desktop dari server MySQL. Screenshot 25 menggunakan viewport 390 × 844. Gambar ERD merupakan diagram skema dari migration. [Screenshot](docs/screenshots/) · [ERD](docs/erd-katalog.png)

### Mengulang pengujian browser

Gunakan **checkout pengujian dan database baru khusus uji** dengan konfigurasi yang sama untuk server dan pemeriksaan database. Rangkaian tes membuat dan menghapus contoh Perlengkapan Kampus dan Ransel Kampus Arunika. Awal tes memerlukan tepat 4 kategori, 8 produk, dan 8 gambar setelah seeding.

1. Konfigurasikan `shopease/.env` memakai database uji. Jalankan setup untuk driver yang dipilih dan jalankan server di Terminal pertama. Gunakan `app.baseURL = 'http://127.0.0.1:8080/'` dan `php spark serve --host 127.0.0.1 --port 8080`.
2. Di Terminal kedua, dari **folder tugas**, jalankan contoh berikut. Untuk MySQL, sesuaikan DSN dan kredensial dengan database uji yang dipakai server.

   ```bash
   npm --prefix tests install
   export PBKK12_TEST_RUN=1
   export PBKK12_TEST_URL='http://127.0.0.1:8080'
   export PBKK12_TEST_DSN='mysql:host=127.0.0.1;dbname=db_shopease_test;charset=utf8mb4'
   export PBKK12_TEST_USER='shopease'
   read -rs PBKK12_TEST_PASSWORD
   export PBKK12_TEST_PASSWORD
   export CHROME_PATH='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'
   node tests/capture.mjs
   ```

   Untuk SQLite, ganti DSN dengan `export PBKK12_TEST_DSN="sqlite:$PWD/shopease/writable/db_shopease.sqlite"`. Tes memperbarui screenshot dan `docs/verifikasi.json` pada checkout uji.

## Menggunakan laporan di Blogger

Buka [sumber HTML laporan](docs/Laporan-Blogspot-ShopEase-Pertemuan-12.html) dengan editor teks. Buat posting baru di Blogger, pilih **Tampilan HTML**, lalu tempel seluruh isi file. Judul yang disarankan **Tugas Pertemuan 12 PBKK (C) — Manajemen Produk ShopEase dengan CodeIgniter 4**. Pilih **Pratinjau** untuk melihat ERD dan 25 screenshot sebelum publikasi. URL gambar mengarah ke berkas publik repositori ini.
