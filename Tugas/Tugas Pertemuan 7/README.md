# ♻️ SIBASA — Sistem Informasi Bank Sampah

**Tugas Pertemuan 7 — Proyek Aplikasi Desktop (Persiapan UTS)**
Pemrograman Berbasis Kerangka Kerja (PBKK) — Kelas C

| | |
|---|---|
| **Nama** | Faizal Aldy Armiriawan |
| **NRP** | 5025231266 |
| **Kelas** | PBKK (C) |
| **Kelompok** | Perorangan (1 anggota) |

---

## Deskripsi

SIBASA adalah aplikasi desktop untuk **bank sampah tingkat RT/RW**. Petugas mencatat setoran sampah terpilah dari warga. Nilai rupiahnya dihitung otomatis dari **berat × harga per kg** dan langsung masuk ke saldo tabungan nasabah. Pengurus memantau jumlah nasabah, sampah yang terkumpul, saldo, dan tren setoran lewat dashboard berisi kartu statistik dan grafik.

Aplikasi dibangun dengan **Avalonia UI** (XAML + C#, sejenis WPF yang berjalan di macOS) karena dikerjakan di MacBook Intel, dengan pola **MVVM** dan database **SQLite**.

## Fitur

| Fitur | Keterangan |
|---|---|
| 🔐 Login dan hak akses | Peran **Admin** dan **Petugas**. Password disimpan sebagai hash PBKDF2-SHA256, dan akses diperiksa di Service (bukan hanya disembunyikan di UI) |
| 📊 Dashboard | 4 kartu statistik, grafik batang setoran (kg) 6 bulan, grafik donat komposisi kategori, dan aktivitas terbaru. Semua angka berasal dari query database |
| 👥 Data nasabah (CRUD) | Tambah, ubah, aktif/nonaktif, hapus. Pencarian nama/no. rekening tanpa membedakan huruf besar/kecil, filter RW dan status, serta pengurutan kolom |
| ♻️ Jenis sampah dan kategori | Kelola jenis, kategori, dan harga per kg (khusus Admin; Petugas hanya bisa melihat) |
| ⬇️ Setoran | Pratinjau total otomatis. Simpan setoran, tambah saldo, dan catat aktivitas dalam **satu transaksi database** |
| ⬆️ Penarikan | Saldo tidak boleh minus, ada dialog konfirmasi, dan tombol "Tarik semua" |
| 🕘 Riwayat transaksi | Filter periode/jenis/kata kunci, ringkasan total, dan pembatalan transaksi oleh Admin (saldo dikoreksi otomatis) |
| 👤 Kelola pengguna | Tambah petugas, ubah peran/status, reset password. Admin tidak bisa mengunci akunnya sendiri |
| 🌗 Tema terang/gelap | Berganti langsung tanpa restart dan tersimpan untuk pembukaan berikutnya |
| ⌨️ Pintasan keyboard | ⌘/Ctrl + 1…7 untuk berpindah halaman |
| 🧪 Tes otomatis | 54 tes xUnit untuk Service, aturan saldo, hak akses, dan database |

## Teknologi

| Komponen | Versi |
|---|---|
| .NET SDK (LTS) | 10.0 |
| Avalonia UI (Fluent + DataGrid) | 11.3 |
| CommunityToolkit.Mvvm | 8.4.2 |
| LiveChartsCore.SkiaSharpView.Avalonia | 2.0.5 |
| Microsoft.Data.Sqlite | 10.0.13 |
| Microsoft.Extensions.Configuration.Json | 10.0.13 |
| xUnit (pengujian) | 2.9.3 |

Avalonia sengaja dikunci di versi 11.3 karena LiveCharts2 versi stabil belum mendukung Avalonia 12 (alasan lengkap ada di [docs/Panduan-Instalasi.md](docs/Panduan-Instalasi.md)).

## Prasyarat (macOS Intel)

- macOS 15 atau lebih baru, prosesor Intel (cek dengan `uname -m` → `x86_64`)
- [.NET SDK 10 (x64)](https://dotnet.microsoft.com/download/dotnet/10.0)
- Visual Studio Code + C# Dev Kit, atau JetBrains Rider
- (Opsional) [DB Browser for SQLite](https://sqlitebrowser.org/dl/) untuk melihat isi database

Langkah instalasi lengkap untuk pemula ada di **[docs/Panduan-Instalasi.md](docs/Panduan-Instalasi.md)**.

## Menjalankan aplikasi

```bash
cd ~/Documents/PBKK/Tugas/PBKK---C/"Tugas/Tugas Pertemuan 7/src/SIBASA"
```

```bash
dotnet run
```

- Saat build pertama, `appsettings.json` dibuat otomatis dari `appsettings.example.json`.
- Database SQLite dan data contohnya dibuat otomatis saat aplikasi pertama kali dibuka:
  - macOS: `~/Library/Application Support/SIBASA/sibasa.db`
  - Windows: `%LOCALAPPDATA%\SIBASA\sibasa.db`
- Untuk kembali ke data contoh awal, tutup aplikasi lalu hapus file `sibasa.db`.

> Jika muncul `NETSDK1045` (SDK 10 tidak ditemukan), artinya perintah `dotnet` masih memakai SDK lama. Lihat langkah 2.3 di panduan instalasi, atau jalankan dengan path lengkap: `/usr/local/share/dotnet/dotnet run`.

### Akun login contoh

| Peran | Username | Password |
|---|---|---|
| Admin | `admin` | `Admin#123` |
| Petugas | `petugas` | `Petugas#123` |

Akun demo juga ditampilkan di layar login. Kotak itu bisa disembunyikan dengan mengubah `App:ShowDemoAccounts` menjadi `false` di `appsettings.json`.

### Konfigurasi (`appsettings.json`)

```json
{
  "ConnectionStrings": {
    "SibasaDb": "Data Source={DataDir}/sibasa.db;Foreign Keys=True;Default Timeout=5"
  },
  "Database": { "SeedSampleData": true },
  "App": { "ShowDemoAccounts": true }
}
```

`{DataDir}` diganti otomatis dengan folder data pengguna. Connection string tidak ditulis di kode. File asli `appsettings.json` di-*ignore* Git, sedangkan yang di-commit hanya `appsettings.example.json`.

## Menjalankan tes otomatis

```bash
cd ~/Documents/PBKK/Tugas/PBKK---C/"Tugas/Tugas Pertemuan 7"
```

```bash
dotnet test SIBASA.sln
```

## Membuat paket unduhan (Windows .exe dan macOS .app)

```bash
./scripts/publish.sh
```

Skrip ini menghasilkan `release/SIBASA-1.0.0-Windows-x64.zip` (berisi `SIBASA.exe`) dan `release/SIBASA-1.0.0-macOS-Intel.zip` (berisi `SIBASA.app`). Keduanya *self-contained*, jadi penguji tidak perlu memasang .NET atau server database. Unggah kedua zip ke Google Drive dan bagikan tautannya. Petunjuk untuk penguji ada di `scripts/BACA-SAYA.txt` (ikut di dalam zip).

## Struktur folder

```
Tugas Pertemuan 7/
├── README.md
├── .gitignore
├── SIBASA.sln
├── docs/
│   ├── Panduan-Instalasi.md     panduan instalasi Mac Intel
│   ├── Infografis.md            infografis ruang lingkup (teks)
│   ├── infografis.html          infografis 1 halaman (visual)
│   ├── PRD.md                   Product Requirements Document
│   ├── Laporan-Blogspot.md      isi postingan Blogspot (sumber)
│   ├── Laporan-Blogspot.html    versi HTML siap tempel ke Blogger
│   ├── Naskah-Video-Demo.md     urutan adegan dan narasi video demo
│   ├── images/                  ERD, diagram alur, infografis (PNG)
│   └── screenshots/             tangkapan layar aplikasi
├── database/
│   ├── 01_create_database.sql   pengaturan awal SQLite (PRAGMA)
│   ├── 02_create_tables.sql     tabel, relasi, constraint, indeks
│   └── 03_seed_data.sql         data contoh (di-embed ke aplikasi)
├── scripts/
│   ├── publish.sh               membuat zip Windows dan macOS
│   ├── buat-html-blogspot.py    mengubah laporan Markdown menjadi HTML Blogger
│   └── BACA-SAYA.txt            petunjuk untuk penguji
├── src/SIBASA/                  proyek Avalonia (MVVM)
│   ├── Views/                   tampilan .axaml (code-behind kosong)
│   ├── ViewModels/              state UI dan command
│   ├── Models/                  entitas: User, Nasabah, JenisSampah, Setoran, ...
│   ├── Repositories/            query SQL berparameter
│   ├── Services/                aturan bisnis, hak akses, tema, notifikasi
│   ├── Data/                    koneksi SQLite dan inisialisasi database
│   ├── Styles/                  design tokens tema terang/gelap dan gaya kontrol
│   ├── Converters/  Helpers/  Controls/  Assets/  Packaging/
│   ├── appsettings.example.json
│   └── SIBASA.csproj
└── tests/SIBASA.Tests/          tes otomatis xUnit
```

## Tangkapan layar

Simpan tangkapan layar di `docs/screenshots/` dengan nama berikut (sama dengan penanda `[SCREENSHOT: ...]` di laporan Blogspot):

| File | Isi |
|---|---|
| `01-login.png` | Halaman login (tema terang) |
| `02-dashboard-terang.png` | Dashboard tema terang |
| `03-dashboard-gelap.png` | Dashboard tema gelap dengan panel aktivitas terbaru |
| `04-nasabah.png` | Daftar nasabah dengan pencarian dan filter |
| `05-nasabah-validasi.png` | Form tambah nasabah dengan pesan validasi per kolom |
| `06-nasabah-hapus.png` | Dialog konfirmasi hapus nasabah |
| `07-jenis-sampah.png` | Jenis sampah dan harga (Admin) dengan panel ubah |
| `08-setoran.png` | Form setoran dengan pratinjau total |
| `09-penarikan-ditolak.png` | Penarikan melebihi saldo ditolak |
| `10-riwayat.png` | Riwayat transaksi dengan status "Dibatalkan" |
| `11-pengguna.png` | Kelola pengguna |
| `12-pengaturan.png` | Pengaturan (tema, profil, ganti password, lokasi database) |
| `13-akses-ditolak.png` | Petugas membuka halaman khusus Admin |
| `14-isi-database.png` | Isi tabel Setoran di `sibasa.db` (sqlite3 atau DB Browser) |
| `15-dotnet-test.png` | Hasil `dotnet test` (54 lulus) |
| `16-paket-rilis.png` | Paket rilis Windows dan macOS |

## Laporan Blogspot dan video demo

- `docs/Laporan-Blogspot.md` berisi seluruh isi postingan: pendahuluan, infografis, PRD lengkap, rancangan, langkah pengerjaan, hasil, pengujian, kendala, kesimpulan, dan bagian **Tautan** di paling bawah.
- `docs/Laporan-Blogspot.html` adalah versi HTML siap tempel. Semua gaya ditulis inline, tanpa CSS eksternal maupun skrip. File ini dibuat ulang dari versi Markdown dengan:

```bash
python3 scripts/buat-html-blogspot.py
```

Cara memasang di Blogger:

1. Lengkapi dulu penanda di `docs/Laporan-Blogspot.md`: `[LINK ...]`, `[ISI SETELAH DIUJI]`, dan `[ISI TANGGAL]`. Setelah itu jalankan ulang perintah di atas.
2. Di Blogger, buat postingan baru dan isi judulnya dengan judul di baris pertama laporan.
3. Pindah ke **Tampilan HTML**, lalu tempel seluruh isi `docs/Laporan-Blogspot.html`.
4. Kembali ke **Tampilan menulis**. Pada setiap kotak kuning `[GAMBAR: ...]` dan `[SCREENSHOT: ...]`, sisipkan gambar yang sesuai lewat ikon *Insert image*, lalu hapus kotaknya.
5. Pratinjau, publikasikan, lalu kumpulkan tautan postingannya.

Alternatif langkah 4: setelah gambar di `docs/images/` dan `docs/screenshots/` di-push ke GitHub (repository publik), gambar bisa ditautkan langsung tanpa diunggah satu per satu:

```bash
python3 scripts/buat-html-blogspot.py --base-url "https://raw.githubusercontent.com/fazdy/PBKK---C/main/Tugas/Tugas%20Pertemuan%207/docs"
```

Urutan adegan, narasi, dan persiapan rekaman video demo ada di `docs/Naskah-Video-Demo.md`.

## Push ke GitHub

Jalankan dari folder repository. Tambahkan hanya folder tugas ini dan README utama, jangan memakai `git add .`, agar file lain yang tidak terkait tidak ikut ter-commit.

```bash
cd ~/Documents/PBKK/Tugas/PBKK---C
```

```bash
git pull origin main
```

```bash
git add "Tugas/Tugas Pertemuan 7" README.md
```

```bash
git status
```

```bash
git commit -m "feat: tambahkan folder Tugas Pertemuan 7 (SIBASA - Sistem Informasi Bank Sampah)"
```

```bash
git push origin main
```

Pada `git status`, pastikan yang berstatus *Changes to be committed* hanya `README.md` dan file di dalam `Tugas/Tugas Pertemuan 7/`. Bila `git push` ditolak karena ada commit baru di GitHub, jalankan `git pull --rebase origin main`, lalu ulangi `git push origin main`.

## Tautan

| | |
|---|---|
| Repository GitHub | [github.com/fazdy/PBKK---C (folder Tugas Pertemuan 7)](https://github.com/fazdy/PBKK---C/tree/main/Tugas/Tugas%20Pertemuan%207) |
| Unduh aplikasi Windows dan macOS (Google Drive) | [Folder "Aplikasi SIBASA"](https://drive.google.com/drive/folders/1DBPBbS9SR2j5M1s-G2tdLhkhv0azft2h?usp=sharing) |
| Video demo | [LINK VIDEO DEMO] |
| Laporan (Blogspot) | [LINK POSTINGAN BLOGSPOT] |

## Kredit

- Ikon: [Material Design Icons](https://pictogrammers.com/library/mdi/) oleh Pictogrammers (Pictogrammers Free License).
- Palet warna grafik mengikuti palet kategorikal yang aman untuk buta warna.
- Data contoh (nama, alamat, nomor HP) fiktif. Harga sampah hanya ilustrasi.
