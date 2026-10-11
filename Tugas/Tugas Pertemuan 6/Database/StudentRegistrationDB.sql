/* =====================================================================
   HANDS-ON LAB — Database Student Registration (SQL Server)
   Nama  : Faizal Aldy Armiriawan
   NRP   : 5025231266
   Kelas : PBKK (C)

   Server : SQL Server 2022 di Docker (macOS), localhost,1433, login sa
   Tools  : DBeaver / sqlcmd

   Script ini aman dijalankan ulang karena memakai pengecekan
   IF ... IS NULL dan IF NOT EXISTS. Bagian pengujian constraint
   (LAB 19 dan LAB 20) sengaja diletakkan paling akhir karena memang
   HARUS GAGAL.
   ===================================================================== */


/* ---------------------------------------------------------------------
   LAB 09 — Membuat Database
   --------------------------------------------------------------------- */
IF DB_ID('StudentRegistrationDB') IS NULL
    CREATE DATABASE StudentRegistrationDB;
GO


/* ---------------------------------------------------------------------
   LAB 10 — Menggunakan Database
   --------------------------------------------------------------------- */
USE StudentRegistrationDB;
GO

SELECT DB_NAME() AS CurrentDatabase;
GO


/* ---------------------------------------------------------------------
   LAB 11 — Membuat Tabel Programs
   --------------------------------------------------------------------- */
IF OBJECT_ID('dbo.Programs', 'U') IS NULL
BEGIN
    CREATE TABLE Programs
    (
        ProgramId INT IDENTITY(1,1)
            CONSTRAINT PK_Programs PRIMARY KEY,

        ProgramCode VARCHAR(20) NOT NULL
            CONSTRAINT UQ_Programs_ProgramCode UNIQUE,

        ProgramName VARCHAR(100) NOT NULL,

        IsActive BIT NOT NULL
            CONSTRAINT DF_Programs_IsActive DEFAULT 1,

        CreatedAt DATETIME2 NOT NULL
            CONSTRAINT DF_Programs_CreatedAt
            DEFAULT SYSDATETIME()
    );
END
GO


/* ---------------------------------------------------------------------
   LAB 12 — Memasukkan Data Program Studi
   --------------------------------------------------------------------- */
IF NOT EXISTS (SELECT 1 FROM Programs)
BEGIN
    INSERT INTO Programs
    (
        ProgramCode,
        ProgramName
    )
    VALUES
    ('TI',  'Teknik Informatika'),
    ('SI',  'Sistem Informasi'),
    ('MNJ', 'Manajemen'),
    ('TE',  'Teknik Elektro'),
    ('DKV', 'Desain Komunikasi Visual');
END
GO


/* ---------------------------------------------------------------------
   LAB 13 — Melihat Data Programs
   --------------------------------------------------------------------- */
SELECT *
FROM Programs;
GO


/* ---------------------------------------------------------------------
   LAB 14 — Membuat Tabel Students (relasi 1:N ke Programs)
   --------------------------------------------------------------------- */
IF OBJECT_ID('dbo.Students', 'U') IS NULL
BEGIN
    CREATE TABLE Students
    (
        StudentId INT IDENTITY(1,1)
            CONSTRAINT PK_Students PRIMARY KEY,

        NIM VARCHAR(20) NOT NULL
            CONSTRAINT UQ_Students_NIM UNIQUE,

        Name VARCHAR(100) NOT NULL,

        ProgramId INT NOT NULL,

        BirthDate DATE NULL,

        Address VARCHAR(250) NULL,

        PhoneNumber VARCHAR(20) NULL,

        CreatedAt DATETIME2 NOT NULL
            CONSTRAINT DF_Students_CreatedAt
            DEFAULT SYSDATETIME(),

        UpdatedAt DATETIME2 NULL,

        CONSTRAINT FK_Students_Programs
            FOREIGN KEY (ProgramId)
            REFERENCES Programs(ProgramId)
    );
END
GO


/* ---------------------------------------------------------------------
   LAB 16 — Memasukkan Data Mahasiswa
   ProgramId: 1 = TI, 2 = SI, 3 = MNJ, 4 = TE, 5 = DKV
   --------------------------------------------------------------------- */
IF NOT EXISTS (SELECT 1 FROM Students)
BEGIN
    INSERT INTO Students
    (
        NIM,
        Name,
        ProgramId,
        BirthDate,
        Address,
        PhoneNumber
    )
    VALUES
    ('20231001', 'Ahmad Rizki',   1, '2003-03-12', 'Jl. Merdeka No. 123, Surabaya',   '081234567890'),
    ('20231002', 'Siti Aisyah',   2, '2004-07-21', 'Jl. Diponegoro No. 45, Surabaya', '082233445566'),
    ('20231003', 'Budi Santoso',  1, '2003-11-05', 'Jl. Ahmad Yani No. 10, Surabaya', '081355667788'),
    ('20231004', 'Citra Lestari', 3, '2004-01-18', 'Jl. Darmo No. 20, Surabaya',      '089900112233'),
    ('20231005', 'Dimas Prayoga', 4, '2003-09-27', 'Jl. Raya ITS, Surabaya',          '081288776655');
END
GO


/* ---------------------------------------------------------------------
   LAB 17 — Menampilkan Data Students
   --------------------------------------------------------------------- */
SELECT *
FROM Students;
GO


/* ---------------------------------------------------------------------
   LAB 18 — Menggabungkan Dua Tabel (INNER JOIN)
   --------------------------------------------------------------------- */
SELECT
    s.StudentId,
    s.NIM,
    s.Name,
    p.ProgramCode,
    p.ProgramName,
    s.BirthDate,
    s.PhoneNumber
FROM Students s
INNER JOIN Programs p
    ON s.ProgramId = p.ProgramId;
GO


/* ---------------------------------------------------------------------
   LAB 24 — Query Challenge
   --------------------------------------------------------------------- */

-- Challenge 1 — Tampilkan seluruh mahasiswa
SELECT *
FROM Students;
GO

-- Challenge 2 — Tampilkan mahasiswa Teknik Informatika
SELECT
    s.NIM,
    s.Name,
    p.ProgramName
FROM Students s
INNER JOIN Programs p
    ON s.ProgramId = p.ProgramId
WHERE p.ProgramCode = 'TI';
GO

-- Challenge 3 — Hitung jumlah mahasiswa
SELECT COUNT(*) AS TotalMahasiswa
FROM Students;
GO

-- Challenge 4 — Hitung mahasiswa berdasarkan program studi
SELECT
    p.ProgramName,
    COUNT(s.StudentId) AS JumlahMahasiswa
FROM Programs p
LEFT JOIN Students s
    ON p.ProgramId = s.ProgramId
GROUP BY
    p.ProgramId,
    p.ProgramName;
GO


/* ---------------------------------------------------------------------
   MINI ASSIGNMENT — 3 mahasiswa baru (TI, SI, TE)
   --------------------------------------------------------------------- */
IF NOT EXISTS (SELECT 1 FROM Students WHERE NIM = '20231006')
    INSERT INTO Students (NIM, Name, ProgramId, BirthDate, Address, PhoneNumber)
    VALUES ('20231006', 'Eka Putri Ramadhani', 1, '2004-05-14', 'Jl. Kertajaya No. 8, Surabaya', '081200000006');   -- TI

IF NOT EXISTS (SELECT 1 FROM Students WHERE NIM = '20231007')
    INSERT INTO Students (NIM, Name, ProgramId, BirthDate, Address, PhoneNumber)
    VALUES ('20231007', 'Fajar Nugroho', 2, '2003-12-02', 'Jl. Manyar Kertoarjo No. 15, Surabaya', '081200000007'); -- SI

IF NOT EXISTS (SELECT 1 FROM Students WHERE NIM = '20231008')
    INSERT INTO Students (NIM, Name, ProgramId, BirthDate, Address, PhoneNumber)
    VALUES ('20231008', 'Gita Maharani', 4, '2004-08-09', 'Jl. Keputih No. 3, Surabaya', '081200000008');         -- TE
GO

SELECT
    s.NIM,
    s.Name,
    p.ProgramName,
    s.BirthDate,
    s.PhoneNumber
FROM Students s
INNER JOIN Programs p
    ON s.ProgramId = p.ProgramId
ORDER BY s.Name;
GO


/* ---------------------------------------------------------------------
   LAB 19 — Pengujian Foreign Key  (HARUS GAGAL)
   ProgramId 99 tidak ada di tabel Programs.
   --------------------------------------------------------------------- */
INSERT INTO Students
(
    NIM,
    Name,
    ProgramId
)
VALUES
(
    '20231099',
    'Test Student',
    99
);
GO


/* ---------------------------------------------------------------------
   LAB 20 — Pengujian UNIQUE NIM  (HARUS GAGAL)
   NIM 20231001 sudah dimiliki Ahmad Rizki.
   --------------------------------------------------------------------- */
INSERT INTO Students
(
    NIM,
    Name,
    ProgramId
)
VALUES
(
    '20231001',
    'Mahasiswa Duplikat',
    1
);
GO
