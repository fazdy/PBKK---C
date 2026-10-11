# Tugas Pertemuan 6 — Student Registration (SQL Server + ADO.NET) di macOS

Implementasi **Hands-On Lab 1** (database `StudentRegistrationDB` di SQL Server) dan **Hands-On Lab 2** (aplikasi desktop C# yang menampilkan data mahasiswa dari SQL Server menggunakan ADO.NET).

Karena tugas dikerjakan di **MacBook**, sesuai izin dosen aplikasi desktop dibuat dengan **Avalonia UI** (XAML lintas platform yang sangat mirip WPF), sedangkan **SQL Server 2022** dijalankan melalui **Docker (Colima)**.

| | |
|---|---|
| **Nama** | Faizal Aldy Armiriawan |
| **NRP** | 5025231266 |
| **Kelas** | PBKK (C) |

## Fitur

- Database `StudentRegistrationDB` dengan tabel `Programs` dan `Students`, relasi **1:N** (PRIMARY KEY, UNIQUE, FOREIGN KEY).
- Script SQL lengkap: CREATE, INSERT, SELECT, JOIN, WHERE, COUNT, GROUP BY, uji FK dan UNIQUE, serta *mini assignment*.
- Aplikasi **Student Registration** (Avalonia UI): DataGrid berisi data mahasiswa hasil JOIN, tombol **Refresh**, kolom **Alamat** dan **Dibuat**, dan **Total Mahasiswa** (*mini challenge* 1–3).
- Akses data dengan ADO.NET: `SqlConnection`, `SqlCommand`, `SqlDataReader`, `List<Student>`.

## Teknologi

| Kebutuhan di modul (Windows) | Pengganti di macOS |
|---|---|
| WPF | Avalonia UI 11.3.12 (.NET 8) |
| SQL Server Express/Developer | SQL Server 2022 Developer di Docker (Colima) |
| SQL Server Management Studio (SSMS) | DBeaver (atau `sqlcmd` di dalam container) |
| Windows Authentication | SQL Server Authentication (user `sa`) |
| Visual Studio | Visual Studio Code + C# Dev Kit + Avalonia |
| `Microsoft.Data.SqlClient` | `Microsoft.Data.SqlClient` 7.1.1 (sama) |

## Struktur Folder

```
Tugas Pertemuan 6/
├── StudentRegistration.sln
├── StudentRegistration/                 # Project Avalonia (.NET 8)
│   ├── StudentRegistration.csproj
│   ├── Program.cs                       # Entry point Avalonia
│   ├── App.axaml, App.axaml.cs          # Tema Fluent + DataGrid
│   ├── MainWindow.axaml                 # UI: judul, Refresh, Total, DataGrid
│   ├── MainWindow.axaml.cs              # LoadStudents(), RefreshButton_Click
│   ├── Models/
│   │   ├── Student.cs                   # Model mahasiswa
│   │   └── Program.cs                   # Model program studi
│   └── Repositories/
│       └── StudentRepository.cs         # ADO.NET: GetAll(), GetTotalStudents()
├── Database/
│   └── StudentRegistrationDB.sql        # Seluruh script Hands-On Lab 1
├── Screenshots/
├── LAPORAN.md
├── LAPORAN_BLOGSPOT.html
└── README.md
```

## Persiapan di Mac (sekali saja)

Sudah terpasang sebelumnya: .NET 8 SDK, Visual Studio Code (+ C# Dev Kit, Avalonia), DBeaver, Homebrew.

1. **Install Colima dan Docker CLI.** Colima adalah Docker ringan berbasis terminal, cocok untuk RAM 8 GB.

   ```bash
   brew install colima docker
   ```

2. **Nyalakan Colima** (2 CPU, RAM 4 GB, disk 20 GB):

   ```bash
   colima start --cpu 2 --memory 4 --disk 20 --vm-type vz
   ```

3. **Jalankan SQL Server 2022** (pertama kali akan mengunduh image ±1,6 GB):

   ```bash
   docker run -d --name sqlserver \
     -e "ACCEPT_EULA=Y" \
     -e "MSSQL_SA_PASSWORD=Pbkk@2026" \
     -e "MSSQL_MEMORY_LIMIT_MB=2048" \
     -e "TZ=Asia/Jakarta" \
     -p 1433:1433 \
     -v sqlserver-data:/var/opt/mssql \
     mcr.microsoft.com/mssql/server:2022-latest
   ```

4. **Buat database** dengan menjalankan `Database/StudentRegistrationDB.sql`. Pilih salah satu cara:
   - **DBeaver**: *New Database Connection → SQL Server*, isi Host `localhost`, Port `1433`, Authentication *SQL Server Authentication*, Username `sa`, Password `Pbkk@2026`, lalu aktifkan *Trust Server Certificate*. Buka *SQL Editor*, tempel script, lalu *Execute SQL Script* (Option+X).
   - **Terminal** (`sqlcmd` sudah ada di dalam container):

     ```bash
     docker cp Database/StudentRegistrationDB.sql sqlserver:/tmp/StudentRegistrationDB.sql
     ```
     ```bash
     docker exec sqlserver /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "Pbkk@2026" -C -i /tmp/StudentRegistrationDB.sql
     ```

   Dua pesan error terakhir (FOREIGN KEY dan UNIQUE KEY) memang disengaja, karena merupakan pengujian LAB 19 dan LAB 20.

## Menjalankan Aplikasi

```bash
dotnet run --project StudentRegistration
```

Setelah selesai, matikan Colima agar RAM kembali lega:

```bash
colima stop
```

Untuk memakai lagi di lain waktu, jalankan perintah berikut, lalu `dotnet run` seperti biasa:

```bash
colima start && docker start sqlserver
```

## Connection String

Berada di `StudentRegistration/Repositories/StudentRepository.cs`:

```
Server=localhost,1433;Database=StudentRegistrationDB;User Id=sa;Password=Pbkk@2026;TrustServerCertificate=True;
```

Password di atas hanya untuk SQL Server lokal di Docker. Untuk menjalankan di Windows dengan SQL Server Express, isi environment variable `STUDENT_DB_CONNECTION`:

```
Server=.\SQLEXPRESS;Database=StudentRegistrationDB;Trusted_Connection=True;TrustServerCertificate=True;
```

## Troubleshooting

| Masalah | Solusi |
|---|---|
| Dialog *Database Error* saat aplikasi dibuka | Pastikan `colima status` berjalan dan `docker ps` menampilkan container `sqlserver` |
| `Cannot open database "StudentRegistrationDB"` | Script `Database/StudentRegistrationDB.sql` belum dijalankan |
| `Login failed for user 'sa'` | Password di connection string harus sama dengan `MSSQL_SA_PASSWORD` |
| Container berhenti sendiri | RAM Colima kurang; pastikan Colima memakai `--memory 4` |

## Laporan

- [LAPORAN.md](LAPORAN.md): laporan pengerjaan dari persiapan hingga kesimpulan.
- `LAPORAN_BLOGSPOT.html`: isi laporan dalam format HTML dengan screenshot tertanam. Di Blogger, buka **New Post → HTML view**, lalu tempel seluruh isi file.
