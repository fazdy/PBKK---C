-- =====================================================================
-- SIBASA - Sistem Informasi Bank Sampah
-- 01_create_database.sql : pengaturan awal database SQLite
-- ---------------------------------------------------------------------
-- Pada SQLite, database berupa satu file (.db). File otomatis dibuat
-- saat pertama kali dibuka, sehingga tidak perlu perintah CREATE DATABASE.
-- Skrip ini mengatur PRAGMA yang harus berlaku sebelum tabel dibuat.
--
-- Aplikasi menjalankan skrip ini otomatis lewat DbInitializer saat file
-- database belum ada. Untuk menjalankan manual (opsional):
--   sqlite3 sibasa.db < 01_create_database.sql
-- =====================================================================

-- Simpan teks dalam UTF-8 (harus diatur sebelum tabel pertama dibuat).
PRAGMA encoding = 'UTF-8';

-- Write-Ahead Logging: penulisan lebih tahan gangguan dan pembacaan
-- tidak terblokir ketika ada proses yang sedang menulis.
PRAGMA journal_mode = WAL;

-- Aktifkan pemeriksaan foreign key. Pengaturan ini berlaku per koneksi,
-- karena itu aplikasi juga menambahkan "Foreign Keys=True" di connection string.
PRAGMA foreign_keys = ON;
