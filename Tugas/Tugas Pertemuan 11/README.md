# Tugas Pertemuan 11 — Autentikasi ShopEase

**Nama:** Faizal Aldy Armiriawan<br>
**NRP:** 5025231266<br>
**Kelas:** PBKK (C)

Tugas ini menerapkan alur autentikasi dari materi **Hands-On Lab Authentication System CodeIgniter 4**. Cakupannya meliputi registrasi customer, login, session, pembatasan akses customer/admin/operator, dan logout. Halaman publik ShopEase dari Pertemuan 10 tetap terintegrasi dengan navigasi akun.

## Mulai di MacBook Pro 2020 Intel

Lingkungan yang diperiksa: macOS 15.8.3, arsitektur `x86_64`, PHP **8.4.12**, CodeIgniter **4.7.6**, Bootstrap **5.3.3**. Aplikasi berjalan sebagai web PHP, sehingga tidak membutuhkan Windows, Laragon, VM, Docker, atau Visual Studio Windows. Gunakan Terminal, VS Code versi Mac Intel, serta Safari/Chrome. Screenshot diuji dengan Chrome 157, bukan pengujian Safari/Firefox.

Dari root repository `PBKK---C`:

```bash
cd "Tugas/Tugas Pertemuan 11"
php -v
bash scripts/setup.sh sqlite
cd shopease
php spark serve
```

Buka **http://localhost:8080/**. Untuk berhenti, tekan **Control+C** pada Terminal server.

`setup.sh` membuat `.env` hanya jika belum ada, menjalankan migration, dan menambahkan tiga akun demo. Database SQLite tersimpan di `shopease/writable/db_shopease.sqlite`; tetap ada setelah aplikasi ditutup. Script tidak menghapus database, mereset password akun yang sudah ada, atau menimpa `.env`.

Framework dan CSS sudah disertakan. **Composer dan Node.js tidak diperlukan untuk menjalankan aplikasi.** Node/Playwright hanya diperlukan jika ingin mengulang pengambilan screenshot otomatis.

Jika PHP belum tersedia, pasang Homebrew mengikuti [petunjuk resmi](https://brew.sh/), kemudian `brew install php`. Untuk Mac Intel, prefix Homebrew umumnya `/usr/local`. CI 4.7.6 membutuhkan PHP **8.2+**, `intl`, dan `mbstring`. Mode SQLite juga membutuhkan `sqlite3`; mode MySQL membutuhkan `mysqli`. Periksa dengan `php -m`. Versi PHP yang benar-benar diuji pada tugas ini adalah 8.4.12.

## Akun demonstrasi

| Role | Email | Password | Setelah login |
|---|---|---|---|
| Customer | `customer@shopease.test` | `ShopEase!2026` | `/`, profil di `/account` |
| Admin | `admin@shopease.test` | `ShopEase!2026` | `/admin` |
| Operator | `operator@shopease.test` | `ShopEase!2026` | `/operator` |

Akun ini hanya untuk demo lokal. Seeder menolak dijalankan dalam environment production. Form registrasi publik **selalu menyimpan `role = customer`**, termasuk ketika request dimanipulasi. Email berdomain `.test` adalah data contoh.

Screenshot menggunakan customer tambahan `faizal.demo@shopease.test` yang dibuat melalui form di database pengujian terpisah. Akun tersebut tidak otomatis ada pada database Anda. Karena itu screenshot dashboard memperlihatkan **2 customer, 1 admin, 1 operator**, sedangkan seeder awal menghasilkan **1/1/1**.

## Cakupan materi

Dokumen khusus **Hands-On_Lab_Authentication_System_CodeIgniter_4.docx.pdf** menjadi acuan pengerjaan Tugas Pertemuan 11. PRD, HOL umum, studi kasus, dan lima infografis menjadi konteks arsitektur/roadmap. Nomor “Pertemuan 11” pada nama folder adalah nomor pengumpulan tugas; roadmap generik dalam dokumen memiliki penomoran modul tersendiri.

| Bagian | Implementasi |
|---|---|
| Registrasi | Nama, email unik, telepon dan alamat opsional, konfirmasi password, timestamp otomatis. |
| Login | Normalisasi email, `password_verify`, session ID baru, redirect sesuai role. |
| Otorisasi | `AuthFilter`, tamu diarahkan ke login, role yang salah mendapat HTTP 403. |
| Dashboard | Admin: statistik akun dan 10 pengguna terbaru; operator: identitas dan area tersendiri. |
| Customer | Beranda setelah login dan halaman profil dari database. |
| Logout | Hanya POST + CSRF, session dihancurkan, akses privat berikutnya ditolak. |
| Proteksi tambahan | Hash password, CSRF session, escaping output, rate limiting login, `Cache-Control: no-store`. |
| Katalog | Data demonstrasi berupa array dari Pertemuan 10, dengan pencarian/filter kategori. |

CRUD produk/kategori, keranjang, checkout, pembayaran, pengiriman, dan laporan penjualan **belum termasuk modul ini**. Dashboard akun bukan dashboard transaksi dari keseluruhan PRD. Database tahap ini berisi tabel `users` serta tabel pelacak `migrations`.

## Menggunakan MySQL sesuai materi

SQLite dipakai untuk demo ringan. MySQL 9.3.0 juga telah diuji dengan 47/47 pemeriksaan integrasi serta 14/14 pemeriksaan browser HTTP. Konfigurasinya tersedia melalui `env.mysql`, migration lintas driver, dan [schema-mysql.sql](database/schema-mysql.sql). Pengujian MySQL menggunakan instance sementara melalui Unix socket, tanpa mengubah service atau database pengguna. Koneksi TCP port 3306 pada instalasi pengguna perlu disesuaikan dengan kredensial lokal.

1. Jalankan MySQL lokal. Jika menggunakan paket Homebrew yang sudah terpasang:

   ```bash
   brew services start mysql
   mysql -u root -p
   ```

2. Buat database dan akun lokal melalui MySQL client. Ganti contoh password dengan password database lokal Anda.

   ```sql
   CREATE DATABASE IF NOT EXISTS db_shopease
     CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER IF NOT EXISTS 'shopease'@'127.0.0.1'
     IDENTIFIED BY 'GantiPasswordDatabaseAnda';
   GRANT ALL PRIVILEGES ON db_shopease.* TO 'shopease'@'127.0.0.1';
   ```

3. Dari folder `Tugas/Tugas Pertemuan 11/shopease`, jika `.env` belum ada: `cp env.mysql .env`. Jika sebelumnya memakai SQLite, **edit `.env` yang ada**. Isi:

   ```ini
   CI_ENVIRONMENT = development
   app.baseURL = 'http://localhost:8080/'
   database.default.hostname = 127.0.0.1
   database.default.database = db_shopease
   database.default.username = shopease
   database.default.password = 'GantiPasswordDatabaseAnda'
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

4. Jalankan:

   ```bash
   php spark migrate
   php spark db:seed DemoUserSeeder
   php spark serve
   ```

Gunakan database kosong untuk migration pertama. File SQL adalah alternatif import manual, **bukan langkah tambahan sebelum migration**: melakukan keduanya pada tabel `users` yang sama akan menimbulkan konflik. Beralih driver tidak memindahkan akun SQLite ke MySQL secara otomatis.

## Struktur berkas penting

| Lokasi | Isi |
|---|---|
| `shopease/app/Config/Routes.php` | Routing eksplisit; Auto Routing dinonaktifkan. |
| `shopease/app/Config/Database.php` | Default SQLite dengan override `.env` untuk MySQL. |
| `shopease/app/Config/Filters.php`, `Security.php` | CSRF global, auth, header keamanan. |
| `shopease/app/Controllers/AuthController.php` | Validasi registrasi, hash, login, session, logout. |
| `shopease/app/Models/UserModel.php` | Model tabel `users`, primary key `id_user`, timestamp. |
| `shopease/app/Filters/AuthFilter.php` | Pemeriksaan akun aktif dalam arti masih terdaftar dan otorisasi role. |
| `shopease/app/Database/Migrations/` | Skema tabel dan index email unik. |
| `shopease/app/Database/Seeds/DemoUserSeeder.php` | Tiga akun demonstrasi lokal. |
| `shopease/app/Views/auth/` | Form, profil, dashboard, dan halaman 403. |
| `shopease/public/assets/css/auth.css` | Layout desktop dan mobile. |
| `scripts/setup.sh` | Persiapan cepat aplikasi. |
| `tests/test_auth.py` | Pengujian backend PHP CGI pada SQLite/MySQL terpisah. |
| `tests/capture.cjs`, `tests/test_mysql.py` | Pengujian MySQL dan browser dan pembuatan screenshot asli. |
| `docs/Laporan-Blogspot-ShopEase-Autentikasi.html` | Isi siap tempel ke tampilan HTML Blogger. |
| `docs/Pratinjau-Laporan-ShopEase-Autentikasi.html` | Pratinjau mandiri dengan screenshot tertanam. |
| `docs/screenshots/` | 12 PNG hasil aplikasi. |
| `docs/hasil-pengujian.json`, `docs/hasil-mysql.json`, `docs/hasil-browser.json`, `docs/hasil-lint.json` | Bukti hasil pengujian mesin. |

## Rute dan matriks akses

| Rute | Method | Tamu | Customer | Admin | Operator |
|---|---|---|---|---|---|
| `/`, `/catalog`, `/about`, `/contact` | GET | Ya | Ya | Ya | Ya |
| `/register`, `/login` | GET/POST | Ya | Redirect ke area akun | Redirect | Redirect |
| `/account` | GET | Redirect login | Ya | 403 | 403 |
| `/admin` | GET | Redirect login | 403 | Ya | 403 |
| `/operator` | GET | Redirect login | 403 | 403 | Ya |
| `/logout` | POST | Redirect login jika CSRF valid | Ya | Ya | Ya |

POST tanpa CSRF ditolak sebelum controller dijalankan. Setelah POST berhasil, CI4 menggunakan HTTP **303** (Post/Redirect/Get). Redirect tamu pada GET menggunakan **302**.

## Pengujian yang sudah dijalankan

Verifikasi ulang pada **15 November 2026**, macOS 15.8.3 Intel x86_64:

| Pemeriksaan | Hasil | Bukti |
|---|---|---|
| Integrasi PHP CGI + SQLite3 | 47/47 lulus | [hasil-pengujian.json](docs/hasil-pengujian.json) |
| Integrasi PHP CGI + MySQL 9.3.0 | 47/47 lulus | [hasil-mysql.json](docs/hasil-mysql.json) |
| Browser Chrome 157 + HTTP nyata + MySQL | 14/14 lulus | [hasil-browser.json](docs/hasil-browser.json) |
| Sintaks PHP aplikasi, entrypoint, helper uji | 82 file, nol error | [hasil-lint.json](docs/hasil-lint.json) |
| Screenshot baru dari HTTP + MySQL | 12 PNG | [screenshots](docs/screenshots/) |

Delapan skenario wajib HOL tercakup: registrasi baru, email duplikat, password pendek, konfirmasi berbeda, login benar, login salah, logout, dan akses privat tanpa login. Tes tambahan mencakup hash password, CSRF, regenerasi/session lama, XSS, rate limiting, perubahan role, dan akun yang dihapus.

Screenshot diambil melalui **server `php spark serve` pada http://127.0.0.1:8082/** dengan Playwright 1.62.1 dan Chrome 157.0.8103.78. Tidak memakai adapter request atau mock HTML. Respons HTTP, cookie, form POST, redirect, dan aset berasal dari server aplikasi. Lebar mobile yang diperiksa adalah 390 dan 320 piksel. Safari, Firefox, perangkat iPhone fisik, dan deployment produksi belum diuji.

Mengulang integrasi SQLite dari folder tugas:

```bash
python3 tests/test_auth.py --output docs/hasil-pengujian.json
```

Tes menggunakan database/cache/session temporer dan tidak membaca `.env` demo. Diperlukan `php-cgi`, yang disediakan paket Homebrew PHP.

Mengulang seluruh pengujian MySQL beserta screenshot HTTP:

```bash
# Jika MySQL belum terpasang:
brew install mysql
npm --prefix tests install
CHROME_PATH="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
  python3 tests/test_mysql.py
python3 scripts/build_report.py
```

`test_mysql.py` membuat instance MySQL sementara dengan buffer pool 64 MiB, tanpa jaringan TCP, memakai Unix socket serta database `db_shopease_test` dan `db_shopease_browser`. Instance dan data uji dibersihkan setelah selesai. Script tidak mengubah MySQL yang sudah berjalan. Pastikan port **8082** kosong untuk server browser. Tes berjalan berurutan agar beban RAM tetap ringan pada perangkat 8 GB.

Jika ingin screenshot HTTP dengan SQLite saja, jalankan `node tests/capture.cjs` dengan `CHROME_PATH` seperti di atas. Ini mengganti screenshot dan `hasil-browser.json` dengan bukti SQLite; jalankan kembali pembuat laporan setelahnya. Untuk memakai Chromium bawaan Playwright, jalankan `cd tests && npx playwright install chromium`, kembali ke folder tugas, lalu jalankan tes tanpa `CHROME_PATH`.

## Kelanjutan dari progres lama

Materi, implementasi, tes, README, dua HTML, dan 12 PNG ditemukan pada folder proyek lama. Kode aplikasi autentikasi dipertahankan. Perubahan lanjutan: isolasi konfigurasi tes agar `.env` SQLite tidak menimpa driver MySQL, penambahan tes MySQL sementara, penggantian adapter screenshot CGI dengan server HTTP nyata, pengambilan ulang 12 screenshot, pembaruan dokumentasi dan tautan README root. Framework `system/` tetap sama dengan Pertemuan 10. Salinan kerja dibuat dari branch `main` terbaru sebelum perubahan tugas digabungkan.

## Upload laporan ke Blogspot

1. Push folder tugas beserta `docs/screenshots/` ke branch `main` repository publik `fazdy/PBKK---C`.
2. Buka [Laporan-Blogspot-ShopEase-Autentikasi.html](docs/Laporan-Blogspot-ShopEase-Autentikasi.html) di VS Code dan salin **seluruh sumber HTML**.
3. Buat posting Blogger. Judul yang disarankan: **Tugas Pertemuan 11 PBKK (C) — Sistem Autentikasi ShopEase dengan CodeIgniter 4**.
4. Ubah editor ke **Tampilan HTML**, tempel kode, lalu gunakan **Pratinjau** sebelum publikasi.
5. File laporan memakai URL `raw.githubusercontent.com` untuk 12 screenshot. Gambar baru dapat diakses pembaca setelah push berhasil dan repository dapat diakses publik. Jika repository privat, unggah PNG dari `docs/screenshots/` lewat Blogger lalu ganti URL `src` pada laporan.

Untuk membaca hasil sekarang tanpa menunggu push, buka [Pratinjau-Laporan-ShopEase-Autentikasi.html](docs/Pratinjau-Laporan-ShopEase-Autentikasi.html). Versi ini menanam PNG di dalam file, tetapi **bukan sumber yang disarankan untuk ditempel ke Blogger**.

## Git push

Dari root repository:

```bash
git status --short
git add -- "Tugas/Tugas Pertemuan 11"
git diff --cached --stat
git commit -m "Tambah Tugas Pertemuan 11: autentikasi ShopEase CI4"
git push origin main
```

README root memuat tautan Pertemuan 11 setelah Pertemuan 10. Sertakan perubahan itu dengan `git add README.md` setelah memeriksa diff. `.env`, database, session, log, cache, dan `node_modules` diabaikan Git. Laporan Blogspot disiapkan untuk ditempel pengguna; tidak ada klaim bahwa posting Blogger sudah diterbitkan.

## Pemecahan masalah

| Gejala | Tindakan |
|---|---|
| `php: command not found` | Pasang PHP, buka Terminal baru, cek `which php` dan `php -v`. |
| Ekstensi `intl`, `mbstring`, `sqlite3` tidak ada | Periksa `php --ini`, `php -m`, dan instalasi PHP CLI yang aktif. |
| `no such table: users` | Jalankan `bash scripts/setup.sh sqlite` dari folder tugas. |
| Port 8080 sedang dipakai | Ubah `app.baseURL` di `.env` menjadi `http://localhost:8081/`, lalu `php spark serve --port 8081`. |
| CSRF 403 setelah lama meninggalkan form | Muat ulang form, lalu kirim kembali dengan token baru. |
| Login mendapat HTTP 429 | Tunggu sesuai pesan; pembatas menggunakan token bucket yang terisi kembali. |
| Akun demo gagal login | Periksa driver/database aktif; jalankan seeder pada database yang benar. Seeder tidak mereset akun yang sudah ada. |
| Gambar Blogspot belum muncul | Pastikan 12 PNG sudah ter-push ke `main`, folder dan huruf besar/kecil tepat, serta repository publik. |

## Referensi

- Materi pengguna: HOL Authentication System; HOL ShopEase; PRD ShopEase; Studi Kasus ShopEase; lima infografis dalam folder `Materi Pertemuan 11`.
- [Persyaratan CodeIgniter 4](https://codeigniter.com/user_guide/intro/requirements.html)
- [Konfigurasi database](https://codeigniter.com/user_guide/database/configuration.html)
- [CSRF dan keamanan](https://codeigniter.com/user_guide/libraries/security.html)
- [Controller Filters](https://codeigniter.com/user_guide/incoming/filters.html)
- [Database Seeding](https://codeigniter.com/user_guide/dbmgmt/seeds.html)
- [Homebrew](https://brew.sh/)
