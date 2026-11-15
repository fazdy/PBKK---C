-- Skema alternatif untuk inspeksi/import manual; jalur utama memakai php spark migrate.
-- Jangan impor tabel ini lalu menjalankan migration CreateUsers pada database yang sama.
CREATE DATABASE IF NOT EXISTS db_shopease CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_shopease;
CREATE TABLE IF NOT EXISTS users (
  id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('customer','admin','operator') NOT NULL DEFAULT 'customer',
  phone VARCHAR(20) NULL,
  address TEXT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Data demo dibuat dengan php spark db:seed DemoUserSeeder agar password selalu di-hash.
