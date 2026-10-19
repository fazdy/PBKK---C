# Panduan Instalasi Lingkungan Kerja SIBASA di MacBook Intel

> **Proyek:** SIBASA — Sistem Informasi Bank Sampah
> **Penyusun:** Faizal Aldy Armiriawan (NRP 5025231266) — PBKK (C)
> **Perangkat acuan:** MacBook Pro 2020, Intel Core i5-8257U (x86_64), RAM 8 GB, macOS Sequoia 15.8.1
> **Versi diperiksa pada:** 19 Oktober 2026 (dari situs resmi .NET dan NuGet)

Panduan ini ditulis untuk pemula. Ikuti berurutan. Setiap langkah diawali pengecekan, jadi kalau perangkat lunaknya sudah terpasang, langkah itu cukup diverifikasi lalu dilewati.

---

## 0. Ringkasan perangkat lunak yang dipakai

| Kebutuhan | Pilihan | Versi | Keterangan |
|---|---|---|---|
| Runtime dan bahasa | .NET SDK (C#) | **10.0.402** (runtime 10.0.13) | Rilis LTS, didukung sampai **14 November 2028**, mendukung macOS 15 x64 |
| Framework UI | Avalonia UI | **11.3.22** | Memakai XAML seperti WPF dan berjalan di macOS, Windows, dan Linux |
| Pola MVVM | CommunityToolkit.Mvvm | **8.4.2** | `ObservableObject`, `[ObservableProperty]`, `[RelayCommand]` |
| Grafik | LiveChartsCore.SkiaSharpView.Avalonia | **2.0.5** | Grafik batang dan pie untuk dashboard |
| Database | SQLite melalui Microsoft.Data.Sqlite | **10.0.13** | Database berupa file, tanpa server dan tanpa Docker |
| Editor | Visual Studio Code + C# Dev Kit | terbaru | Alternatif: JetBrains Rider (gratis untuk non-komersial) |
| Pengelola database | DB Browser for SQLite | terbaru | Alternatif: ekstensi SQLite Viewer di VS Code, atau `sqlite3` bawaan macOS |
| Version control | Git + GitHub | Git 2.48 | Repository: `https://github.com/fazdy/PBKK---C` |

### Mengapa versi ini yang dipilih

- **.NET 10, bukan .NET 8.** .NET 8 memang LTS, tetapi dukungannya berakhir **10 November 2026**, kurang dari sebulan lagi. .NET 10 adalah LTS terbaru dan didukung sampai 2028. Daftar OS resmi .NET 10 mencantumkan macOS 15, 26, dan 27 untuk arsitektur x64 (Intel), jadi Mac ini didukung.
- **.NET 11 tidak dipakai.** Saat panduan ini ditulis, .NET 11 masih Release Candidate. Lagi pula, .NET 11 adalah rilis STS (dukungan pendek), bukan LTS.
- **Avalonia 11.3, bukan Avalonia 12.** Versi terbaru Avalonia adalah 12.1, tetapi paket grafik LiveCharts2 versi stabil (2.0.5) masih dibangun untuk Avalonia 11. Avalonia 12 membawa banyak perubahan yang tidak kompatibel (*breaking changes*), jadi memasangkannya dengan LiveCharts berisiko membuat grafik gagal tampil. Avalonia 11.3 masih dipelihara (patch terbaru 11.3.22) dan aman dipakai.
- **SQLite, bukan SQL Server.** Dosen mengizinkan database selain SQL Server. SQLite dipilih karena tiga alasan:
  1. tidak butuh Docker, sehingga hemat RAM (laptop ini hanya 8 GB, sedangkan container SQL Server butuh minimal 2 GB);
  2. file aplikasi yang diunggah ke Google Drive bisa langsung dijalankan dosen tanpa memasang server database;
  3. query berparameter, constraint, foreign key, dan transaksi tetap tersedia, sehingga syarat keamanan dan integritas data di PRD tetap terpenuhi.

---

## 1. Mengecek versi macOS dan memastikan prosesor Intel

### Lewat menu Apple

1. Klik logo **Apple** di pojok kiri atas, lalu pilih **About This Mac** (Tentang Mac Ini).
2. Perhatikan baris **Processor** (Prosesor). Di laptop ini tertulis `1,4 GHz Quad-Core Intel Core i5`.
   - Kalau tertulis **Intel**, pakai semua installer versi **x64**.
   - Kalau tertulis **Chip Apple M1/M2/M3...**, pakai versi **Arm64** (bukan untuk laptop ini).
3. Perhatikan baris **macOS**. Laptop ini memakai **Sequoia 15.8.1**. .NET 10 membutuhkan minimal macOS 15.

### Lewat Terminal

Buka **Terminal** (tekan `⌘ + Spasi`, ketik `Terminal`, lalu Enter), kemudian jalankan:

```bash
uname -m
```

Hasil yang benar untuk Mac Intel adalah `x86_64`.

```bash
sw_vers
```

Perintah ini menampilkan `ProductVersion: 15.8.1` (atau versi macOS-mu).

```bash
sysctl -n machdep.cpu.brand_string
```

Perintah ini menampilkan nama prosesor, misalnya `Intel(R) Core(TM) i5-8257U CPU @ 1.40GHz`.

---

## 2. Memasang .NET SDK 10 (x64)

### 2.1 Cek dulu apa yang sudah ada

```bash
dotnet --list-sdks
```

```bash
which -a dotnet
```

> **Catatan khusus laptop ini.** Hasil pengecekan menunjukkan ada **dua lokasi .NET**:
>
> | Lokasi | Isi SDK | Cara terpasang |
> |---|---|---|
> | `~/.dotnet` | 6.0, 7.0, 8.0.425 | skrip `dotnet-install.sh` |
> | `/usr/local/share/dotnet` | **10.0.401** | installer `.pkg` resmi |
>
> File `~/.zshrc` berisi baris `export PATH="$HOME/.dotnet:$PATH"`. Akibatnya perintah `dotnet` selalu memakai `~/.dotnet`, sehingga SDK 10 yang sebenarnya sudah terpasang **tidak terlihat**. Cara merapikannya ada di langkah 2.3.

### 2.2 Cara A (untuk pemula): installer resmi `.pkg`

1. Buka halaman resmi: **https://dotnet.microsoft.com/download/dotnet/10.0**
2. Pada tabel **SDK 10.0.402**, baris **macOS**, klik **x64** di kolom *Installers*. **Jangan pilih Arm64.**
   Link langsungnya: https://builds.dotnet.microsoft.com/dotnet/Sdk/10.0.402/dotnet-sdk-10.0.402-osx-x64.pkg
3. Buka file `.pkg` yang terunduh, lalu ikuti wizard sampai selesai. Installer meminta password akun Mac.
4. SDK terpasang di `/usr/local/share/dotnet`.

### 2.3 Cara B (disarankan untuk laptop ini): skrip resmi ke `~/.dotnet`

Karena SDK 6, 7, dan 8 milik tugas-tugas sebelumnya sudah ada di `~/.dotnet`, paling rapi kalau SDK 10 juga dipasang di tempat yang sama. Dengan begitu semua versi berada di satu lokasi, tugas lama (.NET 8) tetap bisa dijalankan, dan tidak perlu password admin.

```bash
curl -sSL https://dot.net/v1/dotnet-install.sh -o ~/Downloads/dotnet-install.sh
```

```bash
bash ~/Downloads/dotnet-install.sh --channel 10.0 --install-dir ~/.dotnet
```

Skrip ini resmi dari Microsoft. Halaman dokumentasinya: https://learn.microsoft.com/dotnet/core/tools/dotnet-install-script

> **Alternatif tanpa memasang ulang:** jalankan aplikasi dengan path lengkap SDK yang sudah ada, misalnya `/usr/local/share/dotnet/dotnet run`. Cara ini berguna untuk mencoba, tetapi kurang praktis untuk dipakai setiap hari.

### 2.4 Verifikasi

Tutup Terminal, buka lagi, lalu jalankan:

```bash
dotnet --list-sdks
```

Harus muncul baris `10.0.402 [...]` (atau `10.0.4xx` lain).

```bash
dotnet --version
```

Di luar folder proyek, perintah ini menampilkan versi SDK tertinggi, yaitu `10.0.402`.

---

## 3. Memasang editor

### Pilihan 1: Visual Studio Code (sudah terpasang di laptop ini)

1. Unduh dari **https://code.visualstudio.com/** lalu pilih **Mac** (installer universal, jalan di Intel). Lewati langkah ini kalau `Visual Studio Code.app` sudah ada di folder Applications.
2. Buka VS Code, tekan `⌘ + Shift + X`, lalu pasang ekstensi berikut:
   - **C# Dev Kit** (Microsoft). Ekstensi **C#** ikut terpasang otomatis.
   - **Avalonia for VSCode** (AvaloniaTeam), untuk *syntax highlighting* dan pratinjau file `.axaml`.
   - **SQLite Viewer** (Florian Klampfer), opsional, untuk membuka file `sibasa.db` langsung di VS Code.
3. Supaya bisa membuka VS Code dari Terminal dengan perintah `code .`: tekan `⌘ + Shift + P`, ketik **Shell Command: Install 'code' command in PATH**, lalu Enter.

> C# Dev Kit memakai lisensi Visual Studio Community. Untuk mahasiswa dan proyek kuliah, penggunaannya gratis.

### Pilihan 2: JetBrains Rider

1. Unduh dari **https://www.jetbrains.com/rider/download/** dan pilih **macOS (Intel)**.
2. Saat pertama kali dibuka, pilih lisensi **Non-commercial use** (gratis untuk belajar).
3. Rider sudah mendukung `.axaml`. Plugin **AvaloniaRider** bisa dipasang untuk pratinjau XAML.

---

## 4. Memasang template Avalonia dan membuat proyek

### 4.1 Cek template yang sudah terpasang

```bash
dotnet new list avalonia
```

Kalau muncul `avalonia.app` dan `avalonia.mvvm`, template sudah ada. Laptop ini sudah memasang **Avalonia.Templates 11.3.12**.

### 4.2 Memasang (atau memastikan) template versi 11.3

Versi template ditentukan dengan sengaja, karena template terbaru (12.x) membuat proyek Avalonia 12, sedangkan proyek ini memakai Avalonia 11.3 (lihat alasannya di bagian 0).

```bash
dotnet new install Avalonia.Templates::11.3.12
```

### 4.3 Membuat proyek baru (contoh)

Proyek SIBASA di repository sudah lengkap, jadi langkah ini hanya contoh cara membuat proyek Avalonia dari nol:

```bash
mkdir -p ~/Documents/latihan-avalonia && cd ~/Documents/latihan-avalonia
```

```bash
dotnet new avalonia.mvvm -o ContohApp
```

```bash
cd ContohApp && dotnet run
```

Kalau sebuah jendela aplikasi muncul, berarti Avalonia sudah siap dipakai.

### 4.4 Menjalankan proyek SIBASA

```bash
cd ~/Documents/PBKK/Tugas/PBKK---C/"Tugas/Tugas Pertemuan 7/src/SIBASA"
```

```bash
dotnet run
```

Pada saat pertama dijalankan, `dotnet` mengunduh paket NuGet (butuh internet, sekitar 1–3 menit). Database SQLite dan data contohnya dibuat otomatis.

### 4.5 Menjalankan tes otomatis

```bash
cd ~/Documents/PBKK/Tugas/PBKK---C/"Tugas/Tugas Pertemuan 7"
```

```bash
dotnet test SIBASA.sln
```

Hasil yang benar diakhiri baris `Passed! - Failed: 0, Passed: 54`.

### 4.6 Membuat paket unduhan (Windows dan macOS)

```bash
./scripts/publish.sh
```

Skrip ini membuat dua file di folder `release/`, yaitu `SIBASA-1.0.0-Windows-x64.zip` dan `SIBASA-1.0.0-macOS-Intel.zip`, yang siap diunggah ke Google Drive. File `.exe` Windows dibangun langsung dari Mac (*cross-compile*), dan keduanya sudah membawa runtime .NET sehingga penguji tidak perlu memasang apa pun. Bila perintah `dotnet` belum mengenali SDK 10, jalankan `DOTNET=/usr/local/share/dotnet/dotnet ./scripts/publish.sh`.

---

## 5. Database: SQLite (tanpa server, tanpa Docker)

Dosen awalnya menyarankan SQL Server di Docker, tetapi kemudian mengizinkan database lain. Proyek ini memakai **SQLite**, jadi **tidak perlu memasang Docker maupun SQL Server**.

### 5.1 Apa yang perlu dipasang?

Tidak ada. Mesin SQLite sudah ikut di dalam paket NuGet `Microsoft.Data.Sqlite` dan terbawa bersama aplikasi, termasuk di file `.exe` untuk Windows.

### 5.2 Di mana file database disimpan?

Aplikasi membuat file `sibasa.db` di folder data milik pengguna:

| Sistem operasi | Lokasi file |
|---|---|
| macOS | `~/Library/Application Support/SIBASA/sibasa.db` |
| Windows | `C:\Users\<nama>\AppData\Local\SIBASA\sibasa.db` |

Lokasinya bisa diubah lewat `appsettings.json` (kunci `Database:Path`). Kalau file database dihapus, aplikasi akan membuat ulang tabel dan data contohnya saat dijalankan lagi.

### 5.3 Mengecek SQLite bawaan macOS (opsional)

macOS sudah membawa program `sqlite3`:

```bash
sqlite3 --version
```

Contoh membuka database SIBASA dan menampilkan daftar tabel:

```bash
sqlite3 ~/Library/Application\ Support/SIBASA/sibasa.db ".tables"
```

---

## 6. Memasang pengelola database: DB Browser for SQLite

DB Browser for SQLite adalah aplikasi gratis dan *open source* untuk melihat dan mengedit isi database SQLite secara visual.

### Cara A: unduh dari situs resmi

1. Buka **https://sqlitebrowser.org/dl/**
2. Di bagian **macOS**, pilih **DB Browser for SQLite (Intel)**, yaitu file `.dmg` versi Intel.
3. Buka file `.dmg`, lalu seret ikon aplikasi ke folder **Applications**.
4. Saat pertama dibuka, macOS mungkin menolak karena aplikasi berasal dari internet. Solusinya ada di bagian 8.

### Cara B: lewat Homebrew (sudah terpasang di laptop ini)

```bash
brew install --cask db-browser-for-sqlite
```

### Menghubungkan ke database SIBASA

1. Jalankan aplikasi SIBASA minimal satu kali supaya file database terbentuk.
2. Buka DB Browser for SQLite, lalu klik **Open Database**.
3. Tekan `⌘ + Shift + G`, tempel path `~/Library/Application Support/SIBASA/`, lalu pilih `sibasa.db`.
4. Tab **Database Structure** menampilkan tabel. Tab **Browse Data** menampilkan isinya.
5. **Penting:** tutup database di DB Browser (`⌘ + W`) sebelum mengedit data dari aplikasi SIBASA, supaya tidak muncul error *database is locked*.

> **Alternatif:** ekstensi **SQLite Viewer** di VS Code (lihat langkah 3). Klik file `sibasa.db` di Explorer VS Code untuk melihat isinya dalam mode baca.

---

## 7. Memasang Git dan menghubungkannya ke GitHub

### 7.1 Cek Git

```bash
git --version
```

Laptop ini sudah memiliki `git version 2.48.1`. Kalau belum ada, jalankan `xcode-select --install` atau `brew install git`.

### 7.2 Atur identitas (sekali saja)

```bash
git config --global user.name "Faizal Aldy Armiriawan"
```

```bash
git config --global user.email "email-github-kamu@example.com"
```

Gunakan email yang sama dengan akun GitHub.

### 7.3 Login ke GitHub dari Terminal

Cara paling mudah adalah memakai **GitHub CLI**:

```bash
brew install gh
```

```bash
gh auth login
```

Pilih **GitHub.com**, lalu **HTTPS**, lalu **Login with a web browser**, dan ikuti kode yang muncul.

> Alternatifnya adalah *Personal Access Token*: GitHub → Settings → Developer settings → Personal access tokens. Token dipakai sebagai pengganti password saat `git push`. Jangan pernah menaruh token di dalam kode atau di repository.

### 7.4 Repository tugas

Repository tugas PBKK sudah terhubung ke `origin`:

```bash
cd ~/Documents/PBKK/Tugas/PBKK---C && git remote -v
```

Hasilnya: `origin https://github.com/fazdy/PBKK---C.git`. Perintah untuk *commit* dan *push* Tugas Pertemuan 7 ada di `README.md`.

---

## 8. Masalah umum dan solusinya

| No | Gejala | Penyebab | Solusi |
|---|---|---|---|
| 1 | `dotnet --list-sdks` tidak menampilkan 10.0 padahal sudah memasang `.pkg` | `~/.dotnet` berada di depan PATH (lihat `~/.zshrc`) | Pasang SDK 10 ke `~/.dotnet` dengan skrip (langkah 2.3), atau jalankan dengan `/usr/local/share/dotnet/dotnet run` |
| 2 | `zsh: command not found: dotnet` | PATH belum memuat lokasi .NET | Tambahkan `export PATH="$HOME/.dotnet:$PATH"` ke `~/.zshrc`, lalu jalankan `source ~/.zshrc` |
| 3 | `error NETSDK1045: The current .NET SDK does not support targeting .NET 10.0` | SDK yang aktif masih 8.0 | Selesaikan masalah no. 1, lalu cek dengan `dotnet --list-sdks` |
| 4 | `error NU1301: Unable to load the service index` saat `dotnet run` | Tidak ada internet, atau jaringan kampus memblokir NuGet | Pastikan internet tersambung (coba hotspot), lalu jalankan `dotnet restore` |
| 5 | Muncul dialog "*Apple could not verify ... is free of malware*" atau "*app is damaged*" saat membuka SIBASA.app atau DB Browser | Gatekeeper macOS memblokir aplikasi yang tidak ditandatangani Apple | Buka **System Settings → Privacy & Security**, gulir ke bawah, lalu klik **Open Anyway**. Atau jalankan `xattr -cr /Applications/SIBASA.app` |
| 6 | Windows menampilkan "*Windows protected your PC*" (SmartScreen) saat membuka `SIBASA.exe` | `.exe` tidak memiliki sertifikat *code signing* | Klik **More info**, lalu **Run anyway** |
| 7 | `SQLite Error 5: 'database is locked'` | File `sibasa.db` sedang dibuka dan dikunci aplikasi lain (misalnya DB Browser) | Tutup database di DB Browser (`⌘ + W`), lalu ulangi aksi di SIBASA |
| 8 | `SQLite Error 1: 'no such table: ...'` | File database rusak atau dibuat oleh versi skema lama | Tutup aplikasi, hapus `~/Library/Application Support/SIBASA/sibasa.db`, lalu jalankan ulang. Tabel dan data contoh akan dibuat lagi |
| 9 | Terminal atau VS Code meminta izin "*would like to access files in your Documents folder*" | Fitur privasi macOS | Klik **Allow**. Kalau terlanjur ditolak, buka **System Settings → Privacy & Security → Files and Folders**, lalu aktifkan akses untuk Terminal/VS Code |
| 10 | Pratinjau `.axaml` di VS Code kosong atau error | Previewer butuh hasil build terbaru | Jalankan `dotnet build` sekali, lalu buka ulang file `.axaml` |
| 11 | Laptop terasa lambat atau kipas berisik saat build | RAM 8 GB dipakai banyak aplikasi sekaligus | Tutup browser dengan banyak tab, aplikasi Docker, dan IDE lain yang tidak dipakai. SIBASA sendiri tidak butuh Docker |
| 12 | Grafik dashboard tidak muncul, dengan error terkait `LiveChartsCore` | Versi Avalonia tidak cocok dengan LiveCharts | Pastikan semua paket `Avalonia.*` memakai versi 11.3.x seperti di `SIBASA.csproj`, jangan diperbarui ke 12.x |
| 13 | `git push` ditolak dengan `Authentication failed` | GitHub tidak lagi menerima password akun untuk Git | Login dengan `gh auth login` atau pakai *Personal Access Token* (langkah 7.3) |
| 14 | Penyimpanan hampir penuh | Folder `bin/`, `obj/`, dan cache NuGet menumpuk | Jalankan `dotnet clean` di folder proyek. Cache NuGet bisa dibersihkan dengan `dotnet nuget locals all --clear` |

---

## 9. Daftar periksa akhir

- [ ] `uname -m` menampilkan `x86_64`
- [ ] `dotnet --list-sdks` memuat `10.0.4xx`
- [ ] VS Code + C# Dev Kit + Avalonia for VSCode terpasang (atau Rider)
- [ ] `dotnet new list avalonia` menampilkan template `avalonia.mvvm`
- [ ] `dotnet run` di folder `src/SIBASA` membuka jendela login SIBASA
- [ ] `dotnet test SIBASA.sln` menampilkan 54 tes lulus
- [ ] DB Browser for SQLite bisa membuka `sibasa.db`
- [ ] `git remote -v` menampilkan repository GitHub dan `gh auth status` sudah login
