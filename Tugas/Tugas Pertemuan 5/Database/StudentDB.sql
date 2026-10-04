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
