# Tugas Pertemuan 5 — Studi Kasus WPF: Student Management System

Implementasi studi kasus **Student Management System** menggunakan **WPF (.NET 8)** dengan pola **MVVM**, **Data Binding**, **Command**, **ADO.NET**, dan **SQL Server LocalDB**. Tampilan dibuat mengikuti desain *Rich UI* pada materi: form *Input Mahasiswa*, panel *Informasi* (statistik), *Search*, dan *DataGrid* dengan aksi *Edit/Hapus*.

| | |
|---|---|
| **Nama** | Faizal Aldy Armiriawan |
| **NRP** | 5025231266 |
| **Kelas** | PBKK (C) |

## Fitur

- **Input mahasiswa**: NIM, Nama, Jurusan, Gender, Email dengan tombol Simpan/Update, Hapus, dan Reset.
- **Panel INFORMASI**: Total Mahasiswa, Informatika, Sistem Informasi, Laki-laki, Perempuan. Nilainya diperbarui otomatis lewat Data Binding.
- **Pencarian** berdasarkan NIM, Nama, atau Jurusan (tombol *Cari* atau tekan Enter, lalu *Reset* untuk menampilkan semua data).
- **DataGrid** dengan nomor urut dan kolom **Aksi** (Edit dan Hapus di setiap baris).
- **Validasi input**: wajib isi, NIM hanya angka dan tidak boleh duplikat, format email. Ada juga **konfirmasi** sebelum menghapus data.
- **Mode Tambah/Edit**: badge mode dan teks tombol (Simpan ↔ Update) berubah otomatis.
- **Database otomatis**: `StudentDB`, tabel `Students`, dan 4 data contoh dibuat saat aplikasi pertama kali dijalankan. Script SQL manual tetap disediakan.
- **Penanganan error**: kegagalan koneksi database ditampilkan sebagai pesan, aplikasi tidak *crash*.

## Teknologi

| Layer | Teknologi |
|---|---|
| Bahasa | C# 12 |
| Framework | .NET 8 WPF (`net8.0-windows`) |
| UI | XAML (custom Style & ControlTemplate) |
| Arsitektur | MVVM |
| Database | SQL Server Express LocalDB `(localdb)\MSSQLLocalDB` |
| Data Access | ADO.NET — `Microsoft.Data.SqlClient` 7.1.1 |
| IDE | Visual Studio 2022 |

## Arsitektur

```
USER
  ↓
VIEW        MainWindow.xaml (Rich UI)
  ↓         Data Binding + Command
VIEWMODEL   StudentViewModel.cs (state, command, search, statistik, validasi)
  ↓
MODEL       Student.cs
  ↓
REPOSITORY  StudentRepository.cs (ADO.NET)
  ↓
SQL SERVER  StudentDB → tabel Students
```

## Struktur Folder

```
Tugas Pertemuan 5/
├── StudentManager.sln                   # Solution Visual Studio
├── StudentManager/                      # Project WPF (.NET 8)
│   ├── StudentManager.csproj
│   ├── App.xaml                         # Resource & Style Rich UI
│   ├── App.xaml.cs                      # Penanganan error global
│   ├── AssemblyInfo.cs
│   ├── MainWindow.xaml                  # View: header, form, informasi, search, DataGrid
│   ├── MainWindow.xaml.cs               # DataContext = new StudentViewModel()
│   ├── Models/
│   │   └── Student.cs                   # Model data mahasiswa
│   ├── ViewModels/
│   │   ├── StudentViewModel.cs          # State, command, search, statistik, validasi
│   │   └── RelayCommand.cs              # Implementasi ICommand
│   ├── Data/
│   │   └── StudentRepository.cs         # ADO.NET: GetAll, Search, Insert, Update, Delete
│   ├── Services/
│   │   ├── IDialogService.cs            # Abstraksi dialog untuk ViewModel
│   │   └── DialogService.cs             # Implementasi dengan MessageBox
│   └── Converters/
│       ├── RowNumberConverter.cs        # Nomor urut kolom "No"
│       └── EmptyStringToNullConverter.cs  # Mengosongkan pilihan ComboBox saat reset
├── Database/
│   └── StudentDB.sql                    # CREATE DATABASE, CREATE TABLE, data contoh
├── Screenshots/
│   └── tampilan-aplikasi.jpg            # Screenshot aplikasi di Windows
├── Tests/
│   └── StudentManager.LogicTests/       # Program uji logika lintas platform (74 skenario)
├── LAPORAN.md                           # Laporan pengerjaan (ringkas)
├── LAPORAN_BLOGSPOT.html                # Versi HTML laporan, siap ditempel ke Blogspot
└── README.md
```

## Cara Menjalankan (Windows)

> WPF dan SQL Server LocalDB hanya berjalan di **Windows**.

1. Install **Visual Studio 2022/2026** dengan workload **.NET desktop development**. Pastikan komponen **SQL Server Express LocalDB** ikut terpasang (*Visual Studio Installer → Modify → Individual components*).
2. Buka `StudentManager.sln`, lalu tekan **F5**. Paket NuGet akan di-*restore* otomatis.
3. Saat pertama kali dijalankan, aplikasi membuat database `StudentDB`, tabel `Students`, dan 4 data contoh secara otomatis. Bila ingin membuatnya manual, jalankan `Database/StudentDB.sql` melalui *SQL Server Object Explorer*.

Alternatif melalui terminal (butuh .NET 8 SDK di Windows):

```bash
dotnet run --project StudentManager
```

Connection string berada di `StudentManager/Data/StudentRepository.cs` (konstanta `DefaultConnectionString`):

```
Server=(localdb)\MSSQLLocalDB;Database=StudentDB;Trusted_Connection=True;TrustServerCertificate=True;
```

Jika memakai SQL Server Express, ganti bagian `Server` menjadi `.\SQLEXPRESS`.

### Muncul error "Unable to locate a Local Database Runtime installation" (error 52)

Artinya SQL Server LocalDB belum terpasang. Buka *Visual Studio Installer → Modify → Individual components*, cari `LocalDB`, centang **SQL Server Express LocalDB**, lalu klik *Modify*. Setelah itu cek di Command Prompt:

```bash
sqllocaldb info
```

Jika `MSSQLLocalDB` belum muncul, jalankan `sqllocaldb create MSSQLLocalDB -s`, lalu jalankan ulang aplikasi.

## Pengujian

Skenario pengujian (READ, CREATE, UPDATE, DELETE, SEARCH, STATISTICS, RESET) dijelaskan di [LAPORAN.md](LAPORAN.md#7-skenario-pengujian).

Logika aplikasi juga bisa diuji tanpa Windows dan tanpa SQL Server, misalnya di macOS atau Linux. Program uji memakai kode ViewModel asli dengan repository palsu (in-memory):

```bash
dotnet run --project Tests/StudentManager.LogicTests
```

Hasil: `HASIL: 74 lulus, 0 gagal`.

Cek kompilasi project WPF di macOS/Linux:

```bash
dotnet build StudentManager.sln -p:EnableWindowsTargeting=true
```

## Laporan

- [LAPORAN.md](LAPORAN.md): laporan ringkas berisi pendahuluan, langkah pengerjaan, kode inti, hasil, pengujian, pembahasan, dan kesimpulan.
- `LAPORAN_BLOGSPOT.html`: isi laporan yang sama dalam format HTML dengan screenshot sudah tertanam. Di Blogger, buka **New Post → HTML view**, lalu tempel seluruh isi file.
