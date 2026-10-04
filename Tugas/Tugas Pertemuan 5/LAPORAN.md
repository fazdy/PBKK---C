# Laporan Tugas Pertemuan 5 — Pemrograman Berbasis Kerangka Kerja (PBKK)
## Studi Kasus WPF: Student Management System (Rich UI + MVVM + Data Binding + SQL Server)

| | |
|---|---|
| **Nama** | Faizal Aldy Armiriawan |
| **NRP** | 5025231266 |
| **Kelas** | PBKK (C) |
| **Topik** | Studi Kasus WPF — Student Management System |
| **Kode Program** | [github.com/fazdy/PBKK---C — Tugas Pertemuan 5](https://github.com/fazdy/PBKK---C/tree/main/Tugas/Tugas%20Pertemuan%205) |

---

### Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Tujuan](#2-tujuan)
3. [Alat dan Bahan](#3-alat-dan-bahan)
4. [Deskripsi Studi Kasus dan Fitur](#4-deskripsi-studi-kasus-dan-fitur)
5. [Desain Antarmuka Rich UI](#5-desain-antarmuka-rich-ui)
6. [Arsitektur MVVM](#6-arsitektur-mvvm)
7. [Struktur Project](#7-struktur-project)
8. [Langkah Pengerjaan](#8-langkah-pengerjaan)
9. [Memahami Data Binding](#9-memahami-data-binding)
10. [Alur CRUD dan Pencarian](#10-alur-crud-dan-pencarian)
11. [Pengujian Aplikasi](#11-pengujian-aplikasi)
12. [Pembahasan](#12-pembahasan)
13. [Kesimpulan](#13-kesimpulan)
14. [Referensi](#14-referensi)

---

### 1. Pendahuluan

**Windows Presentation Foundation (WPF)** adalah framework .NET untuk membangun aplikasi desktop Windows dengan antarmuka yang modern dan kaya fitur (*Rich UI*). Windows Forms bersifat *event-driven* dan logikanya cenderung menumpuk di *code-behind*. WPF berbeda: **tampilan** ditulis secara deklaratif menggunakan **XAML**, terpisah dari **logika aplikasi** yang ditulis dengan C#.

Kekuatan WPF bukan hanya pada tampilan, tetapi pada kombinasi beberapa konsep berikut:

- **XAML**: bahasa markup untuk mendefinisikan antarmuka secara deklaratif.
- **Data Binding**: menghubungkan properti di ViewModel dengan kontrol UI secara otomatis, satu arah maupun dua arah.
- **Command (`ICommand`)**: menghubungkan tombol dengan method di ViewModel tanpa event `Click` di *code-behind*.
- **Style dan ControlTemplate**: mengatur tampilan kontrol secara konsisten di seluruh aplikasi.
- **MVVM (Model–View–ViewModel)**: pola arsitektur yang memisahkan tampilan, logika aplikasi, dan data.

Pada pertemuan ini dikerjakan studi kasus **Student Management System**, yaitu aplikasi desktop untuk mengelola data mahasiswa yang tersimpan di **SQL Server LocalDB** dan diakses menggunakan **ADO.NET**. Tampilan aplikasi mengikuti rancangan UI pada materi: form *Input Mahasiswa*, panel *Informasi* (statistik), kolom *Search*, dan *DataGrid* dengan aksi *Edit/Delete*.

---

### 2. Tujuan

1. Membuat Rich UI dengan WPF dan XAML.
2. Memahami peran Model, View, dan ViewModel.
3. Menggunakan Data Binding untuk input, output, DataGrid, dan seleksi data.
4. Menggunakan `ICommand` (RelayCommand) untuk aksi tombol.
5. Menggunakan `ObservableCollection` dan `INotifyPropertyChanged`.
6. Mengakses SQL Server menggunakan ADO.NET (`Microsoft.Data.SqlClient`).
7. Mengimplementasikan CRUD, pencarian, dan statistik.
8. Memahami alur WPF → MVVM → Repository → SQL Server.

---

### 3. Alat dan Bahan

| No | Kebutuhan | Keterangan |
|---|---|---|
| 1 | Bahasa | C# 12 |
| 2 | Framework | .NET 8 — WPF (`net8.0-windows`) |
| 3 | UI | XAML |
| 4 | Arsitektur | MVVM |
| 5 | Data Binding | WPF Binding (`INotifyPropertyChanged`, `ObservableCollection`) |
| 6 | Database | SQL Server Express LocalDB — instance `(localdb)\MSSQLLocalDB` |
| 7 | Data Access | ADO.NET |
| 8 | SQL Provider | NuGet `Microsoft.Data.SqlClient` versi 7.1.1 |
| 9 | IDE | Visual Studio 2022 (workload *.NET desktop development* + komponen *SQL Server Express LocalDB*) |
| 10 | Sistem operasi aplikasi | Windows 10/11 64-bit |
| 11 | Perangkat penulisan kode | MacBook Pro 2020 (Intel Core i5), macOS Sequoia 15.8.1, .NET SDK 8.0.425 |

> **Catatan lingkungan.** WPF dan SQL Server LocalDB hanya dapat berjalan di Windows. Kode program ditulis di macOS dan dikompilasi silang (*cross-compile*) menggunakan opsi `EnableWindowsTargeting` untuk memastikan tidak ada error. Aplikasi kemudian dijalankan dan diuji di Windows menggunakan Visual Studio 2022. MacBook berprosesor Intel dapat menjalankan Windows melalui Boot Camp atau *virtual machine*.

---

### 4. Deskripsi Studi Kasus dan Fitur

Student Management System adalah aplikasi desktop berbasis WPF untuk mengelola data mahasiswa (NIM, Nama, Jurusan, Gender, Email). Data disimpan pada tabel `Students` di database `StudentDB`.

**Fitur sesuai modul:**

- Input mahasiswa: NIM, Nama, Jurusan, Gender, Email.
- Statistik: Total Mahasiswa, Informatika, Sistem Informasi, Laki-laki, Perempuan.
- Pencarian berdasarkan NIM, Nama, atau Jurusan.
- Data mahasiswa ditampilkan dalam DataGrid.
- Edit/Update, Delete, dan Reset.

**Pengembangan tambahan:**

| Fitur | Keterangan |
|---|---|
| Kolom **No** dan **Aksi** di DataGrid | Nomor urut otomatis serta tombol Edit dan Hapus di setiap baris, sesuai desain UI |
| Validasi input | NIM & Nama wajib diisi, NIM hanya angka dan tidak boleh duplikat, Jurusan & Gender wajib dipilih, format email diperiksa (email opsional) |
| Konfirmasi hapus | Dialog *Yes/No* sebelum data dihapus |
| Mode Tambah / Mode Edit | Badge mode dan teks tombol berubah otomatis (Simpan ↔ Update) |
| Pencarian dengan Enter dan tombol Reset | Tombol Reset menampilkan kembali seluruh data |
| Status bar | Menampilkan pesan hasil setiap aksi dan jumlah data yang sedang tampil |
| Inisialisasi database otomatis | Database, tabel, dan data contoh dibuat otomatis jika belum ada |
| Penanganan error | Kegagalan koneksi database tidak membuat aplikasi *crash*, pesan error ditampilkan |
| Custom Style & ControlTemplate | Tombol, TextBox (dengan *placeholder*), ComboBox, dan DataGrid bergaya modern |

---

### 5. Desain Antarmuka Rich UI

Jendela utama berukuran 1200 × 750 piksel. Kontennya dibagi menjadi lima baris menggunakan `Grid`:

```text
┌──────────────────────────────────────────────────────────────────────┐
│ Row 0  HEADER     STUDENT MANAGEMENT SYSTEM          tanggal, Admin  │
├──────────────────────────────────┬───────────────────────────────────┤
│ Row 1  INPUT MAHASISWA           │ INFORMASI                         │
│        NIM, Nama, Jurusan,       │ Total Mahasiswa                   │
│        Gender, Email             │ Informatika  | Sistem Informasi   │
│        [Simpan] [Hapus] [Reset]  │ Laki-laki    | Perempuan          │
├──────────────────────────────────┴───────────────────────────────────┤
│ Row 2  SEARCH     [ kata kunci ................ ]  [Cari] [Reset]    │
├──────────────────────────────────────────────────────────────────────┤
│ Row 3  DATAGRID   No | NIM | Nama | Jurusan | Gender | Email | Aksi  │
├──────────────────────────────────────────────────────────────────────┤
│ Row 4  STATUS     pesan hasil aksi            Data ditampilkan: n    │
└──────────────────────────────────────────────────────────────────────┘
```

Pemetaan komponen UI ke kontrol WPF:

| Komponen UI | Implementasi WPF | Binding / Command |
|---|---|---|
| NIM / Nama / Email | `TextBox` | `CurrentStudent.NIM`, `CurrentStudent.Nama`, `CurrentStudent.Email` |
| Jurusan / Gender | `ComboBox` | `ItemsSource` = `JurusanOptions` / `GenderOptions`, `SelectedItem` = `CurrentStudent.Jurusan` / `Gender` |
| Simpan / Hapus / Reset | `Button` + Command | `SaveCommand`, `DeleteCommand`, `ResetCommand` |
| Search | `TextBox` + `Button` | `SearchText`, `SearchCommand`, `ClearSearchCommand` |
| Statistik | `TextBlock` + Binding | `TotalStudents`, `TotalInformatika`, `TotalSistemInformasi`, `TotalLakiLaki`, `TotalPerempuan` |
| Tabel | `DataGrid` | `ItemsSource` = `Students`, `SelectedItem` = `SelectedStudent` |
| Edit / Hapus per baris | `Button` di `DataGridTemplateColumn` | `EditCommand` / `DeleteCommand` + `CommandParameter="{Binding}"` |

---

### 6. Arsitektur MVVM

```text
USER
  ↓
VIEW         MainWindow.xaml (Rich UI)
  ↓          Data Binding + Command
VIEWMODEL    StudentViewModel.cs (state, command, search, statistik, validasi)
  ↓
MODEL        Student.cs
  ↓
REPOSITORY   StudentRepository.cs (ADO.NET)
  ↓
SQL SERVER   LocalDB → StudentDB → tabel Students
```

| Layer | File | Tanggung Jawab |
|---|---|---|
| View | `MainWindow.xaml`, `App.xaml` | Menampilkan UI. Hanya berisi binding, tanpa logika aplikasi |
| ViewModel | `StudentViewModel.cs`, `RelayCommand.cs` | Menyimpan state (data form, data tabel, kata kunci), command, pencarian, statistik, dan validasi |
| Model | `Student.cs` | Merepresentasikan satu data mahasiswa |
| Repository | `StudentRepository.cs` | Satu-satunya kelas yang mengakses database (SELECT, INSERT, UPDATE, DELETE) |
| Service | `IDialogService.cs`, `DialogService.cs` | Menampilkan dialog (MessageBox) tanpa membuat ViewModel bergantung pada View |
| Converter | `RowNumberConverter.cs`, `EmptyStringToNullConverter.cs` | Mengubah nilai binding untuk kebutuhan tampilan |

Prinsip utamanya: **View tidak pernah mengakses database secara langsung**. `TextBlock` statistik tidak mengambil data dari SQL Server. ViewModel yang menghitung statistik, lalu XAML hanya menampilkannya melalui binding.

---

### 7. Struktur Project

```text
Tugas Pertemuan 5/
├── StudentManager.sln
├── StudentManager/                      # Project WPF (.NET 8)
│   ├── StudentManager.csproj
│   ├── App.xaml                         # Resource & Style Rich UI
│   ├── App.xaml.cs                      # Penanganan error global
│   ├── AssemblyInfo.cs
│   ├── MainWindow.xaml                  # View
│   ├── MainWindow.xaml.cs               # DataContext
│   ├── Models/
│   │   └── Student.cs
│   ├── ViewModels/
│   │   ├── StudentViewModel.cs
│   │   └── RelayCommand.cs
│   ├── Data/
│   │   └── StudentRepository.cs
│   ├── Services/
│   │   ├── IDialogService.cs
│   │   └── DialogService.cs
│   └── Converters/
│       ├── RowNumberConverter.cs
│       └── EmptyStringToNullConverter.cs
├── Database/
│   └── StudentDB.sql                    # Script database
├── Tests/
│   └── StudentManager.LogicTests/       # Program uji logika (74 skenario)
├── LAPORAN.md
├── LAPORAN_BLOGSPOT.html
└── README.md
```

Struktur ini mengikuti modul (`Models`, `ViewModels`, `Data`). Folder `Services` dan `Converters` ditambahkan sesuai catatan materi bahwa project dapat dikembangkan dengan folder lain seperti *Services* atau *Helpers*.

---

### 8. Langkah Pengerjaan

#### Langkah 1 — Menyiapkan Visual Studio dan SQL Server LocalDB

1. Install **Visual Studio 2022** (versi 17.8 atau lebih baru) dengan workload **.NET desktop development**.
2. Pastikan komponen **SQL Server Express LocalDB** ikut terpasang melalui *Visual Studio Installer → Modify → Individual components* (cari kata "LocalDB").
3. Periksa instance LocalDB melalui Command Prompt dengan perintah di bawah ini. Hasilnya harus menampilkan `MSSQLLocalDB`.

```bash
sqllocaldb info
```

#### Langkah 2 — Membuat Database `StudentDB`

1. Buka menu *View → SQL Server Object Explorer*.
2. Expand *SQL Server → (localdb)\MSSQLLocalDB*, klik kanan, lalu pilih *New Query...*.
3. Tempel dan jalankan script berikut (*Execute* / Ctrl+Shift+E).

📄 `Database/StudentDB.sql`

```sql
-- =====================================================================
--  Student Management System - Script Database
--  Server   : (localdb)\MSSQLLocalDB  (SQL Server LocalDB)
--  Database : StudentDB
--
--  Cara menjalankan (Visual Studio 2022):
--  View > SQL Server Object Explorer > (localdb)\MSSQLLocalDB
--  > klik kanan > New Query... > tempel script ini > Execute (Ctrl+Shift+E)
--
--  Catatan: aplikasi juga otomatis membuat database ini saat pertama kali
--  dijalankan (StudentRepository.EnsureDatabaseCreated), sehingga script ini
--  aman dijalankan ulang (memakai pengecekan IF ... IS NULL).
-- =====================================================================

IF DB_ID('StudentDB') IS NULL
    CREATE DATABASE StudentDB;
GO

USE StudentDB;
GO

IF OBJECT_ID('dbo.Students', 'U') IS NULL
BEGIN
    CREATE TABLE Students
    (
        Id INT IDENTITY(1,1) PRIMARY KEY,
        NIM VARCHAR(20) NOT NULL,
        Nama VARCHAR(100) NOT NULL,
        Jurusan VARCHAR(100) NOT NULL,
        Gender VARCHAR(20) NOT NULL,
        Email VARCHAR(100)
    );

    INSERT INTO Students
    (NIM, Nama, Jurusan, Gender, Email)
    VALUES
    ('23001','Budi Santoso','Informatika','Laki-laki','budi@gmail.com'),
    ('23002','Siti Aminah','Sistem Informasi','Perempuan','siti@gmail.com'),
    ('23003','Andi Wijaya','Informatika','Laki-laki','andi@gmail.com'),
    ('23004','Rina Sari','Sistem Informasi','Perempuan','rina@gmail.com');
END
GO

-- Cek isi tabel
SELECT * FROM Students ORDER BY Id DESC;
GO
```

Script membuat database `StudentDB`, tabel `Students` dengan kolom `Id` sebagai *primary key* `IDENTITY(1,1)`, serta empat data contoh. Pengecekan `IF DB_ID(...) IS NULL` dan `IF OBJECT_ID(...) IS NULL` membuat script aman dijalankan berulang kali. Aplikasi juga memanggil `EnsureDatabaseCreated()` saat pertama kali dijalankan, sehingga database tetap terbentuk walaupun langkah ini dilewati.

> 📷 **Gambar 1 — SQL Server Object Explorer.** Sisipkan screenshot: Database StudentDB dan isi tabel dbo.Students (4 data contoh) setelah script dijalankan.

#### Langkah 3 — Membuat Project WPF

1. Buka *Visual Studio → Create a new project*, lalu pilih template **WPF Application** (C#).
2. Isi *Project name* dengan **StudentManager**, pilih framework **.NET 8.0 (Long Term Support)**, lalu klik *Create*.

> 📷 **Gambar 2 — Create a new project.** Sisipkan screenshot: Template WPF Application dipilih pada jendela Create a new project.

> 📷 **Gambar 3 — Konfigurasi project.** Sisipkan screenshot: Project name StudentManager dengan framework .NET 8.0 (Long Term Support).

Isi file project setelah paket NuGet ditambahkan:

📄 `StudentManager/StudentManager.csproj`

```xml
<Project Sdk="Microsoft.NET.Sdk">

  <PropertyGroup>
    <OutputType>WinExe</OutputType>
    <TargetFramework>net8.0-windows</TargetFramework>
    <Nullable>enable</Nullable>
    <ImplicitUsings>enable</ImplicitUsings>
    <UseWPF>true</UseWPF>
  </PropertyGroup>

  <ItemGroup>
    <PackageReference Include="Microsoft.Data.SqlClient" Version="7.1.1" />
  </ItemGroup>

</Project>
```

`TargetFramework` `net8.0-windows` dan `UseWPF` menandakan bahwa project ini adalah aplikasi WPF untuk Windows.

#### Langkah 4 — Install NuGet Package `Microsoft.Data.SqlClient`

Klik kanan project, pilih *Manage NuGet Packages...*, buka tab *Browse*, cari `Microsoft.Data.SqlClient`, lalu klik *Install* (versi 7.1.1). Cara lain adalah melalui terminal:

```bash
dotnet add package Microsoft.Data.SqlClient --version 7.1.1
```

> 📷 **Gambar 4 — NuGet Package Manager.** Sisipkan screenshot: Paket Microsoft.Data.SqlClient 7.1.1 sudah terpasang pada project StudentManager.

#### Langkah 5 — Membuat Struktur Folder

Klik kanan project, pilih *Add → New Folder*, lalu buat folder `Models`, `ViewModels`, `Data`, `Services`, dan `Converters`. Setiap file kelas ditambahkan melalui *Add → Class...*.

> 📷 **Gambar 5 — Solution Explorer.** Sisipkan screenshot: Struktur project StudentManager beserta folder Models, ViewModels, Data, Services, dan Converters.

#### Langkah 6 — Model: `Student.cs`

Kelas `Student` merepresentasikan satu baris pada tabel `Students`. Setiap properti dipetakan ke satu kolom tabel.

📄 `StudentManager/Models/Student.cs`

```csharp
namespace StudentManager.Models;

/// <summary>
/// Model: representasi satu baris data pada tabel Students.
/// </summary>
public class Student
{
    public int Id { get; set; }
    public string NIM { get; set; } = "";
    public string Nama { get; set; } = "";
    public string Jurusan { get; set; } = "";
    public string Gender { get; set; } = "";
    public string Email { get; set; } = "";

    /// <summary>
    /// Membuat salinan objek. Form mengedit salinan ini sehingga data di
    /// DataGrid tidak ikut berubah sebelum tombol Simpan/Update ditekan.
    /// </summary>
    public Student Clone() => (Student)MemberwiseClone();
}
```

| Kolom Tabel `Students` | Tipe SQL | Properti `Student` | Tipe C# |
|---|---|---|---|
| Id | `INT IDENTITY(1,1) PRIMARY KEY` | `Id` | `int` |
| NIM | `VARCHAR(20) NOT NULL` | `NIM` | `string` |
| Nama | `VARCHAR(100) NOT NULL` | `Nama` | `string` |
| Jurusan | `VARCHAR(100) NOT NULL` | `Jurusan` | `string` |
| Gender | `VARCHAR(20) NOT NULL` | `Gender` | `string` |
| Email | `VARCHAR(100)` (boleh NULL) | `Email` | `string` |

Method `Clone()` ditambahkan agar form dapat mengedit **salinan** data. Data asli di DataGrid tidak ikut berubah sebelum tombol Update ditekan (dibahas pada [Pembahasan](#12-pembahasan)).

#### Langkah 7 — Repository: `StudentRepository.cs` (ADO.NET)

Repository adalah satu-satunya kelas yang berkomunikasi dengan SQL Server. Kelas ini menggunakan objek ADO.NET berikut:

| Objek ADO.NET | Fungsi |
|---|---|
| `SqlConnection` | Membuka koneksi ke SQL Server berdasarkan *connection string* |
| `SqlCommand` | Menjalankan perintah SQL beserta parameternya |
| `SqlDataReader` | Membaca hasil `SELECT` baris per baris |
| `ExecuteNonQuery()` | Menjalankan `INSERT`, `UPDATE`, `DELETE` |
| `ExecuteScalar()` | Mengambil satu nilai, misalnya hasil `COUNT` |

**a. Connection string**

📄 `StudentManager/Data/StudentRepository.cs (potongan)`

```csharp
private const string DefaultConnectionString =
    @"Server=(localdb)\MSSQLLocalDB;" +
    "Database=StudentDB;" +
    "Trusted_Connection=True;" +
    "TrustServerCertificate=True;";
```

| Bagian | Arti |
|---|---|
| `Server=(localdb)\MSSQLLocalDB` | Nama server: SQL Server LocalDB, versi SQL Server ringan untuk development |
| `Database=StudentDB` | Database yang digunakan aplikasi |
| `Trusted_Connection=True` | Login memakai akun Windows (tanpa username dan password) |
| `TrustServerCertificate=True` | Mempercayai sertifikat server lokal, karena SqlClient versi baru mengaktifkan enkripsi secara default |

**b. READ dan SEARCH**

📄 `StudentManager/Data/StudentRepository.cs (potongan)`

```csharp
public List<Student> GetAll()
{
    using SqlConnection connection =
        new SqlConnection(connectionString);

    string sql =
        "SELECT Id, NIM, Nama, Jurusan, Gender, Email " +
        "FROM Students ORDER BY Id DESC";

    using SqlCommand command =
        new SqlCommand(sql, connection);

    connection.Open();

    return ReadStudents(command);
}
```

📄 `StudentManager/Data/StudentRepository.cs (potongan)`

```csharp
public List<Student> Search(string keyword)
{
    using SqlConnection connection =
        new SqlConnection(connectionString);

    string sql =
        "SELECT Id, NIM, Nama, Jurusan, Gender, Email " +
        "FROM Students WHERE NIM LIKE @Keyword " +
        "OR Nama LIKE @Keyword OR Jurusan LIKE @Keyword " +
        "ORDER BY Id DESC";

    using SqlCommand command =
        new SqlCommand(sql, connection);

    command.Parameters.AddWithValue("@Keyword", "%" + keyword + "%");

    connection.Open();

    return ReadStudents(command);
}
```

`GetAll()` mengambil semua data dengan urutan `Id` terbaru di atas. `Search()` memakai `LIKE` dengan pola `%keyword%` pada kolom NIM, Nama, dan Jurusan. Kedua method memakai helper `ReadStudents()` yang mengubah setiap baris `SqlDataReader` menjadi objek `Student`:

📄 `StudentManager/Data/StudentRepository.cs (potongan)`

```csharp
private static List<Student> ReadStudents(SqlCommand command)
{
    var students = new List<Student>();

    using SqlDataReader reader = command.ExecuteReader();

    while (reader.Read())
    {
        students.Add(new Student
        {
            Id = Convert.ToInt32(reader["Id"]),
            NIM = reader["NIM"].ToString()!,
            Nama = reader["Nama"].ToString()!,
            Jurusan = reader["Jurusan"].ToString()!,
            Gender = reader["Gender"].ToString()!,
            Email = reader["Email"].ToString()!   // NULL di database → ""
        });
    }

    return students;
}
```

**c. INSERT, UPDATE, DELETE**

📄 `StudentManager/Data/StudentRepository.cs (potongan)`

```csharp
public void Insert(Student student)
{
    using SqlConnection connection =
        new SqlConnection(connectionString);

    string sql =
        "INSERT INTO Students " +
        "(NIM, Nama, Jurusan, Gender, Email) " +
        "VALUES (@NIM,@Nama,@Jurusan,@Gender,@Email)";

    using SqlCommand command =
        new SqlCommand(sql, connection);

    AddStudentParameters(command, student);

    connection.Open();
    command.ExecuteNonQuery();
}
```

📄 `StudentManager/Data/StudentRepository.cs (potongan)`

```csharp
public void Update(Student student)
{
    using SqlConnection connection =
        new SqlConnection(connectionString);

    string sql =
        "UPDATE Students SET NIM=@NIM, Nama=@Nama, " +
        "Jurusan=@Jurusan, Gender=@Gender, Email=@Email " +
        "WHERE Id=@Id";

    using SqlCommand command =
        new SqlCommand(sql, connection);

    command.Parameters.AddWithValue("@Id", student.Id);
    AddStudentParameters(command, student);

    connection.Open();
    command.ExecuteNonQuery();
}
```

📄 `StudentManager/Data/StudentRepository.cs (potongan)`

```csharp
public void Delete(int id)
{
    using SqlConnection connection =
        new SqlConnection(connectionString);

    using SqlCommand command =
        new SqlCommand("DELETE FROM Students WHERE Id=@Id", connection);

    command.Parameters.AddWithValue("@Id", id);

    connection.Open();
    command.ExecuteNonQuery();
}
```

📄 `StudentManager/Data/StudentRepository.cs (potongan)`

```csharp
private static void AddStudentParameters(SqlCommand command, Student student)
{
    command.Parameters.AddWithValue("@NIM", student.NIM);
    command.Parameters.AddWithValue("@Nama", student.Nama);
    command.Parameters.AddWithValue("@Jurusan", student.Jurusan);
    command.Parameters.AddWithValue("@Gender", student.Gender);

    // Email bersifat opsional: string kosong disimpan sebagai NULL.
    command.Parameters.AddWithValue("@Email",
        string.IsNullOrWhiteSpace(student.Email) ? DBNull.Value : (object)student.Email);
}
```

Semua query memakai **parameter** (`@NIM`, `@Nama`, dan seterusnya), bukan penggabungan string input pengguna. Dengan cara ini input diperlakukan sebagai data, bukan perintah SQL, sehingga aplikasi aman dari **SQL Injection**. Kata kunci `using` memastikan koneksi otomatis ditutup setelah method selesai.

**d. Validasi NIM dan inisialisasi database**

📄 `StudentManager/Data/StudentRepository.cs (potongan)`

```csharp
public bool IsNimExists(string nim, int excludeId = 0)
{
    using SqlConnection connection =
        new SqlConnection(connectionString);

    string sql =
        "SELECT COUNT(1) FROM Students " +
        "WHERE NIM = @NIM AND Id <> @Id";

    using SqlCommand command =
        new SqlCommand(sql, connection);

    command.Parameters.AddWithValue("@NIM", nim);
    command.Parameters.AddWithValue("@Id", excludeId);

    connection.Open();

    return Convert.ToInt32(command.ExecuteScalar()) > 0;
}
```

`IsNimExists()` mencegah NIM ganda. Parameter `excludeId` berisi `Id` data yang sedang di-*update*, agar data tersebut tidak dianggap duplikat dengan dirinya sendiri. Method `EnsureDatabaseCreated()` terhubung ke database `master` untuk membuat `StudentDB` bila belum ada, lalu membuat tabel `Students` beserta 4 data contoh.

<details>
<summary><b>Kode lengkap StudentRepository.cs</b> (klik untuk membuka)</summary>

`StudentManager/Data/StudentRepository.cs`

```csharp
using Microsoft.Data.SqlClient;
using StudentManager.Models;

namespace StudentManager.Data;

/// <summary>
/// Repository: satu-satunya kelas yang berkomunikasi dengan SQL Server
/// menggunakan ADO.NET (Microsoft.Data.SqlClient).
/// </summary>
public class StudentRepository
{
    private const string DefaultConnectionString =
        @"Server=(localdb)\MSSQLLocalDB;" +
        "Database=StudentDB;" +
        "Trusted_Connection=True;" +
        "TrustServerCertificate=True;";

    private readonly string connectionString;

    public StudentRepository() : this(DefaultConnectionString)
    {
    }

    public StudentRepository(string connectionString)
    {
        this.connectionString = connectionString;
    }

    // ===================== READ =====================
    public List<Student> GetAll()
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        string sql =
            "SELECT Id, NIM, Nama, Jurusan, Gender, Email " +
            "FROM Students ORDER BY Id DESC";

        using SqlCommand command =
            new SqlCommand(sql, connection);

        connection.Open();

        return ReadStudents(command);
    }

    // ===================== SEARCH =====================
    public List<Student> Search(string keyword)
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        string sql =
            "SELECT Id, NIM, Nama, Jurusan, Gender, Email " +
            "FROM Students WHERE NIM LIKE @Keyword " +
            "OR Nama LIKE @Keyword OR Jurusan LIKE @Keyword " +
            "ORDER BY Id DESC";

        using SqlCommand command =
            new SqlCommand(sql, connection);

        command.Parameters.AddWithValue("@Keyword", "%" + keyword + "%");

        connection.Open();

        return ReadStudents(command);
    }

    // ===================== CREATE =====================
    public void Insert(Student student)
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        string sql =
            "INSERT INTO Students " +
            "(NIM, Nama, Jurusan, Gender, Email) " +
            "VALUES (@NIM,@Nama,@Jurusan,@Gender,@Email)";

        using SqlCommand command =
            new SqlCommand(sql, connection);

        AddStudentParameters(command, student);

        connection.Open();
        command.ExecuteNonQuery();
    }

    // ===================== UPDATE =====================
    public void Update(Student student)
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        string sql =
            "UPDATE Students SET NIM=@NIM, Nama=@Nama, " +
            "Jurusan=@Jurusan, Gender=@Gender, Email=@Email " +
            "WHERE Id=@Id";

        using SqlCommand command =
            new SqlCommand(sql, connection);

        command.Parameters.AddWithValue("@Id", student.Id);
        AddStudentParameters(command, student);

        connection.Open();
        command.ExecuteNonQuery();
    }

    // ===================== DELETE =====================
    public void Delete(int id)
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        using SqlCommand command =
            new SqlCommand("DELETE FROM Students WHERE Id=@Id", connection);

        command.Parameters.AddWithValue("@Id", id);

        connection.Open();
        command.ExecuteNonQuery();
    }

    // ===================== VALIDASI NIM =====================
    /// <summary>
    /// Mengecek apakah NIM sudah dipakai mahasiswa lain.
    /// excludeId diisi Id data yang sedang di-update agar tidak dianggap duplikat.
    /// </summary>
    public bool IsNimExists(string nim, int excludeId = 0)
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        string sql =
            "SELECT COUNT(1) FROM Students " +
            "WHERE NIM = @NIM AND Id <> @Id";

        using SqlCommand command =
            new SqlCommand(sql, connection);

        command.Parameters.AddWithValue("@NIM", nim);
        command.Parameters.AddWithValue("@Id", excludeId);

        connection.Open();

        return Convert.ToInt32(command.ExecuteScalar()) > 0;
    }

    // ===================== INISIALISASI DATABASE =====================
    /// <summary>
    /// Membuat database StudentDB, tabel Students, dan data contoh jika belum ada,
    /// sehingga aplikasi tetap bisa dijalankan di komputer lain tanpa menjalankan
    /// script SQL secara manual. Jika database sudah ada, method ini tidak mengubah apa pun.
    /// </summary>
    public void EnsureDatabaseCreated()
    {
        var builder = new SqlConnectionStringBuilder(connectionString);
        string databaseName = builder.InitialCatalog;

        // 1) Buat database melalui database "master".
        builder.InitialCatalog = "master";

        using (SqlConnection master = new SqlConnection(builder.ConnectionString))
        {
            string createDatabase =
                "IF DB_ID(@Name) IS NULL " +
                "BEGIN " +
                "    DECLARE @sql NVARCHAR(300) = N'CREATE DATABASE ' + QUOTENAME(@Name); " +
                "    EXEC (@sql); " +
                "END";

            using SqlCommand command = new SqlCommand(createDatabase, master);
            command.Parameters.AddWithValue("@Name", databaseName);

            master.Open();
            command.ExecuteNonQuery();
        }

        // 2) Buat tabel Students beserta data contoh.
        using SqlConnection connection = new SqlConnection(connectionString);

        string createTable = """
            IF OBJECT_ID(N'dbo.Students', N'U') IS NULL
            BEGIN
                CREATE TABLE dbo.Students
                (
                    Id INT IDENTITY(1,1) PRIMARY KEY,
                    NIM VARCHAR(20) NOT NULL,
                    Nama VARCHAR(100) NOT NULL,
                    Jurusan VARCHAR(100) NOT NULL,
                    Gender VARCHAR(20) NOT NULL,
                    Email VARCHAR(100)
                );

                INSERT INTO dbo.Students
                (NIM, Nama, Jurusan, Gender, Email)
                VALUES
                ('23001','Budi Santoso','Informatika','Laki-laki','budi@gmail.com'),
                ('23002','Siti Aminah','Sistem Informasi','Perempuan','siti@gmail.com'),
                ('23003','Andi Wijaya','Informatika','Laki-laki','andi@gmail.com'),
                ('23004','Rina Sari','Sistem Informasi','Perempuan','rina@gmail.com');
            END
            """;

        using SqlCommand tableCommand = new SqlCommand(createTable, connection);

        connection.Open();
        tableCommand.ExecuteNonQuery();
    }

    // ===================== HELPER =====================
    private static List<Student> ReadStudents(SqlCommand command)
    {
        var students = new List<Student>();

        using SqlDataReader reader = command.ExecuteReader();

        while (reader.Read())
        {
            students.Add(new Student
            {
                Id = Convert.ToInt32(reader["Id"]),
                NIM = reader["NIM"].ToString()!,
                Nama = reader["Nama"].ToString()!,
                Jurusan = reader["Jurusan"].ToString()!,
                Gender = reader["Gender"].ToString()!,
                Email = reader["Email"].ToString()!   // NULL di database → ""
            });
        }

        return students;
    }

    private static void AddStudentParameters(SqlCommand command, Student student)
    {
        command.Parameters.AddWithValue("@NIM", student.NIM);
        command.Parameters.AddWithValue("@Nama", student.Nama);
        command.Parameters.AddWithValue("@Jurusan", student.Jurusan);
        command.Parameters.AddWithValue("@Gender", student.Gender);

        // Email bersifat opsional: string kosong disimpan sebagai NULL.
        command.Parameters.AddWithValue("@Email",
            string.IsNullOrWhiteSpace(student.Email) ? DBNull.Value : (object)student.Email);
    }
}
```

</details>

#### Langkah 8 — `RelayCommand.cs`

`RelayCommand` adalah implementasi `ICommand` yang meneruskan (*relay*) eksekusi tombol ke method di ViewModel.

📄 `StudentManager/ViewModels/RelayCommand.cs`

```csharp
using System.Windows.Input;

namespace StudentManager.ViewModels;

/// <summary>
/// Implementasi ICommand: menghubungkan Button di View dengan method di ViewModel
/// tanpa event Click di code-behind.
/// </summary>
public class RelayCommand : ICommand
{
    private readonly Action<object?> _execute;
    private readonly Predicate<object?>? _canExecute;

    // Command tanpa parameter, contoh: new RelayCommand(Save)
    public RelayCommand(Action execute)
        : this(_ => execute())
    {
    }

    // Command dengan parameter (CommandParameter), contoh: new RelayCommand(Edit)
    public RelayCommand(Action<object?> execute, Predicate<object?>? canExecute = null)
    {
        _execute = execute ?? throw new ArgumentNullException(nameof(execute));
        _canExecute = canExecute;
    }

    public bool CanExecute(object? parameter) => _canExecute?.Invoke(parameter) ?? true;

    public void Execute(object? parameter) => _execute(parameter);

    public event EventHandler? CanExecuteChanged;

    public void RaiseCanExecuteChanged() => CanExecuteChanged?.Invoke(this, EventArgs.Empty);
}
```

| Anggota `ICommand` | Fungsi |
|---|---|
| `Execute(parameter)` | Dijalankan saat tombol diklik dan memanggil method ViewModel |
| `CanExecute(parameter)` | Menentukan apakah command boleh dijalankan (default `true`) |
| `CanExecuteChanged` | Event untuk memberi tahu WPF agar memeriksa ulang `CanExecute` |

Konstruktor `RelayCommand(Action)` dipakai untuk command tanpa parameter seperti `SaveCommand`. Konstruktor `RelayCommand(Action<object?>)` dipakai untuk command yang menerima `CommandParameter`, misalnya tombol Edit dan Hapus pada setiap baris DataGrid yang mengirim objek `Student` baris tersebut.

#### Langkah 9 — Dialog Service

Agar ViewModel tidak memanggil `MessageBox` (komponen milik View) secara langsung, dialog dibungkus dalam interface `IDialogService`. ViewModel hanya mengenal interface ini, sedangkan implementasinya memakai `MessageBox` WPF.

📄 `StudentManager/Services/IDialogService.cs`

```csharp
namespace StudentManager.Services;

/// <summary>
/// Abstraksi dialog. ViewModel cukup memanggil interface ini sehingga
/// tidak bergantung langsung pada MessageBox (komponen milik View).
/// </summary>
public interface IDialogService
{
    void ShowWarning(string message, string title);

    void ShowError(string message, string title);

    bool Confirm(string message, string title);
}
```

📄 `StudentManager/Services/DialogService.cs`

```csharp
using System.Windows;

namespace StudentManager.Services;

/// <summary>
/// Implementasi IDialogService menggunakan MessageBox WPF.
/// </summary>
public class DialogService : IDialogService
{
    public void ShowWarning(string message, string title) =>
        Show(message, title, MessageBoxButton.OK, MessageBoxImage.Warning);

    public void ShowError(string message, string title) =>
        Show(message, title, MessageBoxButton.OK, MessageBoxImage.Error);

    public bool Confirm(string message, string title) =>
        Show(message, title, MessageBoxButton.YesNo, MessageBoxImage.Question) == MessageBoxResult.Yes;

    private static MessageBoxResult Show(string message, string title,
        MessageBoxButton button, MessageBoxImage icon)
    {
        // Jika jendela utama sudah tampil, dialog dibuka di tengah jendela tersebut.
        Window? owner = Application.Current?.MainWindow;

        return owner is { IsVisible: true }
            ? MessageBox.Show(owner, message, title, button, icon)
            : MessageBox.Show(message, title, button, icon);
    }
}
```

#### Langkah 10 — ViewModel: `StudentViewModel.cs`

`StudentViewModel` adalah pusat logika aplikasi. Kelas ini mengimplementasikan `INotifyPropertyChanged` agar setiap perubahan properti otomatis diteruskan ke UI.

**a. Koleksi data dan pilihan ComboBox**

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
public ObservableCollection<Student> Students { get; } = new();

// ===================== PILIHAN COMBOBOX =====================
public IReadOnlyList<string> JurusanOptions { get; } =
    new[] { "Informatika", "Sistem Informasi" };

public IReadOnlyList<string> GenderOptions { get; } =
    new[] { "Laki-laki", "Perempuan" };
```

`ObservableCollection<Student>` memberi tahu DataGrid setiap kali ada data yang ditambah, dihapus, atau dikosongkan, sehingga tabel ter-*update* otomatis tanpa *refresh* manual. Pilihan Jurusan dan Gender disediakan sebagai `string` dari ViewModel.

**b. Baris terpilih dan data form**

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
private Student? _selectedStudent;
public Student? SelectedStudent
{
    get => _selectedStudent;
    set
    {
        _selectedStudent = value;
        OnPropertyChanged();

        // Selection: DataGrid.SelectedItem → SelectedStudent → Form
        if (value != null)
        {
            CurrentStudent = value.Clone();
            StatusMessage = $"Data {value.Nama} dipilih. Ubah data lalu klik Update, atau klik Hapus.";
        }
    }
}

// ===================== DATA PADA FORM INPUT =====================
private Student _currentStudent = new();
public Student CurrentStudent
{
    get => _currentStudent;
    set
    {
        _currentStudent = value;
        OnPropertyChanged();
        OnPropertyChanged(nameof(IsEditMode));
        OnPropertyChanged(nameof(FormModeText));
        OnPropertyChanged(nameof(SaveButtonText));
    }
}

public bool IsEditMode => CurrentStudent.Id != 0;

public string FormModeText => IsEditMode ? "Mode Edit" : "Mode Tambah";

public string SaveButtonText => IsEditMode ? "Update" : "Simpan";
```

- `SelectedStudent` terhubung ke `DataGrid.SelectedItem`. Saat pengguna memilih baris, **salinan** datanya dimasukkan ke `CurrentStudent`.
- `CurrentStudent` terhubung ke form input. Jika `Id = 0` berarti data baru (**Mode Tambah**), jika `Id ≠ 0` berarti data lama (**Mode Edit**).
- Properti turunan `IsEditMode`, `FormModeText`, dan `SaveButtonText` ikut diberi notifikasi saat `CurrentStudent` berubah, sehingga badge mode dan teks tombol (Simpan/Update) berganti otomatis.

**c. Statistik untuk panel INFORMASI**

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
public int TotalStudents => Students.Count;
public int TotalInformatika =>
    Students.Count(x => x.Jurusan == "Informatika");
public int TotalSistemInformasi =>
    Students.Count(x => x.Jurusan == "Sistem Informasi");
public int TotalLakiLaki =>
    Students.Count(x => x.Gender == "Laki-laki");
public int TotalPerempuan =>
    Students.Count(x => x.Gender == "Perempuan");
```

Statistik dihitung dengan LINQ `Count()` dari koleksi `Students`, sama seperti pada materi Panel Informasi. Karena properti ini hanya memiliki *getter*, ViewModel harus memanggil `RefreshStatistics()` setiap kali isi `Students` berubah:

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
private void FillStudents(IEnumerable<Student> students)
{
    Students.Clear();

    foreach (var student in students)
        Students.Add(student);

    RefreshStatistics();
}

private void RefreshStatistics()
{
    OnPropertyChanged(nameof(TotalStudents));
    OnPropertyChanged(nameof(TotalInformatika));
    OnPropertyChanged(nameof(TotalSistemInformasi));
    OnPropertyChanged(nameof(TotalLakiLaki));
    OnPropertyChanged(nameof(TotalPerempuan));
}
```

**d. Command dan konstruktor**

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
public ICommand SaveCommand { get; }
public ICommand DeleteCommand { get; }
public ICommand ResetCommand { get; }
public ICommand SearchCommand { get; }
public ICommand ClearSearchCommand { get; }
public ICommand EditCommand { get; }

public StudentViewModel()
    : this(new StudentRepository(), new DialogService())
{
}

public StudentViewModel(StudentRepository repository, IDialogService dialogService)
{
    _repository = repository;
    _dialogService = dialogService;

    SaveCommand = new RelayCommand(Save);
    DeleteCommand = new RelayCommand(Delete);
    ResetCommand = new RelayCommand(Reset);
    SearchCommand = new RelayCommand(Search);
    ClearSearchCommand = new RelayCommand(ClearSearch);
    EditCommand = new RelayCommand(Edit);

    InitializeData();
}
```

| Command | Method | Dipicu oleh |
|---|---|---|
| `SaveCommand` | `Save()` | Tombol Simpan/Update |
| `DeleteCommand` | `Delete(object?)` | Tombol Hapus di form dan ikon Hapus di setiap baris |
| `ResetCommand` | `Reset()` | Tombol Reset di form |
| `SearchCommand` | `Search()` | Tombol Cari dan tombol Enter di kolom pencarian |
| `ClearSearchCommand` | `ClearSearch()` | Tombol Reset di bar pencarian |
| `EditCommand` | `Edit(object?)` | Ikon Edit di setiap baris |

Konstruktor tanpa parameter dipakai oleh `MainWindow`. Konstruktor kedua menerima repository dan dialog service dari luar (*dependency injection* sederhana), sehingga ViewModel bisa diuji dengan objek palsu.

**e. CREATE / UPDATE — method `Save()`**

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
private void Save()
{
    Student student = ReadForm();

    List<string> errors = Validate(student);
    if (errors.Count > 0)
    {
        ShowValidationErrors(errors);
        return;
    }

    try
    {
        if (_repository.IsNimExists(student.NIM, student.Id))
        {
            ShowValidationErrors(new List<string>
            {
                $"NIM {student.NIM} sudah terdaftar. Gunakan NIM lain."
            });
            return;
        }

        bool isNew = student.Id == 0;

        if (isNew)
            _repository.Insert(student);
        else
            _repository.Update(student);

        SearchText = "";
        LoadData();
        Reset();

        StatusMessage = isNew
            ? $"Data {student.Nama} berhasil disimpan."
            : $"Data {student.Nama} berhasil diperbarui.";
    }
    catch (Exception ex)
    {
        ShowDatabaseError("menyimpan", ex);
    }
}
```

Alurnya: data form dibaca dan dirapikan (`Trim`), lalu divalidasi. NIM dicek agar tidak duplikat. Jika `Id = 0`, data di-*insert*; jika tidak, data di-*update*. Setelah itu data dimuat ulang, form dikosongkan, dan pesan sukses ditampilkan di status bar.

**f. DELETE — method `Delete()`**

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
private void Delete(object? parameter)
{
    // Tombol hapus pada kolom Aksi mengirim Student sebagai CommandParameter,
    // sedangkan tombol Hapus di form memakai data yang sedang diedit.
    Student? target = parameter as Student ?? (IsEditMode ? CurrentStudent : null);

    if (target == null)
    {
        _dialogService.ShowWarning(
            "Pilih data mahasiswa yang akan dihapus terlebih dahulu.",
            "Hapus Data");
        return;
    }

    bool confirmed = _dialogService.Confirm(
        "Yakin ingin menghapus data mahasiswa berikut?\n\n" +
        $"NIM   : {target.NIM}\n" +
        $"Nama : {target.Nama}",
        "Konfirmasi Hapus");

    if (!confirmed)
    {
        StatusMessage = "Penghapusan data dibatalkan.";
        return;
    }

    try
    {
        _repository.Delete(target.Id);

        SearchText = "";
        LoadData();
        Reset();

        StatusMessage = $"Data {target.Nama} berhasil dihapus.";
    }
    catch (Exception ex)
    {
        ShowDatabaseError("menghapus", ex);
    }
}
```

**g. SEARCH dan RESET**

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
private void Search()
{
    string keyword = SearchText.Trim();

    try
    {
        var result = string.IsNullOrEmpty(keyword)
            ? _repository.GetAll()
            : _repository.Search(keyword);

        FillStudents(result);

        if (string.IsNullOrEmpty(keyword))
        {
            InfoCaption = AllDataCaption;
            StatusMessage = $"Menampilkan seluruh data ({Students.Count} mahasiswa).";
        }
        else
        {
            InfoCaption = $"Hasil pencarian \"{keyword}\"";
            StatusMessage = $"Ditemukan {Students.Count} data untuk kata kunci \"{keyword}\".";
        }
    }
    catch (Exception ex)
    {
        ShowDatabaseError("mencari", ex);
    }
}
```

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
private void Reset()
{
    SelectedStudent = null;
    CurrentStudent = new Student();
    StatusMessage = "Form dikosongkan. Silakan input data mahasiswa baru.";
}
```

**h. Validasi input**

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
private static List<string> Validate(Student student)
{
    var errors = new List<string>();

    if (string.IsNullOrWhiteSpace(student.NIM))
        errors.Add("NIM wajib diisi.");
    else if (!student.NIM.All(char.IsAsciiDigit))
        errors.Add("NIM hanya boleh berisi angka.");

    if (string.IsNullOrWhiteSpace(student.Nama))
        errors.Add("Nama wajib diisi.");

    if (string.IsNullOrWhiteSpace(student.Jurusan))
        errors.Add("Jurusan wajib dipilih.");

    if (string.IsNullOrWhiteSpace(student.Gender))
        errors.Add("Gender wajib dipilih.");

    if (student.Email.Length > 0 && !EmailPattern.IsMatch(student.Email))
        errors.Add("Format email tidak valid (contoh: nama@gmail.com).");

    return errors;
}
```

Semua kesalahan dikumpulkan lalu ditampilkan sekaligus dalam satu dialog, sehingga pengguna dapat memperbaiki semua input dalam satu kali langkah.

**i. INotifyPropertyChanged**

📄 `StudentManager/ViewModels/StudentViewModel.cs (potongan)`

```csharp
public event PropertyChangedEventHandler? PropertyChanged;

protected void OnPropertyChanged(
    [CallerMemberName] string? propertyName = null)
{
    PropertyChanged?.Invoke(
        this,
        new PropertyChangedEventArgs(propertyName));
}
```

Atribut `[CallerMemberName]` otomatis mengisi nama properti yang memanggil `OnPropertyChanged()`. Dengan begitu, setter cukup menulis `OnPropertyChanged();` tanpa mengetik nama properti.

<details>
<summary><b>Kode lengkap StudentViewModel.cs</b> (klik untuk membuka)</summary>

`StudentManager/ViewModels/StudentViewModel.cs`

```csharp
using System.Collections.ObjectModel;
using System.ComponentModel;
using System.Globalization;
using System.Runtime.CompilerServices;
using System.Text.RegularExpressions;
using System.Windows.Input;
using StudentManager.Data;
using StudentManager.Models;
using StudentManager.Services;

namespace StudentManager.ViewModels;

/// <summary>
/// ViewModel: menyimpan state UI, command, pencarian, statistik,
/// dan logika aplikasi. View (XAML) hanya melakukan binding ke kelas ini.
/// </summary>
public class StudentViewModel : INotifyPropertyChanged
{
    private const string AllDataCaption = "Seluruh data mahasiswa";

    private static readonly Regex EmailPattern =
        new(@"^[^@\s]+@[^@\s]+\.[^@\s]+$");

    private readonly StudentRepository _repository;
    private readonly IDialogService _dialogService;

    // ===================== DATA UNTUK DATAGRID =====================
    public ObservableCollection<Student> Students { get; } = new();

    // ===================== PILIHAN COMBOBOX =====================
    public IReadOnlyList<string> JurusanOptions { get; } =
        new[] { "Informatika", "Sistem Informasi" };

    public IReadOnlyList<string> GenderOptions { get; } =
        new[] { "Laki-laki", "Perempuan" };

    // ===================== HEADER =====================
    public string TodayText { get; } =
        DateTime.Now.ToString("dddd, dd MMMM yyyy", new CultureInfo("id-ID"));

    // ===================== BARIS TERPILIH DI DATAGRID =====================
    private Student? _selectedStudent;
    public Student? SelectedStudent
    {
        get => _selectedStudent;
        set
        {
            _selectedStudent = value;
            OnPropertyChanged();

            // Selection: DataGrid.SelectedItem → SelectedStudent → Form
            if (value != null)
            {
                CurrentStudent = value.Clone();
                StatusMessage = $"Data {value.Nama} dipilih. Ubah data lalu klik Update, atau klik Hapus.";
            }
        }
    }

    // ===================== DATA PADA FORM INPUT =====================
    private Student _currentStudent = new();
    public Student CurrentStudent
    {
        get => _currentStudent;
        set
        {
            _currentStudent = value;
            OnPropertyChanged();
            OnPropertyChanged(nameof(IsEditMode));
            OnPropertyChanged(nameof(FormModeText));
            OnPropertyChanged(nameof(SaveButtonText));
        }
    }

    public bool IsEditMode => CurrentStudent.Id != 0;

    public string FormModeText => IsEditMode ? "Mode Edit" : "Mode Tambah";

    public string SaveButtonText => IsEditMode ? "Update" : "Simpan";

    // ===================== PENCARIAN =====================
    private string _searchText = "";
    public string SearchText
    {
        get => _searchText;
        set
        {
            _searchText = value;
            OnPropertyChanged();
        }
    }

    // ===================== PESAN STATUS =====================
    private string _statusMessage = "";
    public string StatusMessage
    {
        get => _statusMessage;
        set
        {
            _statusMessage = value;
            OnPropertyChanged();
        }
    }

    private string _infoCaption = AllDataCaption;
    public string InfoCaption
    {
        get => _infoCaption;
        set
        {
            _infoCaption = value;
            OnPropertyChanged();
        }
    }

    // ===================== STATISTIK (PANEL INFORMASI) =====================
    public int TotalStudents => Students.Count;
    public int TotalInformatika =>
        Students.Count(x => x.Jurusan == "Informatika");
    public int TotalSistemInformasi =>
        Students.Count(x => x.Jurusan == "Sistem Informasi");
    public int TotalLakiLaki =>
        Students.Count(x => x.Gender == "Laki-laki");
    public int TotalPerempuan =>
        Students.Count(x => x.Gender == "Perempuan");

    // ===================== COMMAND =====================
    public ICommand SaveCommand { get; }
    public ICommand DeleteCommand { get; }
    public ICommand ResetCommand { get; }
    public ICommand SearchCommand { get; }
    public ICommand ClearSearchCommand { get; }
    public ICommand EditCommand { get; }

    public StudentViewModel()
        : this(new StudentRepository(), new DialogService())
    {
    }

    public StudentViewModel(StudentRepository repository, IDialogService dialogService)
    {
        _repository = repository;
        _dialogService = dialogService;

        SaveCommand = new RelayCommand(Save);
        DeleteCommand = new RelayCommand(Delete);
        ResetCommand = new RelayCommand(Reset);
        SearchCommand = new RelayCommand(Search);
        ClearSearchCommand = new RelayCommand(ClearSearch);
        EditCommand = new RelayCommand(Edit);

        InitializeData();
    }

    // ===================== INISIALISASI =====================
    private void InitializeData()
    {
        try
        {
            _repository.EnsureDatabaseCreated();
            LoadData();
            StatusMessage = $"Terhubung ke database StudentDB. {Students.Count} data mahasiswa dimuat.";
        }
        catch (Exception ex)
        {
            StatusMessage = "Gagal terhubung ke database. Periksa SQL Server LocalDB.";
            _dialogService.ShowError(
                "Aplikasi tidak dapat terhubung ke SQL Server LocalDB.\n\n" +
                "Pastikan komponen \"SQL Server Express LocalDB\" sudah terpasang " +
                "(Visual Studio Installer → Modify → Individual components), " +
                "lalu jalankan ulang aplikasi.\n\n" +
                "Detail: " + ex.Message,
                "Koneksi Database Gagal");
        }
    }

    // ===================== READ =====================
    private void LoadData()
    {
        FillStudents(_repository.GetAll());
        InfoCaption = AllDataCaption;
    }

    // ===================== CREATE / UPDATE =====================
    private void Save()
    {
        Student student = ReadForm();

        List<string> errors = Validate(student);
        if (errors.Count > 0)
        {
            ShowValidationErrors(errors);
            return;
        }

        try
        {
            if (_repository.IsNimExists(student.NIM, student.Id))
            {
                ShowValidationErrors(new List<string>
                {
                    $"NIM {student.NIM} sudah terdaftar. Gunakan NIM lain."
                });
                return;
            }

            bool isNew = student.Id == 0;

            if (isNew)
                _repository.Insert(student);
            else
                _repository.Update(student);

            SearchText = "";
            LoadData();
            Reset();

            StatusMessage = isNew
                ? $"Data {student.Nama} berhasil disimpan."
                : $"Data {student.Nama} berhasil diperbarui.";
        }
        catch (Exception ex)
        {
            ShowDatabaseError("menyimpan", ex);
        }
    }

    // ===================== DELETE =====================
    private void Delete(object? parameter)
    {
        // Tombol hapus pada kolom Aksi mengirim Student sebagai CommandParameter,
        // sedangkan tombol Hapus di form memakai data yang sedang diedit.
        Student? target = parameter as Student ?? (IsEditMode ? CurrentStudent : null);

        if (target == null)
        {
            _dialogService.ShowWarning(
                "Pilih data mahasiswa yang akan dihapus terlebih dahulu.",
                "Hapus Data");
            return;
        }

        bool confirmed = _dialogService.Confirm(
            "Yakin ingin menghapus data mahasiswa berikut?\n\n" +
            $"NIM   : {target.NIM}\n" +
            $"Nama : {target.Nama}",
            "Konfirmasi Hapus");

        if (!confirmed)
        {
            StatusMessage = "Penghapusan data dibatalkan.";
            return;
        }

        try
        {
            _repository.Delete(target.Id);

            SearchText = "";
            LoadData();
            Reset();

            StatusMessage = $"Data {target.Nama} berhasil dihapus.";
        }
        catch (Exception ex)
        {
            ShowDatabaseError("menghapus", ex);
        }
    }

    // ===================== EDIT (TOMBOL AKSI) =====================
    private void Edit(object? parameter)
    {
        if (parameter is not Student student) return;

        SelectedStudent = student;
        CurrentStudent = student.Clone();
        StatusMessage = $"Mode edit: {student.Nama}. Ubah data lalu klik Update.";
    }

    // ===================== SEARCH =====================
    private void Search()
    {
        string keyword = SearchText.Trim();

        try
        {
            var result = string.IsNullOrEmpty(keyword)
                ? _repository.GetAll()
                : _repository.Search(keyword);

            FillStudents(result);

            if (string.IsNullOrEmpty(keyword))
            {
                InfoCaption = AllDataCaption;
                StatusMessage = $"Menampilkan seluruh data ({Students.Count} mahasiswa).";
            }
            else
            {
                InfoCaption = $"Hasil pencarian \"{keyword}\"";
                StatusMessage = $"Ditemukan {Students.Count} data untuk kata kunci \"{keyword}\".";
            }
        }
        catch (Exception ex)
        {
            ShowDatabaseError("mencari", ex);
        }
    }

    private void ClearSearch()
    {
        SearchText = "";

        try
        {
            LoadData();
            StatusMessage = $"Pencarian direset. Menampilkan seluruh data ({Students.Count} mahasiswa).";
        }
        catch (Exception ex)
        {
            ShowDatabaseError("memuat", ex);
        }
    }

    // ===================== RESET FORM =====================
    private void Reset()
    {
        SelectedStudent = null;
        CurrentStudent = new Student();
        StatusMessage = "Form dikosongkan. Silakan input data mahasiswa baru.";
    }

    // ===================== HELPER =====================
    private void FillStudents(IEnumerable<Student> students)
    {
        Students.Clear();

        foreach (var student in students)
            Students.Add(student);

        RefreshStatistics();
    }

    private void RefreshStatistics()
    {
        OnPropertyChanged(nameof(TotalStudents));
        OnPropertyChanged(nameof(TotalInformatika));
        OnPropertyChanged(nameof(TotalSistemInformasi));
        OnPropertyChanged(nameof(TotalLakiLaki));
        OnPropertyChanged(nameof(TotalPerempuan));
    }

    private Student ReadForm() => new()
    {
        Id = CurrentStudent.Id,
        NIM = CurrentStudent.NIM.Trim(),
        Nama = CurrentStudent.Nama.Trim(),
        Jurusan = CurrentStudent.Jurusan,
        Gender = CurrentStudent.Gender,
        Email = CurrentStudent.Email.Trim()
    };

    private static List<string> Validate(Student student)
    {
        var errors = new List<string>();

        if (string.IsNullOrWhiteSpace(student.NIM))
            errors.Add("NIM wajib diisi.");
        else if (!student.NIM.All(char.IsAsciiDigit))
            errors.Add("NIM hanya boleh berisi angka.");

        if (string.IsNullOrWhiteSpace(student.Nama))
            errors.Add("Nama wajib diisi.");

        if (string.IsNullOrWhiteSpace(student.Jurusan))
            errors.Add("Jurusan wajib dipilih.");

        if (string.IsNullOrWhiteSpace(student.Gender))
            errors.Add("Gender wajib dipilih.");

        if (student.Email.Length > 0 && !EmailPattern.IsMatch(student.Email))
            errors.Add("Format email tidak valid (contoh: nama@gmail.com).");

        return errors;
    }

    private void ShowValidationErrors(List<string> errors)
    {
        StatusMessage = "Data belum valid: " + errors[0];
        _dialogService.ShowWarning(
            "Periksa kembali input berikut:\n\n• " + string.Join("\n• ", errors),
            "Validasi Data");
    }

    private void ShowDatabaseError(string action, Exception ex)
    {
        StatusMessage = $"Gagal {action} data: {ex.Message}";
        _dialogService.ShowError(
            $"Terjadi kesalahan saat {action} data.\n\nDetail: {ex.Message}",
            "Kesalahan Database");
    }

    // ===================== INotifyPropertyChanged =====================
    public event PropertyChangedEventHandler? PropertyChanged;

    protected void OnPropertyChanged(
        [CallerMemberName] string? propertyName = null)
    {
        PropertyChanged?.Invoke(
            this,
            new PropertyChangedEventArgs(propertyName));
    }
}
```

</details>

#### Langkah 11 — Converter

Converter (`IValueConverter`) mengubah nilai binding sebelum ditampilkan ke UI, atau sebelum dikirim kembali ke ViewModel.

📄 `StudentManager/Converters/RowNumberConverter.cs`

```csharp
using System.Globalization;
using System.Windows.Data;

namespace StudentManager.Converters;

/// <summary>
/// Mengubah indeks baris DataGrid (dimulai dari 0) menjadi nomor urut
/// (dimulai dari 1) untuk kolom "No".
/// </summary>
public class RowNumberConverter : IValueConverter
{
    public object Convert(object value, Type targetType, object parameter, CultureInfo culture) =>
        value is int index ? (index + 1).ToString(culture) : string.Empty;

    public object ConvertBack(object value, Type targetType, object parameter, CultureInfo culture) =>
        Binding.DoNothing;
}
```

📄 `StudentManager/Converters/EmptyStringToNullConverter.cs`

```csharp
using System.Globalization;
using System.Windows.Data;

namespace StudentManager.Converters;

/// <summary>
/// Dipakai pada SelectedItem ComboBox. Nilai "" pada model diubah menjadi null
/// agar pilihan ComboBox ikut kosong saat form di-reset, dan sebaliknya
/// null dari ComboBox dikembalikan menjadi "" ke model.
/// </summary>
public class EmptyStringToNullConverter : IValueConverter
{
    // ViewModel → ComboBox
    public object? Convert(object? value, Type targetType, object? parameter, CultureInfo culture) =>
        value is string text && string.IsNullOrWhiteSpace(text) ? null : value;

    // ComboBox → ViewModel
    public object ConvertBack(object? value, Type targetType, object? parameter, CultureInfo culture) =>
        value ?? string.Empty;
}
```

- `RowNumberConverter` mengubah indeks baris DataGrid (mulai 0) menjadi nomor urut (mulai 1) untuk kolom **No**.
- `EmptyStringToNullConverter` membuat pilihan ComboBox ikut kosong saat form di-*reset*. Nilai `""` pada model diubah menjadi `null`, karena `SelectedItem` hanya bisa dikosongkan dengan `null`.

#### Langkah 12 — Style Rich UI: `App.xaml`

Semua warna dan gaya kontrol disimpan sebagai *resource* di `App.xaml`, sehingga tampilan konsisten di seluruh aplikasi dan mudah diubah dari satu tempat. Beberapa bagian pentingnya:

**a. Style tombol dengan ControlTemplate.** Tombol dibuat bersudut membulat. Efek *hover*, *pressed*, dan *disabled* diatur melalui `Trigger`. Style lain (`PrimaryButton`, `DangerButton`, `SecondaryButton`, `IconButton`) mewarisi style dasar ini dengan `BasedOn`.

📄 `StudentManager/App.xaml (potongan)`

```xml
<Style x:Key="BaseButton" TargetType="Button">
    <Setter Property="Foreground" Value="White"/>
    <Setter Property="FontSize" Value="13"/>
    <Setter Property="FontWeight" Value="SemiBold"/>
    <Setter Property="Height" Value="34"/>
    <Setter Property="Padding" Value="16,0"/>
    <Setter Property="BorderThickness" Value="0"/>
    <Setter Property="Cursor" Value="Hand"/>
    <Setter Property="SnapsToDevicePixels" Value="True"/>
    <Setter Property="Template">
        <Setter.Value>
            <ControlTemplate TargetType="Button">
                <Border x:Name="Bd"
                        Background="{TemplateBinding Background}"
                        BorderBrush="{TemplateBinding BorderBrush}"
                        BorderThickness="{TemplateBinding BorderThickness}"
                        CornerRadius="6"
                        Padding="{TemplateBinding Padding}">
                    <ContentPresenter HorizontalAlignment="Center"
                                      VerticalAlignment="Center"
                                      RecognizesAccessKey="True"/>
                </Border>
                <ControlTemplate.Triggers>
                    <Trigger Property="IsMouseOver" Value="True">
                        <Setter TargetName="Bd" Property="Opacity" Value="0.88"/>
                    </Trigger>
                    <Trigger Property="IsPressed" Value="True">
                        <Setter TargetName="Bd" Property="Opacity" Value="0.72"/>
                    </Trigger>
                    <Trigger Property="IsEnabled" Value="False">
                        <Setter TargetName="Bd" Property="Opacity" Value="0.4"/>
                    </Trigger>
                </ControlTemplate.Triggers>
            </ControlTemplate>
        </Setter.Value>
    </Setter>
</Style>

<Style x:Key="PrimaryButton" TargetType="Button" BasedOn="{StaticResource BaseButton}">
    <Setter Property="Background" Value="{StaticResource PrimaryBrush}"/>
</Style>
```

**b. Placeholder TextBox.** Teks petunjuk diambil dari properti `Tag` dan hanya tampil ketika `Text` kosong.

📄 `StudentManager/App.xaml (potongan)`

```xml
<Trigger Property="Text" Value="">
    <Setter TargetName="Placeholder" Property="Visibility" Value="Visible"/>
</Trigger>
<Trigger Property="Text" Value="{x:Null}">
    <Setter TargetName="Placeholder" Property="Visibility" Value="Visible"/>
</Trigger>
```

**c. DataTrigger untuk badge mode.** Warna badge berubah ketika properti `IsEditMode` di ViewModel bernilai `True`.

📄 `StudentManager/App.xaml (potongan)`

```xml
<Style x:Key="ModeBadge" TargetType="Border">
    <Setter Property="CornerRadius" Value="10"/>
    <Setter Property="Padding" Value="10,3"/>
    <Setter Property="VerticalAlignment" Value="Center"/>
    <Setter Property="Background" Value="{StaticResource BadgeBrush}"/>
    <Style.Triggers>
        <DataTrigger Binding="{Binding IsEditMode}" Value="True">
            <Setter Property="Background" Value="{StaticResource BadgeEditBrush}"/>
        </DataTrigger>
    </Style.Triggers>
</Style>
```

<details>
<summary><b>Kode lengkap App.xaml (Style Rich UI)</b> (klik untuk membuka)</summary>

`StudentManager/App.xaml`

```xml
<Application x:Class="StudentManager.App"
             xmlns="http://schemas.microsoft.com/winfx/2006/xaml/presentation"
             xmlns:x="http://schemas.microsoft.com/winfx/2006/xaml"
             xmlns:conv="clr-namespace:StudentManager.Converters"
             StartupUri="MainWindow.xaml">

    <Application.Resources>

        <!-- ==================================================== -->
        <!--  CONVERTER                                            -->
        <!-- ==================================================== -->
        <conv:RowNumberConverter x:Key="RowNumberConverter"/>
        <conv:EmptyStringToNullConverter x:Key="EmptyStringToNullConverter"/>

        <!-- ==================================================== -->
        <!--  WARNA (BRUSH)                                        -->
        <!-- ==================================================== -->
        <SolidColorBrush x:Key="PrimaryBrush" Color="#1976D2"/>
        <SolidColorBrush x:Key="DangerBrush" Color="#E53935"/>
        <SolidColorBrush x:Key="SecondaryButtonBrush" Color="#E2E8F0"/>
        <SolidColorBrush x:Key="SecondaryButtonTextBrush" Color="#334155"/>

        <SolidColorBrush x:Key="WindowBackgroundBrush" Color="#F3F6FB"/>
        <SolidColorBrush x:Key="CardBorderBrush" Color="#E2E8F0"/>
        <SolidColorBrush x:Key="TextPrimaryBrush" Color="#1E293B"/>
        <SolidColorBrush x:Key="TextSecondaryBrush" Color="#64748B"/>

        <SolidColorBrush x:Key="InputBorderBrush" Color="#CBD5E1"/>
        <SolidColorBrush x:Key="InputHoverBorderBrush" Color="#94A3B8"/>
        <SolidColorBrush x:Key="PlaceholderBrush" Color="#94A3B8"/>

        <SolidColorBrush x:Key="FormCardBrush" Color="#F5F9FF"/>
        <SolidColorBrush x:Key="FormCardBorderBrush" Color="#D3E3FD"/>
        <SolidColorBrush x:Key="FormTitleBrush" Color="#1565C0"/>
        <SolidColorBrush x:Key="BadgeBrush" Color="#DCE8FB"/>
        <SolidColorBrush x:Key="BadgeEditBrush" Color="#FFEDD5"/>
        <SolidColorBrush x:Key="BadgeEditTextBrush" Color="#C2410C"/>

        <SolidColorBrush x:Key="InfoCardBrush" Color="#F1FFF7"/>
        <SolidColorBrush x:Key="InfoCardBorderBrush" Color="#C6EBD5"/>
        <SolidColorBrush x:Key="InfoTitleBrush" Color="#159447"/>

        <SolidColorBrush x:Key="TileBlueBrush" Color="#E6F0FD"/>
        <SolidColorBrush x:Key="TileBlueIconBrush" Color="#1E6FD9"/>
        <SolidColorBrush x:Key="TileGreenBrush" Color="#DFF5E8"/>
        <SolidColorBrush x:Key="TileGreenIconBrush" Color="#16A34A"/>
        <SolidColorBrush x:Key="TilePurpleBrush" Color="#EEE8FD"/>
        <SolidColorBrush x:Key="TilePurpleIconBrush" Color="#7C3AED"/>
        <SolidColorBrush x:Key="TileOrangeBrush" Color="#FFF1DC"/>
        <SolidColorBrush x:Key="TileOrangeIconBrush" Color="#F59E0B"/>
        <SolidColorBrush x:Key="TilePinkBrush" Color="#FDE6EB"/>
        <SolidColorBrush x:Key="TilePinkIconBrush" Color="#E11D48"/>

        <SolidColorBrush x:Key="GridHeaderBrush" Color="#E3EEFC"/>
        <SolidColorBrush x:Key="GridLineBrush" Color="#E6EBF2"/>
        <SolidColorBrush x:Key="RowHoverBrush" Color="#F5F9FF"/>
        <SolidColorBrush x:Key="RowSelectedBrush" Color="#DCEBFF"/>

        <!-- ==================================================== -->
        <!--  TEKS                                                 -->
        <!-- ==================================================== -->
        <Style x:Key="IconText" TargetType="TextBlock">
            <Setter Property="FontFamily" Value="Segoe MDL2 Assets"/>
            <Setter Property="VerticalAlignment" Value="Center"/>
        </Style>

        <Style x:Key="SectionTitle" TargetType="TextBlock">
            <Setter Property="FontSize" Value="16"/>
            <Setter Property="FontWeight" Value="Bold"/>
            <Setter Property="VerticalAlignment" Value="Center"/>
        </Style>

        <Style x:Key="FormLabel" TargetType="TextBlock">
            <Setter Property="FontWeight" Value="SemiBold"/>
            <Setter Property="Foreground" Value="{StaticResource TextPrimaryBrush}"/>
            <Setter Property="VerticalAlignment" Value="Center"/>
            <Setter Property="Margin" Value="0,0,0,6"/>
        </Style>

        <Style x:Key="StatIcon" TargetType="TextBlock" BasedOn="{StaticResource IconText}">
            <Setter Property="FontSize" Value="24"/>
            <Setter Property="Margin" Value="0,0,14,0"/>
        </Style>

        <Style x:Key="StatLabel" TargetType="TextBlock">
            <Setter Property="FontSize" Value="12"/>
            <Setter Property="Foreground" Value="{StaticResource TextSecondaryBrush}"/>
        </Style>

        <Style x:Key="StatValue" TargetType="TextBlock">
            <Setter Property="FontSize" Value="22"/>
            <Setter Property="FontWeight" Value="Bold"/>
            <Setter Property="Foreground" Value="{StaticResource TextPrimaryBrush}"/>
        </Style>

        <!-- ==================================================== -->
        <!--  CARD / PANEL                                         -->
        <!-- ==================================================== -->
        <Style x:Key="Card" TargetType="Border">
            <Setter Property="Background" Value="White"/>
            <Setter Property="BorderBrush" Value="{StaticResource CardBorderBrush}"/>
            <Setter Property="BorderThickness" Value="1"/>
            <Setter Property="CornerRadius" Value="10"/>
            <Setter Property="Padding" Value="14"/>
        </Style>

        <Style x:Key="FormCard" TargetType="Border" BasedOn="{StaticResource Card}">
            <Setter Property="Background" Value="{StaticResource FormCardBrush}"/>
            <Setter Property="BorderBrush" Value="{StaticResource FormCardBorderBrush}"/>
        </Style>

        <Style x:Key="InfoCard" TargetType="Border" BasedOn="{StaticResource Card}">
            <Setter Property="Background" Value="{StaticResource InfoCardBrush}"/>
            <Setter Property="BorderBrush" Value="{StaticResource InfoCardBorderBrush}"/>
        </Style>

        <Style x:Key="StatTile" TargetType="Border">
            <Setter Property="CornerRadius" Value="8"/>
            <Setter Property="Padding" Value="14,8"/>
        </Style>

        <!-- Badge "Mode Tambah" / "Mode Edit" (berubah lewat DataTrigger) -->
        <Style x:Key="ModeBadge" TargetType="Border">
            <Setter Property="CornerRadius" Value="10"/>
            <Setter Property="Padding" Value="10,3"/>
            <Setter Property="VerticalAlignment" Value="Center"/>
            <Setter Property="Background" Value="{StaticResource BadgeBrush}"/>
            <Style.Triggers>
                <DataTrigger Binding="{Binding IsEditMode}" Value="True">
                    <Setter Property="Background" Value="{StaticResource BadgeEditBrush}"/>
                </DataTrigger>
            </Style.Triggers>
        </Style>

        <Style x:Key="ModeBadgeText" TargetType="TextBlock">
            <Setter Property="FontSize" Value="11"/>
            <Setter Property="FontWeight" Value="SemiBold"/>
            <Setter Property="Foreground" Value="{StaticResource FormTitleBrush}"/>
            <Style.Triggers>
                <DataTrigger Binding="{Binding IsEditMode}" Value="True">
                    <Setter Property="Foreground" Value="{StaticResource BadgeEditTextBrush}"/>
                </DataTrigger>
            </Style.Triggers>
        </Style>

        <!-- ==================================================== -->
        <!--  BUTTON                                               -->
        <!-- ==================================================== -->
        <Style x:Key="BaseButton" TargetType="Button">
            <Setter Property="Foreground" Value="White"/>
            <Setter Property="FontSize" Value="13"/>
            <Setter Property="FontWeight" Value="SemiBold"/>
            <Setter Property="Height" Value="34"/>
            <Setter Property="Padding" Value="16,0"/>
            <Setter Property="BorderThickness" Value="0"/>
            <Setter Property="Cursor" Value="Hand"/>
            <Setter Property="SnapsToDevicePixels" Value="True"/>
            <Setter Property="Template">
                <Setter.Value>
                    <ControlTemplate TargetType="Button">
                        <Border x:Name="Bd"
                                Background="{TemplateBinding Background}"
                                BorderBrush="{TemplateBinding BorderBrush}"
                                BorderThickness="{TemplateBinding BorderThickness}"
                                CornerRadius="6"
                                Padding="{TemplateBinding Padding}">
                            <ContentPresenter HorizontalAlignment="Center"
                                              VerticalAlignment="Center"
                                              RecognizesAccessKey="True"/>
                        </Border>
                        <ControlTemplate.Triggers>
                            <Trigger Property="IsMouseOver" Value="True">
                                <Setter TargetName="Bd" Property="Opacity" Value="0.88"/>
                            </Trigger>
                            <Trigger Property="IsPressed" Value="True">
                                <Setter TargetName="Bd" Property="Opacity" Value="0.72"/>
                            </Trigger>
                            <Trigger Property="IsEnabled" Value="False">
                                <Setter TargetName="Bd" Property="Opacity" Value="0.4"/>
                            </Trigger>
                        </ControlTemplate.Triggers>
                    </ControlTemplate>
                </Setter.Value>
            </Setter>
        </Style>

        <Style x:Key="PrimaryButton" TargetType="Button" BasedOn="{StaticResource BaseButton}">
            <Setter Property="Background" Value="{StaticResource PrimaryBrush}"/>
        </Style>

        <Style x:Key="DangerButton" TargetType="Button" BasedOn="{StaticResource BaseButton}">
            <Setter Property="Background" Value="{StaticResource DangerBrush}"/>
        </Style>

        <Style x:Key="SecondaryButton" TargetType="Button" BasedOn="{StaticResource BaseButton}">
            <Setter Property="Background" Value="{StaticResource SecondaryButtonBrush}"/>
            <Setter Property="Foreground" Value="{StaticResource SecondaryButtonTextBrush}"/>
        </Style>

        <!-- Tombol ikon kecil untuk kolom Aksi pada DataGrid -->
        <Style x:Key="IconButton" TargetType="Button" BasedOn="{StaticResource BaseButton}">
            <Setter Property="Width" Value="32"/>
            <Setter Property="Height" Value="28"/>
            <Setter Property="Padding" Value="0"/>
            <Setter Property="FontFamily" Value="Segoe MDL2 Assets"/>
            <Setter Property="FontWeight" Value="Normal"/>
        </Style>

        <Style x:Key="IconButtonPrimary" TargetType="Button" BasedOn="{StaticResource IconButton}">
            <Setter Property="Background" Value="{StaticResource PrimaryBrush}"/>
        </Style>

        <Style x:Key="IconButtonDanger" TargetType="Button" BasedOn="{StaticResource IconButton}">
            <Setter Property="Background" Value="{StaticResource DangerBrush}"/>
        </Style>

        <!-- ==================================================== -->
        <!--  TEXTBOX (dengan placeholder dari properti Tag)       -->
        <!-- ==================================================== -->
        <Style x:Key="InputTextBox" TargetType="TextBox">
            <Setter Property="Height" Value="32"/>
            <Setter Property="Margin" Value="0,0,0,6"/>
            <Setter Property="Padding" Value="10,0"/>
            <Setter Property="FontSize" Value="13"/>
            <Setter Property="Foreground" Value="{StaticResource TextPrimaryBrush}"/>
            <Setter Property="Background" Value="White"/>
            <Setter Property="BorderBrush" Value="{StaticResource InputBorderBrush}"/>
            <Setter Property="BorderThickness" Value="1"/>
            <Setter Property="VerticalContentAlignment" Value="Center"/>
            <Setter Property="Template">
                <Setter.Value>
                    <ControlTemplate TargetType="TextBox">
                        <Border x:Name="Bd"
                                Background="{TemplateBinding Background}"
                                BorderBrush="{TemplateBinding BorderBrush}"
                                BorderThickness="{TemplateBinding BorderThickness}"
                                CornerRadius="6"
                                SnapsToDevicePixels="True">
                            <Grid Margin="{TemplateBinding Padding}">
                                <ScrollViewer x:Name="PART_ContentHost"
                                              Focusable="False"
                                              VerticalAlignment="{TemplateBinding VerticalContentAlignment}"
                                              HorizontalScrollBarVisibility="Hidden"
                                              VerticalScrollBarVisibility="Hidden"/>
                                <TextBlock x:Name="Placeholder"
                                           Text="{Binding Tag, RelativeSource={RelativeSource TemplatedParent}}"
                                           Foreground="{StaticResource PlaceholderBrush}"
                                           VerticalAlignment="Center"
                                           Margin="2,0,0,0"
                                           IsHitTestVisible="False"
                                           Visibility="Collapsed"/>
                            </Grid>
                        </Border>
                        <ControlTemplate.Triggers>
                            <Trigger Property="Text" Value="">
                                <Setter TargetName="Placeholder" Property="Visibility" Value="Visible"/>
                            </Trigger>
                            <Trigger Property="Text" Value="{x:Null}">
                                <Setter TargetName="Placeholder" Property="Visibility" Value="Visible"/>
                            </Trigger>
                            <Trigger Property="IsMouseOver" Value="True">
                                <Setter TargetName="Bd" Property="BorderBrush" Value="{StaticResource InputHoverBorderBrush}"/>
                            </Trigger>
                            <Trigger Property="IsKeyboardFocused" Value="True">
                                <Setter TargetName="Bd" Property="BorderBrush" Value="{StaticResource PrimaryBrush}"/>
                            </Trigger>
                            <Trigger Property="IsEnabled" Value="False">
                                <Setter TargetName="Bd" Property="Opacity" Value="0.6"/>
                            </Trigger>
                        </ControlTemplate.Triggers>
                    </ControlTemplate>
                </Setter.Value>
            </Setter>
        </Style>

        <!-- ==================================================== -->
        <!--  COMBOBOX (sudut membulat + placeholder dari Tag)     -->
        <!-- ==================================================== -->
        <ControlTemplate x:Key="ComboBoxToggleTemplate" TargetType="ToggleButton">
            <Border x:Name="Bd"
                    Background="{TemplateBinding Background}"
                    BorderBrush="{TemplateBinding BorderBrush}"
                    BorderThickness="{TemplateBinding BorderThickness}"
                    CornerRadius="6">
                <Path Data="M 0 0 L 5 5 L 10 0"
                      Stroke="{StaticResource TextSecondaryBrush}"
                      StrokeThickness="1.6"
                      HorizontalAlignment="Right"
                      VerticalAlignment="Center"
                      Margin="0,0,12,0"/>
            </Border>
            <ControlTemplate.Triggers>
                <Trigger Property="IsMouseOver" Value="True">
                    <Setter TargetName="Bd" Property="BorderBrush" Value="{StaticResource InputHoverBorderBrush}"/>
                </Trigger>
                <Trigger Property="IsChecked" Value="True">
                    <Setter TargetName="Bd" Property="BorderBrush" Value="{StaticResource PrimaryBrush}"/>
                </Trigger>
            </ControlTemplate.Triggers>
        </ControlTemplate>

        <Style x:Key="InputComboBox" TargetType="ComboBox">
            <Setter Property="Height" Value="32"/>
            <Setter Property="Margin" Value="0,0,0,6"/>
            <Setter Property="Padding" Value="12,0,32,0"/>
            <Setter Property="FontSize" Value="13"/>
            <Setter Property="Foreground" Value="{StaticResource TextPrimaryBrush}"/>
            <Setter Property="Background" Value="White"/>
            <Setter Property="BorderBrush" Value="{StaticResource InputBorderBrush}"/>
            <Setter Property="BorderThickness" Value="1"/>
            <Setter Property="Cursor" Value="Hand"/>
            <Setter Property="SnapsToDevicePixels" Value="True"/>
            <Setter Property="ItemContainerStyle">
                <Setter.Value>
                    <Style TargetType="ComboBoxItem">
                        <Setter Property="Padding" Value="10,6"/>
                    </Style>
                </Setter.Value>
            </Setter>
            <Setter Property="Template">
                <Setter.Value>
                    <ControlTemplate TargetType="ComboBox">
                        <Grid>
                            <ToggleButton x:Name="ToggleButton"
                                          Template="{StaticResource ComboBoxToggleTemplate}"
                                          Background="{TemplateBinding Background}"
                                          BorderBrush="{TemplateBinding BorderBrush}"
                                          BorderThickness="{TemplateBinding BorderThickness}"
                                          Focusable="False"
                                          ClickMode="Press"
                                          IsChecked="{Binding IsDropDownOpen, Mode=TwoWay, RelativeSource={RelativeSource TemplatedParent}}"/>

                            <ContentPresenter x:Name="ContentSite"
                                              Margin="{TemplateBinding Padding}"
                                              HorizontalAlignment="Left"
                                              VerticalAlignment="Center"
                                              IsHitTestVisible="False"
                                              Content="{TemplateBinding SelectionBoxItem}"
                                              ContentTemplate="{TemplateBinding SelectionBoxItemTemplate}"
                                              ContentTemplateSelector="{TemplateBinding ItemTemplateSelector}"/>

                            <TextBlock x:Name="Placeholder"
                                       Margin="{TemplateBinding Padding}"
                                       VerticalAlignment="Center"
                                       IsHitTestVisible="False"
                                       Foreground="{StaticResource PlaceholderBrush}"
                                       Text="{Binding Tag, RelativeSource={RelativeSource TemplatedParent}}"
                                       Visibility="Collapsed"/>

                            <Popup x:Name="PART_Popup"
                                   Placement="Bottom"
                                   IsOpen="{TemplateBinding IsDropDownOpen}"
                                   AllowsTransparency="True"
                                   Focusable="False"
                                   PopupAnimation="Slide">
                                <Border Margin="0,4,0,0"
                                        MinWidth="{TemplateBinding ActualWidth}"
                                        MaxHeight="{TemplateBinding MaxDropDownHeight}"
                                        Background="White"
                                        BorderBrush="{StaticResource InputBorderBrush}"
                                        BorderThickness="1"
                                        CornerRadius="6"
                                        Padding="4">
                                    <ScrollViewer SnapsToDevicePixels="True">
                                        <ItemsPresenter KeyboardNavigation.DirectionalNavigation="Contained"/>
                                    </ScrollViewer>
                                </Border>
                            </Popup>
                        </Grid>
                        <ControlTemplate.Triggers>
                            <Trigger Property="SelectedItem" Value="{x:Null}">
                                <Setter TargetName="Placeholder" Property="Visibility" Value="Visible"/>
                            </Trigger>
                            <Trigger Property="IsKeyboardFocusWithin" Value="True">
                                <Setter TargetName="ToggleButton" Property="BorderBrush" Value="{StaticResource PrimaryBrush}"/>
                            </Trigger>
                            <Trigger Property="IsEnabled" Value="False">
                                <Setter Property="Opacity" Value="0.6"/>
                            </Trigger>
                        </ControlTemplate.Triggers>
                    </ControlTemplate>
                </Setter.Value>
            </Setter>
        </Style>

        <!-- ==================================================== -->
        <!--  DATAGRID                                             -->
        <!-- ==================================================== -->
        <Style TargetType="DataGrid">
            <Setter Property="Background" Value="White"/>
            <Setter Property="BorderThickness" Value="0"/>
            <Setter Property="FontSize" Value="13"/>
            <Setter Property="Foreground" Value="{StaticResource TextPrimaryBrush}"/>
            <Setter Property="RowHeight" Value="36"/>
            <Setter Property="ColumnHeaderHeight" Value="38"/>
            <Setter Property="HeadersVisibility" Value="Column"/>
            <Setter Property="GridLinesVisibility" Value="Horizontal"/>
            <Setter Property="HorizontalGridLinesBrush" Value="{StaticResource GridLineBrush}"/>
            <Setter Property="SelectionMode" Value="Single"/>
            <Setter Property="SelectionUnit" Value="FullRow"/>
            <Setter Property="CanUserAddRows" Value="False"/>
            <Setter Property="CanUserDeleteRows" Value="False"/>
            <Setter Property="CanUserResizeRows" Value="False"/>
            <Setter Property="CanUserReorderColumns" Value="False"/>
            <Setter Property="HorizontalScrollBarVisibility" Value="Disabled"/>
            <Setter Property="VerticalScrollBarVisibility" Value="Auto"/>
        </Style>

        <Style TargetType="DataGridColumnHeader">
            <Setter Property="Background" Value="{StaticResource GridHeaderBrush}"/>
            <Setter Property="Foreground" Value="{StaticResource TextPrimaryBrush}"/>
            <Setter Property="FontWeight" Value="SemiBold"/>
            <Setter Property="Padding" Value="12,0"/>
            <Setter Property="HorizontalContentAlignment" Value="Left"/>
            <Setter Property="VerticalContentAlignment" Value="Center"/>
            <Setter Property="BorderBrush" Value="{StaticResource GridLineBrush}"/>
            <Setter Property="BorderThickness" Value="0,0,0,1"/>
            <Setter Property="Cursor" Value="Hand"/>
            <Setter Property="Template">
                <Setter.Value>
                    <ControlTemplate TargetType="DataGridColumnHeader">
                        <Border Background="{TemplateBinding Background}"
                                BorderBrush="{TemplateBinding BorderBrush}"
                                BorderThickness="{TemplateBinding BorderThickness}"
                                Padding="{TemplateBinding Padding}">
                            <Grid>
                                <Grid.ColumnDefinitions>
                                    <ColumnDefinition Width="*"/>
                                    <ColumnDefinition Width="Auto"/>
                                </Grid.ColumnDefinitions>
                                <ContentPresenter HorizontalAlignment="{TemplateBinding HorizontalContentAlignment}"
                                                  VerticalAlignment="{TemplateBinding VerticalContentAlignment}"/>
                                <Path x:Name="SortArrow"
                                      Grid.Column="1"
                                      Width="8"
                                      Height="5"
                                      Margin="6,0,0,0"
                                      Stretch="Fill"
                                      Data="M 0 0 L 1 0 L 0.5 1 Z"
                                      Fill="{StaticResource TextSecondaryBrush}"
                                      VerticalAlignment="Center"
                                      RenderTransformOrigin="0.5,0.5"
                                      Visibility="Collapsed"/>
                            </Grid>
                        </Border>
                        <ControlTemplate.Triggers>
                            <Trigger Property="SortDirection" Value="Ascending">
                                <Setter TargetName="SortArrow" Property="Visibility" Value="Visible"/>
                                <Setter TargetName="SortArrow" Property="RenderTransform">
                                    <Setter.Value>
                                        <RotateTransform Angle="180"/>
                                    </Setter.Value>
                                </Setter>
                            </Trigger>
                            <Trigger Property="SortDirection" Value="Descending">
                                <Setter TargetName="SortArrow" Property="Visibility" Value="Visible"/>
                            </Trigger>
                        </ControlTemplate.Triggers>
                    </ControlTemplate>
                </Setter.Value>
            </Setter>
        </Style>

        <!-- Header kolom rata tengah (dipakai kolom No dan Aksi) -->
        <Style x:Key="CenterHeader" TargetType="DataGridColumnHeader"
               BasedOn="{StaticResource {x:Type DataGridColumnHeader}}">
            <Setter Property="HorizontalContentAlignment" Value="Center"/>
        </Style>

        <Style x:Key="CenterCellText" TargetType="TextBlock">
            <Setter Property="HorizontalAlignment" Value="Center"/>
        </Style>

        <Style TargetType="DataGridRow">
            <Setter Property="Background" Value="White"/>
            <Style.Triggers>
                <Trigger Property="IsMouseOver" Value="True">
                    <Setter Property="Background" Value="{StaticResource RowHoverBrush}"/>
                </Trigger>
            </Style.Triggers>
        </Style>

        <Style TargetType="DataGridCell">
            <Setter Property="BorderThickness" Value="0"/>
            <Setter Property="Padding" Value="12,0"/>
            <Setter Property="FocusVisualStyle" Value="{x:Null}"/>
            <Setter Property="Template">
                <Setter.Value>
                    <ControlTemplate TargetType="DataGridCell">
                        <Border Background="{TemplateBinding Background}"
                                BorderBrush="{TemplateBinding BorderBrush}"
                                BorderThickness="{TemplateBinding BorderThickness}"
                                Padding="{TemplateBinding Padding}"
                                SnapsToDevicePixels="True">
                            <ContentPresenter VerticalAlignment="Center"/>
                        </Border>
                    </ControlTemplate>
                </Setter.Value>
            </Setter>
            <Style.Triggers>
                <Trigger Property="IsSelected" Value="True">
                    <Setter Property="Background" Value="{StaticResource RowSelectedBrush}"/>
                    <Setter Property="Foreground" Value="{StaticResource TextPrimaryBrush}"/>
                </Trigger>
            </Style.Triggers>
        </Style>

    </Application.Resources>
</Application>
```

</details>

#### Langkah 13 — View: `MainWindow.xaml`

**a. Window dan Grid.** Window berukuran 1200 × 750 dan berada di tengah layar. `Grid` utama memiliki lima baris: Header, Input + Informasi, Search, DataGrid, dan Status bar.

📄 `StudentManager/MainWindow.xaml (potongan)`

```xml
<Grid Margin="16,14,16,10">
    <Grid.RowDefinitions>
        <RowDefinition Height="Auto"/>  <!-- 0: Header -->
        <RowDefinition Height="Auto"/>  <!-- 1: Input Mahasiswa + Informasi -->
        <RowDefinition Height="Auto"/>  <!-- 2: Search -->
        <RowDefinition Height="*"/>     <!-- 3: DataGrid -->
        <RowDefinition Height="Auto"/>  <!-- 4: Status bar -->
    </Grid.RowDefinitions>
```

**b. Header Rich UI.** `Border` dengan latar *gradient* biru (`#1565C0` → `#1E88E5`), sudut membulat, ikon toga, judul aplikasi, tanggal hari ini (`TodayText`), dan teks *Welcome, Admin*.

**c. Form input dan binding.** Setiap `TextBox` dan `ComboBox` di-*bind* ke properti `CurrentStudent`. Opsi `UpdateSourceTrigger=PropertyChanged` membuat ViewModel langsung menerima setiap ketikan.

📄 `StudentManager/MainWindow.xaml (potongan)`

```xml
<TextBlock Grid.Row="0" Text="NIM" Style="{StaticResource FormLabel}"/>
<TextBox Grid.Row="0" Grid.Column="1"
         Style="{StaticResource InputTextBox}"
         Tag="Masukkan NIM (angka)"
         MaxLength="20"
         Text="{Binding CurrentStudent.NIM,
                UpdateSourceTrigger=PropertyChanged}"/>

<TextBlock Grid.Row="1" Text="Nama" Style="{StaticResource FormLabel}"/>
<TextBox Grid.Row="1" Grid.Column="1"
         Style="{StaticResource InputTextBox}"
         Tag="Masukkan nama lengkap"
         MaxLength="100"
         Text="{Binding CurrentStudent.Nama,
                UpdateSourceTrigger=PropertyChanged}"/>

<TextBlock Grid.Row="2" Text="Jurusan" Style="{StaticResource FormLabel}"/>
<ComboBox Grid.Row="2" Grid.Column="1"
          Style="{StaticResource InputComboBox}"
          Tag="Pilih jurusan"
          ItemsSource="{Binding JurusanOptions}"
          SelectedItem="{Binding CurrentStudent.Jurusan,
                         Converter={StaticResource EmptyStringToNullConverter}}"/>

<TextBlock Grid.Row="3" Text="Gender" Style="{StaticResource FormLabel}"/>
<ComboBox Grid.Row="3" Grid.Column="1"
          Style="{StaticResource InputComboBox}"
          Tag="Pilih gender"
          ItemsSource="{Binding GenderOptions}"
          SelectedItem="{Binding CurrentStudent.Gender,
                         Converter={StaticResource EmptyStringToNullConverter}}"/>

<TextBlock Grid.Row="4" Text="Email" Style="{StaticResource FormLabel}"/>
<TextBox Grid.Row="4" Grid.Column="1"
         Style="{StaticResource InputTextBox}"
         Tag="Masukkan email (opsional)"
         MaxLength="100"
         Text="{Binding CurrentStudent.Email,
                UpdateSourceTrigger=PropertyChanged}"/>
```

**d. Tombol dengan Command.** Tombol tidak memakai event `Click`. Tombol Hapus hanya aktif pada Mode Edit melalui binding `IsEnabled="{Binding IsEditMode}"`.

📄 `StudentManager/MainWindow.xaml (potongan)`

```xml
<Button Style="{StaticResource PrimaryButton}"
        Command="{Binding SaveCommand}"
        MinWidth="110">
    <StackPanel Orientation="Horizontal">
        <TextBlock Text="&#xE74E;" Style="{StaticResource IconText}" Margin="0,0,8,0"/>
        <TextBlock Text="{Binding SaveButtonText}"/>
    </StackPanel>
</Button>

<Button Style="{StaticResource DangerButton}"
        Command="{Binding DeleteCommand}"
        IsEnabled="{Binding IsEditMode}"
        MinWidth="100"
        Margin="10,0,0,0">
    <StackPanel Orientation="Horizontal">
        <TextBlock Text="&#xE74D;" Style="{StaticResource IconText}" Margin="0,0,8,0"/>
        <TextBlock Text="Hapus"/>
    </StackPanel>
</Button>
```

**e. Panel INFORMASI.** Setiap kartu statistik menampilkan properti ViewModel melalui `TextBlock` + binding. Contoh kartu Total Mahasiswa:

📄 `StudentManager/MainWindow.xaml (potongan)`

```xml
<!-- Total Mahasiswa -->
<Border Grid.Row="1" Style="{StaticResource StatTile}"
        Background="{StaticResource TileBlueBrush}" Margin="0,0,0,8">
    <StackPanel Orientation="Horizontal">
        <TextBlock Text="&#xE716;" Style="{StaticResource StatIcon}"
                   FontSize="26" Foreground="{StaticResource TileBlueIconBrush}"/>
        <StackPanel VerticalAlignment="Center">
            <TextBlock Text="Total Mahasiswa" Style="{StaticResource StatLabel}"/>
            <TextBlock Text="{Binding TotalStudents}"
                       Style="{StaticResource StatValue}" FontSize="26"/>
        </StackPanel>
    </StackPanel>
</Border>
```

**f. Search.** `KeyBinding` membuat tombol Enter langsung menjalankan `SearchCommand`.

📄 `StudentManager/MainWindow.xaml (potongan)`

```xml
<TextBox Grid.Column="1"
         Style="{StaticResource InputTextBox}"
         Margin="0"
         Tag="Cari berdasarkan NIM, Nama, atau Jurusan..."
         Text="{Binding SearchText, UpdateSourceTrigger=PropertyChanged}">
    <TextBox.InputBindings>
        <KeyBinding Key="Enter" Command="{Binding SearchCommand}"/>
    </TextBox.InputBindings>
</TextBox>
```

**g. DataGrid.** `ItemsSource` terhubung ke `Students` dan `SelectedItem` ke `SelectedStudent` (TwoWay). Kolom **No** memakai `AlternationIndex` dan `RowNumberConverter`. Kolom **Aksi** berisi tombol Edit dan Hapus yang mencari `DataContext` milik DataGrid melalui `RelativeSource` dan mengirim data barisnya lewat `CommandParameter="{Binding}"`.

📄 `StudentManager/MainWindow.xaml (potongan)`

```xml
<DataGrid ItemsSource="{Binding Students}"
          SelectedItem="{Binding SelectedStudent, Mode=TwoWay}"
          AutoGenerateColumns="False"
          IsReadOnly="True"
          AlternationCount="{Binding Items.Count, RelativeSource={RelativeSource Self}}">
    <DataGrid.Columns>
        <DataGridTextColumn Header="No"
                            Width="50"
                            CanUserSort="False"
                            HeaderStyle="{StaticResource CenterHeader}"
                            ElementStyle="{StaticResource CenterCellText}"
                            Binding="{Binding (ItemsControl.AlternationIndex),
                                      RelativeSource={RelativeSource AncestorType=DataGridRow},
                                      Converter={StaticResource RowNumberConverter},
                                      Mode=OneWay}"/>
        <DataGridTextColumn Header="NIM" Width="110" Binding="{Binding NIM}"/>
        <DataGridTextColumn Header="Nama" Width="1.4*" Binding="{Binding Nama}"/>
        <DataGridTextColumn Header="Jurusan" Width="*" Binding="{Binding Jurusan}"/>
        <DataGridTextColumn Header="Gender" Width="110" Binding="{Binding Gender}"/>
        <DataGridTextColumn Header="Email" Width="1.4*" Binding="{Binding Email}"/>
        <DataGridTemplateColumn Header="Aksi" Width="110" HeaderStyle="{StaticResource CenterHeader}">
            <DataGridTemplateColumn.CellTemplate>
                <DataTemplate>
                    <StackPanel Orientation="Horizontal" HorizontalAlignment="Center">
                        <Button Style="{StaticResource IconButtonPrimary}"
                                Content="&#xE70F;"
                                ToolTip="Edit data"
                                Command="{Binding DataContext.EditCommand,
                                          RelativeSource={RelativeSource AncestorType=DataGrid}}"
                                CommandParameter="{Binding}"/>
                        <Button Style="{StaticResource IconButtonDanger}"
                                Content="&#xE74D;"
                                ToolTip="Hapus data"
                                Margin="6,0,0,0"
                                Command="{Binding DataContext.DeleteCommand,
                                          RelativeSource={RelativeSource AncestorType=DataGrid}}"
                                CommandParameter="{Binding}"/>
                    </StackPanel>
                </DataTemplate>
            </DataGridTemplateColumn.CellTemplate>
        </DataGridTemplateColumn>
    </DataGrid.Columns>
</DataGrid>
```

<details>
<summary><b>Kode lengkap MainWindow.xaml</b> (klik untuk membuka)</summary>

`StudentManager/MainWindow.xaml`

```xml
<Window x:Class="StudentManager.MainWindow"
        xmlns="http://schemas.microsoft.com/winfx/2006/xaml/presentation"
        xmlns:x="http://schemas.microsoft.com/winfx/2006/xaml"
        xmlns:d="http://schemas.microsoft.com/expression/blend/2008"
        xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"
        xmlns:vm="clr-namespace:StudentManager.ViewModels"
        mc:Ignorable="d"
        d:DataContext="{d:DesignInstance Type=vm:StudentViewModel, IsDesignTimeCreatable=False}"
        Title="Student Management System"
        Width="1200"
        Height="750"
        MinWidth="1000"
        MinHeight="650"
        WindowStartupLocation="CenterScreen"
        Background="{StaticResource WindowBackgroundBrush}"
        FontFamily="Segoe UI"
        UseLayoutRounding="True">

    <Grid Margin="16,14,16,10">
        <Grid.RowDefinitions>
            <RowDefinition Height="Auto"/>  <!-- 0: Header -->
            <RowDefinition Height="Auto"/>  <!-- 1: Input Mahasiswa + Informasi -->
            <RowDefinition Height="Auto"/>  <!-- 2: Search -->
            <RowDefinition Height="*"/>     <!-- 3: DataGrid -->
            <RowDefinition Height="Auto"/>  <!-- 4: Status bar -->
        </Grid.RowDefinitions>

        <!-- ================================================== -->
        <!--  1. HEADER RICH UI                                  -->
        <!-- ================================================== -->
        <Border Grid.Row="0"
                CornerRadius="10"
                Padding="20,10"
                Margin="0,0,0,10">
            <Border.Background>
                <LinearGradientBrush StartPoint="0,0" EndPoint="1,0">
                    <GradientStop Color="#1565C0" Offset="0"/>
                    <GradientStop Color="#1E88E5" Offset="1"/>
                </LinearGradientBrush>
            </Border.Background>

            <Grid>
                <Grid.ColumnDefinitions>
                    <ColumnDefinition Width="Auto"/>
                    <ColumnDefinition Width="*"/>
                    <ColumnDefinition Width="Auto"/>
                </Grid.ColumnDefinitions>

                <Border Width="46" Height="46" CornerRadius="23"
                        Background="#33FFFFFF" Margin="0,0,14,0">
                    <TextBlock Text="&#xE7BE;" Style="{StaticResource IconText}"
                               FontSize="24" Foreground="White"
                               HorizontalAlignment="Center"/>
                </Border>

                <StackPanel Grid.Column="1" VerticalAlignment="Center">
                    <TextBlock Text="STUDENT MANAGEMENT SYSTEM"
                               Foreground="White"
                               FontSize="22"
                               FontWeight="Bold"/>
                    <TextBlock Text="Manage student data"
                               Foreground="White"
                               FontSize="13"
                               Opacity="0.85"/>
                </StackPanel>

                <StackPanel Grid.Column="2" VerticalAlignment="Center">
                    <StackPanel Orientation="Horizontal" HorizontalAlignment="Right">
                        <TextBlock Text="&#xE787;" Style="{StaticResource IconText}"
                                   FontSize="14" Foreground="White" Margin="0,0,8,0"/>
                        <TextBlock Text="{Binding TodayText}"
                                   Foreground="White" FontSize="13" FontWeight="SemiBold"/>
                    </StackPanel>
                    <TextBlock Text="Welcome, Admin"
                               Foreground="White" FontSize="12" Opacity="0.85"
                               HorizontalAlignment="Right" Margin="0,4,0,0"/>
                </StackPanel>
            </Grid>
        </Border>

        <!-- ================================================== -->
        <!--  2. INPUT MAHASISWA + PANEL INFORMASI               -->
        <!-- ================================================== -->
        <Grid Grid.Row="1" Margin="0,0,0,10">
            <Grid.ColumnDefinitions>
                <ColumnDefinition Width="*"/>
                <ColumnDefinition Width="16"/>
                <ColumnDefinition Width="1.1*"/>
            </Grid.ColumnDefinitions>

            <!-- ===== Form Input Mahasiswa ===== -->
            <Border Grid.Column="0" Style="{StaticResource FormCard}">
                <StackPanel>
                    <Grid Margin="0,0,0,10">
                        <StackPanel Orientation="Horizontal">
                            <TextBlock Text="&#xE70F;" Style="{StaticResource IconText}"
                                       FontSize="16" Foreground="{StaticResource FormTitleBrush}"
                                       Margin="0,0,8,0"/>
                            <TextBlock Text="INPUT MAHASISWA"
                                       Style="{StaticResource SectionTitle}"
                                       Foreground="{StaticResource FormTitleBrush}"/>
                        </StackPanel>
                        <Border Style="{StaticResource ModeBadge}" HorizontalAlignment="Right">
                            <TextBlock Text="{Binding FormModeText}" Style="{StaticResource ModeBadgeText}"/>
                        </Border>
                    </Grid>

                    <Grid>
                        <Grid.ColumnDefinitions>
                            <ColumnDefinition Width="80"/>
                            <ColumnDefinition Width="*"/>
                        </Grid.ColumnDefinitions>
                        <Grid.RowDefinitions>
                            <RowDefinition Height="Auto"/>
                            <RowDefinition Height="Auto"/>
                            <RowDefinition Height="Auto"/>
                            <RowDefinition Height="Auto"/>
                            <RowDefinition Height="Auto"/>
                        </Grid.RowDefinitions>

                        <TextBlock Grid.Row="0" Text="NIM" Style="{StaticResource FormLabel}"/>
                        <TextBox Grid.Row="0" Grid.Column="1"
                                 Style="{StaticResource InputTextBox}"
                                 Tag="Masukkan NIM (angka)"
                                 MaxLength="20"
                                 Text="{Binding CurrentStudent.NIM,
                                        UpdateSourceTrigger=PropertyChanged}"/>

                        <TextBlock Grid.Row="1" Text="Nama" Style="{StaticResource FormLabel}"/>
                        <TextBox Grid.Row="1" Grid.Column="1"
                                 Style="{StaticResource InputTextBox}"
                                 Tag="Masukkan nama lengkap"
                                 MaxLength="100"
                                 Text="{Binding CurrentStudent.Nama,
                                        UpdateSourceTrigger=PropertyChanged}"/>

                        <TextBlock Grid.Row="2" Text="Jurusan" Style="{StaticResource FormLabel}"/>
                        <ComboBox Grid.Row="2" Grid.Column="1"
                                  Style="{StaticResource InputComboBox}"
                                  Tag="Pilih jurusan"
                                  ItemsSource="{Binding JurusanOptions}"
                                  SelectedItem="{Binding CurrentStudent.Jurusan,
                                                 Converter={StaticResource EmptyStringToNullConverter}}"/>

                        <TextBlock Grid.Row="3" Text="Gender" Style="{StaticResource FormLabel}"/>
                        <ComboBox Grid.Row="3" Grid.Column="1"
                                  Style="{StaticResource InputComboBox}"
                                  Tag="Pilih gender"
                                  ItemsSource="{Binding GenderOptions}"
                                  SelectedItem="{Binding CurrentStudent.Gender,
                                                 Converter={StaticResource EmptyStringToNullConverter}}"/>

                        <TextBlock Grid.Row="4" Text="Email" Style="{StaticResource FormLabel}"/>
                        <TextBox Grid.Row="4" Grid.Column="1"
                                 Style="{StaticResource InputTextBox}"
                                 Tag="Masukkan email (opsional)"
                                 MaxLength="100"
                                 Text="{Binding CurrentStudent.Email,
                                        UpdateSourceTrigger=PropertyChanged}"/>
                    </Grid>

                    <StackPanel Orientation="Horizontal" Margin="0,4,0,0">
                        <Button Style="{StaticResource PrimaryButton}"
                                Command="{Binding SaveCommand}"
                                MinWidth="110">
                            <StackPanel Orientation="Horizontal">
                                <TextBlock Text="&#xE74E;" Style="{StaticResource IconText}" Margin="0,0,8,0"/>
                                <TextBlock Text="{Binding SaveButtonText}"/>
                            </StackPanel>
                        </Button>

                        <Button Style="{StaticResource DangerButton}"
                                Command="{Binding DeleteCommand}"
                                IsEnabled="{Binding IsEditMode}"
                                MinWidth="100"
                                Margin="10,0,0,0">
                            <StackPanel Orientation="Horizontal">
                                <TextBlock Text="&#xE74D;" Style="{StaticResource IconText}" Margin="0,0,8,0"/>
                                <TextBlock Text="Hapus"/>
                            </StackPanel>
                        </Button>

                        <Button Style="{StaticResource SecondaryButton}"
                                Command="{Binding ResetCommand}"
                                MinWidth="100"
                                Margin="10,0,0,0">
                            <StackPanel Orientation="Horizontal">
                                <TextBlock Text="&#xE72C;" Style="{StaticResource IconText}" Margin="0,0,8,0"/>
                                <TextBlock Text="Reset"/>
                            </StackPanel>
                        </Button>
                    </StackPanel>
                </StackPanel>
            </Border>

            <!-- ===== Panel INFORMASI ===== -->
            <Border Grid.Column="2" Style="{StaticResource InfoCard}">
                <Grid>
                    <Grid.RowDefinitions>
                        <RowDefinition Height="Auto"/>
                        <RowDefinition Height="Auto"/>
                        <RowDefinition Height="*"/>
                    </Grid.RowDefinitions>

                    <!-- Judul panel -->
                    <Grid Grid.Row="0" Margin="0,0,0,10">
                        <StackPanel Orientation="Horizontal">
                            <StackPanel Orientation="Horizontal" VerticalAlignment="Center" Margin="0,0,8,0">
                                <Rectangle Width="4" Height="8" RadiusX="1" RadiusY="1"
                                           Fill="{StaticResource InfoTitleBrush}" VerticalAlignment="Bottom"/>
                                <Rectangle Width="4" Height="13" RadiusX="1" RadiusY="1" Margin="2,0,0,0"
                                           Fill="{StaticResource InfoTitleBrush}" VerticalAlignment="Bottom"/>
                                <Rectangle Width="4" Height="18" RadiusX="1" RadiusY="1" Margin="2,0,0,0"
                                           Fill="{StaticResource InfoTitleBrush}" VerticalAlignment="Bottom"/>
                            </StackPanel>
                            <TextBlock Text="INFORMASI"
                                       Style="{StaticResource SectionTitle}"
                                       Foreground="{StaticResource InfoTitleBrush}"/>
                        </StackPanel>
                        <TextBlock Text="{Binding InfoCaption}"
                                   HorizontalAlignment="Right" VerticalAlignment="Center"
                                   MaxWidth="320" TextTrimming="CharacterEllipsis"
                                   FontSize="11" Foreground="{StaticResource TextSecondaryBrush}"/>
                    </Grid>

                    <!-- Total Mahasiswa -->
                    <Border Grid.Row="1" Style="{StaticResource StatTile}"
                            Background="{StaticResource TileBlueBrush}" Margin="0,0,0,8">
                        <StackPanel Orientation="Horizontal">
                            <TextBlock Text="&#xE716;" Style="{StaticResource StatIcon}"
                                       FontSize="26" Foreground="{StaticResource TileBlueIconBrush}"/>
                            <StackPanel VerticalAlignment="Center">
                                <TextBlock Text="Total Mahasiswa" Style="{StaticResource StatLabel}"/>
                                <TextBlock Text="{Binding TotalStudents}"
                                           Style="{StaticResource StatValue}" FontSize="26"/>
                            </StackPanel>
                        </StackPanel>
                    </Border>

                    <!-- Statistik Jurusan dan Gender -->
                    <Grid Grid.Row="2">
                        <Grid.ColumnDefinitions>
                            <ColumnDefinition Width="*"/>
                            <ColumnDefinition Width="8"/>
                            <ColumnDefinition Width="*"/>
                        </Grid.ColumnDefinitions>
                        <Grid.RowDefinitions>
                            <RowDefinition Height="Auto"/>
                            <RowDefinition Height="8"/>
                            <RowDefinition Height="Auto"/>
                        </Grid.RowDefinitions>

                        <!-- Informatika -->
                        <Border Grid.Row="0" Grid.Column="0" Style="{StaticResource StatTile}"
                                Background="{StaticResource TileGreenBrush}">
                            <StackPanel Orientation="Horizontal">
                                <TextBlock Text="&#xE7F4;" Style="{StaticResource StatIcon}"
                                           Foreground="{StaticResource TileGreenIconBrush}"/>
                                <StackPanel VerticalAlignment="Center">
                                    <TextBlock Text="Informatika" Style="{StaticResource StatLabel}"/>
                                    <TextBlock Text="{Binding TotalInformatika}" Style="{StaticResource StatValue}"/>
                                </StackPanel>
                            </StackPanel>
                        </Border>

                        <!-- Sistem Informasi (ikon database digambar dengan Path) -->
                        <Border Grid.Row="0" Grid.Column="2" Style="{StaticResource StatTile}"
                                Background="{StaticResource TilePurpleBrush}">
                            <StackPanel Orientation="Horizontal">
                                <Viewbox Width="24" Height="24" Margin="0,0,14,0" VerticalAlignment="Center">
                                    <Canvas Width="24" Height="24">
                                        <Path Fill="{StaticResource TilePurpleIconBrush}"
                                              Data="M12,2 C7.58,2 4,3.34 4,5 L4,19 C4,20.66 7.58,22 12,22 C16.42,22 20,20.66 20,19 L20,5 C20,3.34 16.42,2 12,2 Z"/>
                                        <Path Stroke="White" StrokeThickness="1.3"
                                              Data="M4,5 C4,6.66 7.58,8 12,8 C16.42,8 20,6.66 20,5 M4,10.7 C4,12.36 7.58,13.7 12,13.7 C16.42,13.7 20,12.36 20,10.7 M4,16.4 C4,18.06 7.58,19.4 12,19.4 C16.42,19.4 20,18.06 20,16.4"/>
                                    </Canvas>
                                </Viewbox>
                                <StackPanel VerticalAlignment="Center">
                                    <TextBlock Text="Sistem Informasi" Style="{StaticResource StatLabel}"/>
                                    <TextBlock Text="{Binding TotalSistemInformasi}" Style="{StaticResource StatValue}"/>
                                </StackPanel>
                            </StackPanel>
                        </Border>

                        <!-- Laki-laki -->
                        <Border Grid.Row="2" Grid.Column="0" Style="{StaticResource StatTile}"
                                Background="{StaticResource TileOrangeBrush}">
                            <StackPanel Orientation="Horizontal">
                                <TextBlock Text="&#xE77B;" Style="{StaticResource StatIcon}"
                                           Foreground="{StaticResource TileOrangeIconBrush}"/>
                                <StackPanel VerticalAlignment="Center">
                                    <TextBlock Text="Laki-laki" Style="{StaticResource StatLabel}"/>
                                    <TextBlock Text="{Binding TotalLakiLaki}" Style="{StaticResource StatValue}"/>
                                </StackPanel>
                            </StackPanel>
                        </Border>

                        <!-- Perempuan -->
                        <Border Grid.Row="2" Grid.Column="2" Style="{StaticResource StatTile}"
                                Background="{StaticResource TilePinkBrush}">
                            <StackPanel Orientation="Horizontal">
                                <TextBlock Text="&#xE77B;" Style="{StaticResource StatIcon}"
                                           Foreground="{StaticResource TilePinkIconBrush}"/>
                                <StackPanel VerticalAlignment="Center">
                                    <TextBlock Text="Perempuan" Style="{StaticResource StatLabel}"/>
                                    <TextBlock Text="{Binding TotalPerempuan}" Style="{StaticResource StatValue}"/>
                                </StackPanel>
                            </StackPanel>
                        </Border>
                    </Grid>
                </Grid>
            </Border>
        </Grid>

        <!-- ================================================== -->
        <!--  3. SEARCH                                          -->
        <!-- ================================================== -->
        <Border Grid.Row="2" Style="{StaticResource Card}" Padding="12,8" Margin="0,0,0,10">
            <Grid>
                <Grid.ColumnDefinitions>
                    <ColumnDefinition Width="Auto"/>
                    <ColumnDefinition Width="*"/>
                    <ColumnDefinition Width="Auto"/>
                    <ColumnDefinition Width="Auto"/>
                </Grid.ColumnDefinitions>

                <StackPanel Orientation="Horizontal" VerticalAlignment="Center" Margin="4,0,14,0">
                    <TextBlock Text="&#xE721;" Style="{StaticResource IconText}"
                               FontSize="15" Foreground="{StaticResource TextSecondaryBrush}" Margin="0,0,8,0"/>
                    <TextBlock Text="Search" FontWeight="SemiBold" VerticalAlignment="Center"
                               Foreground="{StaticResource TextPrimaryBrush}"/>
                </StackPanel>

                <TextBox Grid.Column="1"
                         Style="{StaticResource InputTextBox}"
                         Margin="0"
                         Tag="Cari berdasarkan NIM, Nama, atau Jurusan..."
                         Text="{Binding SearchText, UpdateSourceTrigger=PropertyChanged}">
                    <TextBox.InputBindings>
                        <KeyBinding Key="Enter" Command="{Binding SearchCommand}"/>
                    </TextBox.InputBindings>
                </TextBox>

                <Button Grid.Column="2"
                        Style="{StaticResource PrimaryButton}"
                        Command="{Binding SearchCommand}"
                        MinWidth="100"
                        Margin="10,0,0,0">
                    <StackPanel Orientation="Horizontal">
                        <TextBlock Text="&#xE721;" Style="{StaticResource IconText}" Margin="0,0,8,0"/>
                        <TextBlock Text="Cari"/>
                    </StackPanel>
                </Button>

                <Button Grid.Column="3"
                        Style="{StaticResource SecondaryButton}"
                        Command="{Binding ClearSearchCommand}"
                        MinWidth="100"
                        Margin="10,0,0,0">
                    <StackPanel Orientation="Horizontal">
                        <TextBlock Text="&#xE72C;" Style="{StaticResource IconText}" Margin="0,0,8,0"/>
                        <TextBlock Text="Reset"/>
                    </StackPanel>
                </Button>
            </Grid>
        </Border>

        <!-- ================================================== -->
        <!--  4. DATAGRID                                        -->
        <!-- ================================================== -->
        <Border Grid.Row="3" Style="{StaticResource Card}" Padding="6">
            <DataGrid ItemsSource="{Binding Students}"
                      SelectedItem="{Binding SelectedStudent, Mode=TwoWay}"
                      AutoGenerateColumns="False"
                      IsReadOnly="True"
                      AlternationCount="{Binding Items.Count, RelativeSource={RelativeSource Self}}">
                <DataGrid.Columns>
                    <DataGridTextColumn Header="No"
                                        Width="50"
                                        CanUserSort="False"
                                        HeaderStyle="{StaticResource CenterHeader}"
                                        ElementStyle="{StaticResource CenterCellText}"
                                        Binding="{Binding (ItemsControl.AlternationIndex),
                                                  RelativeSource={RelativeSource AncestorType=DataGridRow},
                                                  Converter={StaticResource RowNumberConverter},
                                                  Mode=OneWay}"/>
                    <DataGridTextColumn Header="NIM" Width="110" Binding="{Binding NIM}"/>
                    <DataGridTextColumn Header="Nama" Width="1.4*" Binding="{Binding Nama}"/>
                    <DataGridTextColumn Header="Jurusan" Width="*" Binding="{Binding Jurusan}"/>
                    <DataGridTextColumn Header="Gender" Width="110" Binding="{Binding Gender}"/>
                    <DataGridTextColumn Header="Email" Width="1.4*" Binding="{Binding Email}"/>
                    <DataGridTemplateColumn Header="Aksi" Width="110" HeaderStyle="{StaticResource CenterHeader}">
                        <DataGridTemplateColumn.CellTemplate>
                            <DataTemplate>
                                <StackPanel Orientation="Horizontal" HorizontalAlignment="Center">
                                    <Button Style="{StaticResource IconButtonPrimary}"
                                            Content="&#xE70F;"
                                            ToolTip="Edit data"
                                            Command="{Binding DataContext.EditCommand,
                                                      RelativeSource={RelativeSource AncestorType=DataGrid}}"
                                            CommandParameter="{Binding}"/>
                                    <Button Style="{StaticResource IconButtonDanger}"
                                            Content="&#xE74D;"
                                            ToolTip="Hapus data"
                                            Margin="6,0,0,0"
                                            Command="{Binding DataContext.DeleteCommand,
                                                      RelativeSource={RelativeSource AncestorType=DataGrid}}"
                                            CommandParameter="{Binding}"/>
                                </StackPanel>
                            </DataTemplate>
                        </DataGridTemplateColumn.CellTemplate>
                    </DataGridTemplateColumn>
                </DataGrid.Columns>
            </DataGrid>
        </Border>

        <!-- ================================================== -->
        <!--  5. STATUS BAR                                      -->
        <!-- ================================================== -->
        <Grid Grid.Row="4" Margin="4,6,4,0">
            <Grid.ColumnDefinitions>
                <ColumnDefinition Width="*"/>
                <ColumnDefinition Width="Auto"/>
            </Grid.ColumnDefinitions>

            <DockPanel Grid.Column="0">
                <Ellipse DockPanel.Dock="Left" Width="8" Height="8" Margin="0,0,8,0"
                         Fill="{StaticResource PrimaryBrush}" VerticalAlignment="Center"/>
                <TextBlock Text="{Binding StatusMessage}"
                           FontSize="12" Foreground="{StaticResource TextSecondaryBrush}"
                           VerticalAlignment="Center" TextTrimming="CharacterEllipsis"/>
            </DockPanel>

            <TextBlock Grid.Column="1" Margin="16,0,0,0" VerticalAlignment="Center"
                       FontSize="12" Foreground="{StaticResource TextSecondaryBrush}">
                <Run Text="Data ditampilkan:"/>
                <Run Text="{Binding Students.Count, Mode=OneWay}" FontWeight="SemiBold"/>
            </TextBlock>
        </Grid>
    </Grid>
</Window>
```

</details>

#### Langkah 14 — DataContext: `MainWindow.xaml.cs`

📄 `StudentManager/MainWindow.xaml.cs`

```csharp
using System.Windows;
using StudentManager.ViewModels;

namespace StudentManager;

public partial class MainWindow : Window
{
    public MainWindow()
    {
        InitializeComponent();

        // DataContext menentukan sumber data untuk semua Binding di MainWindow.xaml
        DataContext = new StudentViewModel();
    }
}
```

`DataContext` menentukan sumber data untuk semua binding. Setelah `DataContext = new StudentViewModel()`, ekspresi `{Binding Students}`, `{Binding TotalStudents}`, dan binding lainnya diambil dari objek `StudentViewModel`. Inilah satu-satunya kode di *code-behind*; semua logika berada di ViewModel.

#### Langkah 15 — `App.xaml.cs`

📄 `StudentManager/App.xaml.cs`

```csharp
using System.Windows;
using System.Windows.Threading;

namespace StudentManager;

public partial class App : Application
{
    protected override void OnStartup(StartupEventArgs e)
    {
        base.OnStartup(e);
        DispatcherUnhandledException += OnDispatcherUnhandledException;
    }

    // Jaring pengaman: error yang tidak tertangani ditampilkan sebagai pesan
    // sehingga aplikasi tidak langsung tertutup.
    private void OnDispatcherUnhandledException(object sender, DispatcherUnhandledExceptionEventArgs e)
    {
        MessageBox.Show(
            "Terjadi kesalahan yang tidak terduga:\n\n" + e.Exception.Message,
            "Student Management System",
            MessageBoxButton.OK,
            MessageBoxImage.Error);

        e.Handled = true;

        // Jika error terjadi sebelum jendela utama tampil, aplikasi ditutup
        // agar proses tidak berjalan tanpa jendela.
        if (MainWindow is null || !MainWindow.IsVisible)
            Shutdown(-1);
    }
}
```

`DispatcherUnhandledException` berfungsi sebagai jaring pengaman. Error yang tidak tertangani ditampilkan sebagai pesan, sehingga aplikasi tidak langsung tertutup.

#### Langkah 16 — Menjalankan Aplikasi

Tekan **Ctrl+Shift+B** untuk *build*, lalu **F5** untuk menjalankan aplikasi. Saat dibuka, ViewModel memeriksa database (`EnsureDatabaseCreated`), memuat data (`LoadData`), dan menampilkan pesan *"Terhubung ke database StudentDB. 4 data mahasiswa dimuat."* di status bar.

> 📷 **Gambar 6 — Tampilan awal aplikasi (READ).** Sisipkan screenshot: 4 data contoh tampil di DataGrid; panel INFORMASI menampilkan Total 4, Informatika 2, Sistem Informasi 2, Laki-laki 2, Perempuan 2.

---

### 9. Memahami Data Binding

| Jenis Binding | Alur Data | Contoh pada Aplikasi |
|---|---|---|
| Input (*TwoWay*) | TextBox → Binding → `CurrentStudent.Nama` | `Text="{Binding CurrentStudent.Nama, UpdateSourceTrigger=PropertyChanged}"` |
| Output (*OneWay*) | ViewModel → Binding → TextBlock | `Text="{Binding TotalStudents}"` |
| Koleksi | `ObservableCollection<Student>` → `ItemsSource` → DataGrid | `ItemsSource="{Binding Students}"` |
| Seleksi | `DataGrid.SelectedItem` → `SelectedStudent` → salinan → `CurrentStudent` → Form | `SelectedItem="{Binding SelectedStudent, Mode=TwoWay}"` |
| Command | Button → Command → ViewModel | `Command="{Binding SaveCommand}"` |
| Command + parameter | Tombol pada baris → `CommandParameter` (objek `Student`) | `CommandParameter="{Binding}"` |
| Converter | Nilai model → Converter → kontrol | `Converter={StaticResource EmptyStringToNullConverter}` |
| DataTrigger | Properti ViewModel → Style | Badge berubah warna saat `IsEditMode = True` |

Siklus pembaruan panel INFORMASI setelah data berubah:

```text
INSERT / UPDATE / DELETE / SEARCH
        ↓
LoadData() atau Search()  →  isi ObservableCollection Students berubah
        ↓
RefreshStatistics()
        ↓
PropertyChanged("TotalStudents", "TotalInformatika", ...)
        ↓
WPF Binding
        ↓
Angka pada panel INFORMASI berubah
```

---

### 10. Alur CRUD dan Pencarian

```text
CREATE
User mengisi form → Binding → CurrentStudent → klik Simpan → SaveCommand → Save()
→ Validate() → Repository.IsNimExists() → Repository.Insert() → INSERT → SQL Server
→ LoadData() → Students → DataGrid & statistik → Reset()

READ
SQL Server → SELECT → Repository.GetAll() → List<Student>
→ ObservableCollection<Student> Students → ItemsSource → DataGrid

UPDATE
Klik baris / ikon Edit → SelectedStudent → Clone() → CurrentStudent → Form
→ ubah data → klik Update → SaveCommand → Repository.Update() → UPDATE → SQL Server
→ LoadData() → DataGrid & statistik

DELETE
Tombol Hapus (form) / ikon Hapus (baris) → DeleteCommand (+ CommandParameter)
→ IDialogService.Confirm() → Repository.Delete() → DELETE → SQL Server
→ LoadData() → DataGrid & statistik

SEARCH
SearchText → Enter / tombol Cari → SearchCommand → Search()
→ Repository.Search() → SELECT ... WHERE NIM/Nama/Jurusan LIKE @Keyword
→ Students → DataGrid & statistik hasil pencarian
```

---

### 11. Pengujian Aplikasi

Pengujian dijalankan di Windows dengan Visual Studio 2022 dan SQL Server LocalDB. Skenario dilakukan **berurutan**, dimulai dari kondisi awal berisi 4 data contoh, sehingga angka statistik pada setiap langkah dapat diprediksi.

#### A. Skenario Utama

| No | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Bukti |
|---|---|---|---|---|
| 1 | READ | Jalankan aplikasi | 4 data contoh tampil (Id terbaru di atas). Statistik: Total 4, Informatika 2, Sistem Informasi 2, Laki-laki 2, Perempuan 2 | Gambar 6 |
| 2 | CREATE | Isi NIM `23005`, Nama `Dewi Lestari`, Jurusan Sistem Informasi, Gender Perempuan, Email `dewi@gmail.com`, lalu klik Simpan | Data tampil di baris teratas, form kosong kembali, status *"Data Dewi Lestari berhasil disimpan."* Statistik: Total 5, Informatika 2, Sistem Informasi 3, Laki-laki 2, Perempuan 3 | Gambar 7, 8 |
| 3 | UPDATE | Klik baris Andi Wijaya (atau ikon Edit), ubah Jurusan menjadi Sistem Informasi, lalu klik Update | Badge berubah menjadi *Mode Edit*, tombol menjadi *Update*. Setelah disimpan Jurusan Andi berubah. Statistik: Informatika 1, Sistem Informasi 4 | Gambar 9, 10 |
| 4 | DELETE | Klik ikon Hapus pada baris Dewi Lestari, lalu pilih Yes | Muncul dialog konfirmasi, kemudian data terhapus. Statistik: Total 4, Informatika 1, Sistem Informasi 3, Laki-laki 2, Perempuan 2 | Gambar 11, 12 |
| 5 | SEARCH (Jurusan) | Ketik `Sistem Informasi`, lalu tekan Enter / Cari | Tampil 3 data (Rina Sari, Andi Wijaya, Siti Aminah). Statistik mengikuti hasil: Total 3, Informatika 0, Sistem Informasi 3, Laki-laki 1, Perempuan 2 | Gambar 13 |
| 6 | SEARCH (NIM, Nama) | Cari `23002`, lalu cari `Budi` | Masing-masing tampil 1 data: Siti Aminah dan Budi Santoso | — |
| 7 | Reset pencarian | Klik Reset di bar pencarian | Kolom pencarian kosong dan seluruh data tampil kembali | — |
| 8 | STATISTICS | Amati panel INFORMASI pada skenario 1–7 | Angka statistik berubah otomatis setelah setiap CRUD dan pencarian | Gambar 6–13 |
| 9 | RESET form | Klik sebuah baris (form terisi, Mode Edit), lalu klik Reset | Form kosong, ComboBox kembali menampilkan *placeholder*, badge *Mode Tambah*, tombol Hapus nonaktif | Gambar 14 |

> 📷 **Gambar 7 — CREATE — sebelum disimpan.** Sisipkan screenshot: Form terisi data Dewi Lestari (23005, Sistem Informasi, Perempuan, dewi@gmail.com).

> 📷 **Gambar 8 — CREATE — setelah disimpan.** Sisipkan screenshot: Dewi Lestari tampil di baris teratas; statistik Total 5, Sistem Informasi 3, Perempuan 3; status bar menampilkan pesan berhasil.

> 📷 **Gambar 9 — UPDATE — Mode Edit.** Sisipkan screenshot: Baris Andi Wijaya dipilih, badge Mode Edit, tombol berubah menjadi Update, Jurusan diubah ke Sistem Informasi.

> 📷 **Gambar 10 — UPDATE — setelah diperbarui.** Sisipkan screenshot: Jurusan Andi Wijaya menjadi Sistem Informasi; statistik Informatika 1 dan Sistem Informasi 4.

> 📷 **Gambar 11 — DELETE — konfirmasi.** Sisipkan screenshot: Dialog Konfirmasi Hapus untuk data Dewi Lestari (NIM 23005).

> 📷 **Gambar 12 — DELETE — setelah dihapus.** Sisipkan screenshot: Data Dewi Lestari hilang dari tabel; statistik kembali Total 4.

> 📷 **Gambar 13 — SEARCH.** Sisipkan screenshot: Hasil pencarian "Sistem Informasi" (3 data) dan statistik hasil pencarian.

> 📷 **Gambar 14 — RESET form.** Sisipkan screenshot: Form kosong kembali dengan placeholder dan badge Mode Tambah.

#### B. Validasi dan Kasus Tepi

| No | Skenario | Input / Langkah | Hasil yang Diharapkan | Bukti |
|---|---|---|---|---|
| 10 | Form kosong | Klik Simpan tanpa mengisi form | Dialog *Validasi Data* berisi 4 poin: NIM wajib diisi, Nama wajib diisi, Jurusan wajib dipilih, Gender wajib dipilih | Gambar 15 |
| 11 | NIM bukan angka | NIM `23A01` | Pesan *"NIM hanya boleh berisi angka."* | — |
| 12 | NIM duplikat | NIM `23001` (milik Budi Santoso) | Pesan *"NIM 23001 sudah terdaftar. Gunakan NIM lain."* | Gambar 16 |
| 13 | Format email salah | Email `dewi@` | Pesan *"Format email tidak valid (contoh: nama@gmail.com)."* | — |
| 14 | Batal hapus | Klik Hapus, lalu pilih No | Data tidak terhapus, status *"Penghapusan data dibatalkan."* | — |
| 15 | Email kosong | Simpan data tanpa email | Data tersimpan, kolom Email kosong (NULL di database) | — |
| 16 | Update tanpa mengubah NIM | Edit data lalu Update dengan NIM yang sama | Data tersimpan; NIM milik sendiri tidak dianggap duplikat | — |

> 📷 **Gambar 15 — Validasi form kosong.** Sisipkan screenshot: Dialog Validasi Data berisi daftar input yang belum diisi.

> 📷 **Gambar 16 — Validasi NIM duplikat.** Sisipkan screenshot: Pesan NIM 23001 sudah terdaftar.

#### C. Verifikasi Kompilasi dan Logika (macOS)

Sebelum dijalankan di Windows, project dikompilasi di macOS menggunakan opsi `EnableWindowsTargeting`:

```bash
dotnet build StudentManager.sln -p:EnableWindowsTargeting=true
```

```text
  Determining projects to restore...
  Restored StudentManager/StudentManager.csproj (in 460 ms).
  StudentManager -> StudentManager/bin/Debug/net8.0-windows/StudentManager.dll
Build succeeded.
    0 Warning(s)
    0 Error(s)
Time Elapsed 00:00:05.83
```

Logika ViewModel juga diuji dengan program konsol `Tests/StudentManager.LogicTests`. Program ini mengompilasi kode **asli** (`StudentViewModel`, `RelayCommand`, `Student`, dan Converter), lalu mengganti repository dan dialog dengan versi palsu (*in-memory*), sehingga bisa dijalankan tanpa Windows dan tanpa SQL Server. Urutan skenarionya sama dengan tabel pengujian di atas, ditambah pengujian notifikasi `PropertyChanged` dan penanganan error database.

```bash
dotnet run --project Tests/StudentManager.LogicTests
```

<details>
<summary><b>Output lengkap program uji (74 skenario)</b> (klik untuk membuka)</summary>

`Output terminal`

```text
T1  READ
  [PASS] 4 data contoh tampil di DataGrid
  [PASS] urutan ORDER BY Id DESC
  [PASS] statistik awal → Total=4, Inf=2, SI=2, L=2, P=2
  [PASS] form dalam Mode Tambah
  [PASS] status → Terhubung ke database StudentDB. 4 data mahasiswa dimuat.
  [PASS] tidak ada dialog error
        TodayText = Minggu, 04 Oktober 2026
T2  VALIDASI
  [PASS] form kosong → 4 pesan validasi sekaligus
        dialog: Periksa kembali input berikut: |  | • NIM wajib diisi. | • Nama wajib diisi. | • Jurusan wajib dipilih. | • Gender wajib dipilih.
  [PASS] NIM berisi huruf ditolak
  [PASS] format email salah ditolak
  [PASS] NIM duplikat ditolak
  [PASS] tidak ada INSERT, data tetap 4
T3  CREATE
  [PASS] Repository.Insert dipanggil
  [PASS] data baru muncul di baris teratas
  [PASS] statistik → Total=5, Inf=2, SI=3, L=2, P=3
  [PASS] form kembali kosong
  [PASS] status → Data Dewi Lestari berhasil disimpan.
T4  UPDATE
  [PASS] mode edit aktif (tombol jadi Update)
  [PASS] form berisi SALINAN data terpilih
  [PASS] DataGrid belum berubah sebelum Update ditekan
  [PASS] Repository.Update dipanggil (bukan Insert)
  [PASS] jurusan Andi tersimpan
  [PASS] statistik → Total=5, Inf=1, SI=4, L=2, P=3
  [PASS] status → Data Andi Wijaya berhasil diperbarui.
  [PASS] form & seleksi direset
  [PASS] update data sendiri (NIM sama) tidak dianggap duplikat
  [PASS] update ke NIM milik mahasiswa lain ditolak
  [PASS] seleksi DataGrid hilang tidak menghapus isi form
  [PASS] perubahan yang tidak disimpan tidak masuk ke data
T5  DELETE
  [PASS] dialog konfirmasi menampilkan NIM
  [PASS] pilih No → data tidak terhapus
  [PASS] pilih Yes → data terhapus
  [PASS] statistik → Total=4, Inf=1, SI=3, L=2, P=2
  [PASS] status → Data Dewi Lestari berhasil dihapus.
  [PASS] hapus tanpa memilih data → peringatan
T6  SEARCH
  [PASS] cari Nama 'Budi' → 1 data
  [PASS] statistik mengikuti hasil → Total=1, Inf=1, SI=0, L=1, P=0
  [PASS] cari NIM '23002' → Siti Aminah
  [PASS] cari Jurusan 'Sistem Informasi' → 3 data
  [PASS] statistik → Total=3, Inf=0, SI=3, L=1, P=2
  [PASS] status → Ditemukan 3 data untuk kata kunci "Sistem Informasi".
  [PASS] kata kunci tidak ditemukan → tabel kosong
  [PASS] huruf kecil tetap ditemukan (meniru collation CI SQL Server)
  [PASS] kata kunci kosong → semua data
  [PASS] tombol Reset pencarian → semua data
T7  RESET
  [PASS] baris dipilih → mode edit
  [PASS] form kembali ke kondisi awal
  [PASS] status → Form dikosongkan. Silakan input data mahasiswa baru.
T8  EDIT & HAPUS DARI FORM
  [PASS] tombol Edit memuat salinan data ke form
  [PASS] tombol Hapus di form menghapus data yang sedang diedit
  [PASS] statistik → Total=3, Inf=1, SI=2, L=2, P=1
T9  TRIM & EMAIL OPSIONAL
  [PASS] spasi dipangkas & email boleh kosong
T10 NOTIFIKASI PropertyChanged
  [PASS] PropertyChanged("TotalStudents") dikirim
  [PASS] PropertyChanged("TotalInformatika") dikirim
  [PASS] PropertyChanged("TotalSistemInformasi") dikirim
  [PASS] PropertyChanged("TotalLakiLaki") dikirim
  [PASS] PropertyChanged("TotalPerempuan") dikirim
  [PASS] PropertyChanged("CurrentStudent") dikirim
  [PASS] PropertyChanged("IsEditMode") dikirim
  [PASS] PropertyChanged("FormModeText") dikirim
  [PASS] PropertyChanged("SaveButtonText") dikirim
  [PASS] PropertyChanged("StatusMessage") dikirim
  [PASS] PropertyChanged("InfoCaption") dikirim
  [PASS] PropertyChanged("SearchText") dikirim
  [PASS] PropertyChanged("SelectedStudent") dikirim
T11 ERROR DATABASE
  [PASS] ViewModel tidak crash saat database tidak tersedia
  [PASS] dialog error koneksi ditampilkan
  [PASS] error saat simpan ditangani
  [PASS] error saat cari ditangani
T12 RELAYCOMMAND & CONVERTER
  [PASS] RelayCommand(Action) berjalan, CanExecute default true
  [PASS] CommandParameter & predicate CanExecute
  [PASS] event CanExecuteChanged
  [PASS] RowNumberConverter: indeks 0 → No 1
  [PASS] EmptyStringToNullConverter: "" → null (ComboBox kosong)
  [PASS] EmptyStringToNullConverter: null → ""

HASIL: 74 lulus, 0 gagal
```

</details>

Hasil akhirnya **74 skenario lulus, 0 gagal**. Logika CRUD, validasi, pencarian, statistik, reset, dan penanganan error berjalan sesuai harapan.

---

### 12. Pembahasan

#### a. Windows Forms vs WPF + MVVM

| Windows Forms | WPF + MVVM |
|---|---|
| Button → event `Click` → *code-behind* | Button → `Command` → ViewModel |
| Designer + *code-behind* | XAML + Data Binding |
| UI dan logika cenderung bercampur | View dipisahkan dari logika aplikasi |
| Mengisi kontrol secara manual (`txtNama.Text = ...`) | Kontrol ter-*update* otomatis melalui binding |
| *Event-driven* | Binding + Command + MVVM |

Pada aplikasi ini, `MainWindow.xaml.cs` hanya berisi satu baris logika, yaitu `DataContext = new StudentViewModel();`. Seluruh logika berada di ViewModel, sehingga dapat diuji tanpa membuka jendela aplikasi.

#### b. Pemisahan `SelectedStudent` dan `CurrentStudent`

Pada kode modul, form dan DataGrid sama-sama di-*bind* ke `SelectedStudent`. Pendekatan tersebut menimbulkan beberapa masalah:

1. Saat aplikasi pertama kali dibuka, `SelectedStudent` masih bernilai `null` karena konstruktor tidak mengisinya. Akibatnya, input yang diketik di form tidak tersimpan ke objek mana pun dan tombol Simpan tidak melakukan apa-apa (`if (SelectedStudent == null) return;`) sampai tombol Reset ditekan.
2. Form mengedit objek yang sama dengan baris DataGrid, sehingga data di tabel ikut berubah saat pengguna mengetik, walaupun belum menekan Simpan.
3. Ketika isi DataGrid dimuat ulang (misalnya saat pencarian), seleksi DataGrid hilang dan binding *TwoWay* mengirim `null` ke `SelectedStudent`. Data yang sedang diedit di form pun ikut hilang.

Solusinya, `SelectedStudent` hanya mewakili baris yang dipilih di DataGrid, sedangkan form memakai `CurrentStudent`. `CurrentStudent` sudah berisi objek kosong sejak awal dan berisi **salinan** (`Clone()`) ketika sebuah baris dipilih. Perubahan baru masuk ke database dan tabel setelah tombol Update ditekan. Program uji (skenario T4) membuktikan bahwa data tabel tidak berubah sebelum disimpan, dan isi form tidak hilang ketika seleksi DataGrid kosong.

#### c. ComboBox dengan `SelectedItem`

Modul mengisi ComboBox dengan `<ComboBoxItem>`, lalu mem-*bind* `SelectedItem` ke properti bertipe `string`. Akibatnya, nilai yang dikirim ke model adalah objek `ComboBoxItem`. Teks yang tersimpan bisa menjadi *"System.Windows.Controls.ComboBoxItem: Informatika"*, dan saat mode edit pilihan tidak tampil karena `string` tidak sama dengan `ComboBoxItem`.

Pada aplikasi ini, pilihan disediakan sebagai daftar `string` dari ViewModel (`JurusanOptions`, `GenderOptions`), sehingga `SelectedItem` bertipe `string` sama dengan propertinya. Selain itu, WPF hanya mengosongkan pilihan ComboBox bila `SelectedItem` bernilai `null`. Nilai string kosong tidak ada di daftar pilihan sehingga ditolak, dan pilihan sebelumnya tetap tampil. Karena itu dipakai `EmptyStringToNullConverter` yang mengubah `""` menjadi `null`, sehingga pilihan ComboBox ikut kosong ketika form di-*reset*.

#### d. Peran `ObservableCollection` dan `INotifyPropertyChanged`

- `ObservableCollection<Student>` mengirim notifikasi `CollectionChanged` setiap kali item ditambah, dihapus, atau dikosongkan, sehingga DataGrid langsung ter-*update*.
- `INotifyPropertyChanged` mengirim notifikasi `PropertyChanged` ketika nilai properti berubah. Properti statistik bersifat *read-only* (hanya menghitung), sehingga perlu `RefreshStatistics()` untuk memberi tahu WPF bahwa nilainya sudah berubah.

Sesuai materi Panel Informasi, `TextBlock` statistik tidak membaca SQL Server secara langsung. Statistik dihitung dari koleksi `Students` menggunakan LINQ `Count()`. Konsekuensinya, setelah pencarian panel INFORMASI menampilkan statistik **hasil pencarian**. Hal ini ditandai dengan keterangan *"Hasil pencarian ..."* di samping judul panel. Untuk data berukuran besar, statistik sebaiknya dihitung di database menggunakan `SELECT COUNT(*) ... GROUP BY`.

#### e. Keamanan dan Ketahanan Aplikasi

- **SQL Injection** dicegah dengan parameter (`@NIM`, `@Keyword`, dan seterusnya).
- **Validasi** dilakukan di ViewModel sebelum data dikirim ke database. `MaxLength` pada `TextBox` disesuaikan dengan panjang kolom tabel (NIM 20, Nama 100, Email 100).
- **NIM unik** diperiksa melalui `IsNimExists()` karena tabel pada modul tidak memiliki *constraint* `UNIQUE`.
- **Error database** ditangkap dengan `try-catch` di ViewModel dan ditampilkan melalui `IDialogService`. Penggunaan interface membuat ViewModel tetap bebas dari kelas View, sehingga mudah diuji.

#### f. Nomor Urut pada DataGrid

Tabel `Students` tidak memiliki kolom nomor urut, dan nilai `Id` tidak berurutan setelah ada data yang dihapus. Kolom **No** dibuat dengan `AlternationCount` sebesar jumlah item, sehingga setiap baris mendapatkan `AlternationIndex` unik (0, 1, 2, ...), lalu ditambah 1 oleh `RowNumberConverter`. Kolom ini diberi `CanUserSort="False"` karena bukan properti dari `Student`.

#### g. Kendala Selama Pengerjaan

1. **WPF dan LocalDB tidak berjalan di macOS.** Kode ditulis di macOS dan dikompilasi dengan `-p:EnableWindowsTargeting=true` untuk memastikan C# dan XAML bebas error. Logika diuji dengan program konsol lintas platform, sedangkan aplikasi dijalankan di Windows.
2. **Error `MC3000: An XML comment cannot contain '--'`.** Komentar XAML seperti `<!-- ---- Form ---- -->` tidak valid karena XML melarang dua tanda minus berurutan di dalam komentar. Pemisah diganti menjadi `=====`. Error serupa (`MSB4025`) juga muncul pada file `.csproj` program uji karena komentarnya memuat teks `--project`.
3. **Ruang DataGrid terlalu sempit.** Setelah tinggi setiap bagian dihitung, jendela 750 piksel (sesuai modul) hanya menyisakan ruang sekitar 3 baris data. Jarak antar-elemen kemudian dirapatkan (tinggi input 32, *padding* kartu 14, tinggi baris 36), sehingga DataGrid memuat sekitar 5 baris tanpa mengubah ukuran jendela.

#### h. Peluang Pengembangan

Sesuai bagian Pengembangan Lanjutan pada modul, aplikasi masih dapat dikembangkan dengan:

1. Validasi per-field menggunakan `INotifyDataErrorInfo` (border merah dan pesan di bawah input).
2. `async/await` untuk akses database agar UI tidak tertahan saat query berjalan.
3. Entity Framework Core sebagai pengganti ADO.NET.
4. *Dependency Injection container* dan *Service Layer*.
5. *Sorting* lanjutan dan *pagination* pada DataGrid.
6. *Dashboard chart* untuk statistik jurusan dan gender.
7. REST API dan *Clean Architecture*.

---

### 13. Kesimpulan

1. Aplikasi **Student Management System** berhasil dibangun menggunakan WPF .NET 8 dengan pola **MVVM**. View (XAML) hanya menangani tampilan, ViewModel menangani state, command, pencarian, statistik, dan validasi, Model merepresentasikan data, dan Repository menangani akses database.
2. **Data Binding** menghubungkan form, panel INFORMASI, dan DataGrid dengan ViewModel tanpa kode manual di *code-behind*. Dengan `ObservableCollection` dan `INotifyPropertyChanged`, tabel dan statistik ter-*update* otomatis setelah setiap operasi CRUD maupun pencarian.
3. **Command** (`RelayCommand` / `ICommand`) menggantikan event `Click`, termasuk aksi Edit dan Hapus per baris yang mengirim data melalui `CommandParameter`.
4. **ADO.NET** dengan `Microsoft.Data.SqlClient` dipakai untuk operasi CRUD dan pencarian ke **SQL Server LocalDB** menggunakan query berparameter yang aman dari SQL Injection.
5. **Rich UI** dibangun dengan Style, ControlTemplate, Trigger, dan DataTrigger: header *gradient*, kartu statistik berwarna, input dengan *placeholder*, serta DataGrid bergaya modern.
6. Pengembangan tambahan berupa validasi, konfirmasi hapus, pemisahan data form dari data tabel, inisialisasi database otomatis, dan penanganan error membuat aplikasi lebih andal. Hal ini dibuktikan oleh 74 skenario uji logika yang seluruhnya lulus.

Studi kasus ini menunjukkan bahwa kekuatan WPF bukan hanya pada tampilan. Kombinasi XAML, Data Binding, Command, MVVM, dan pemisahan akses data membuat aplikasi lebih terstruktur, mudah diuji, dan mudah dikembangkan.

---

### 14. Referensi

1. Materi PBKK Pertemuan 5 — *Studi Kasus WPF: Student Management System (Rich UI + MVVM + Data Binding + SQL Server)*.
2. Materi PBKK Pertemuan 5 — *WPF Panel Informasi: Implementasi Statistik dengan MVVM + Data Binding*.
3. Materi PBKK Pertemuan 5 — *Latihan WPF 3: Rich UI Application with WPF*.
4. Microsoft Learn — [Desktop Guide (WPF .NET)](https://learn.microsoft.com/en-us/dotnet/desktop/wpf/overview/)
5. Microsoft Learn — [Data binding overview (WPF .NET)](https://learn.microsoft.com/en-us/dotnet/desktop/wpf/data/)
6. Microsoft Learn — [Commanding Overview](https://learn.microsoft.com/en-us/dotnet/desktop/wpf/advanced/commanding-overview)
7. Microsoft Learn — [Styles and templates (WPF .NET)](https://learn.microsoft.com/en-us/dotnet/desktop/wpf/controls/styles-templates-overview)
8. Microsoft Learn — [DataGrid](https://learn.microsoft.com/en-us/dotnet/desktop/wpf/controls/datagrid)
9. Microsoft Learn — [Model-View-ViewModel (MVVM)](https://learn.microsoft.com/en-us/dotnet/architecture/maui/mvvm)
10. Microsoft Learn — [Introduction to Microsoft.Data.SqlClient namespace](https://learn.microsoft.com/en-us/sql/connect/ado-net/introduction-microsoft-data-sqlclient-namespace)
11. Microsoft Learn — [SQL Server Express LocalDB](https://learn.microsoft.com/en-us/sql/database-engine/configure-windows/sql-server-express-localdb)
12. NuGet — [Microsoft.Data.SqlClient 7.1.1](https://www.nuget.org/packages/Microsoft.Data.SqlClient/7.1.1)
