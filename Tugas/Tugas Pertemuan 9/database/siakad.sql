-- ============================================================
-- Database Latihan 2.1: SIAKAD - Sistem Informasi Akademik (CodeIgniter 3)
-- Impor lewat phpMyAdmin (tab Import) atau:
--   mysql -u root < database/siakad.sql
-- Akun uji (username / password):
--   admin              / admin123  (Admin akademik)
--   198001012005011001 / dosen123  (Dosen: Dr. Rina Kusuma)
--   5025231001         / mhs123    (Mahasiswa: Andi Pratama)
--   Mahasiswa yang ditambahkan admin memakai NRP sebagai password awal.
-- ============================================================

CREATE DATABASE IF NOT EXISTS siakad
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE siakad;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS krs;
DROP TABLE IF EXISTS kelas;
DROP TABLE IF EXISTS matakuliah;
DROP TABLE IF EXISTS mahasiswa;
DROP TABLE IF EXISTS dosen;
DROP TABLE IF EXISTS prodi;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id         INT          NOT NULL AUTO_INCREMENT,
  username   VARCHAR(32)  NOT NULL,
  password   VARCHAR(255) NOT NULL,
  nama       VARCHAR(100) NOT NULL,
  role       ENUM('admin','dosen','mahasiswa') NOT NULL,
  last_login DATETIME     NULL DEFAULT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE prodi (
  id      INT          NOT NULL AUTO_INCREMENT,
  kode    VARCHAR(10)  NOT NULL,
  nama    VARCHAR(100) NOT NULL,
  jenjang ENUM('D3','D4','S1','S2','S3') NOT NULL DEFAULT 'S1',
  PRIMARY KEY (id),
  UNIQUE KEY uq_prodi_kode (kode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE dosen (
  id       INT          NOT NULL AUTO_INCREMENT,
  user_id  INT          NOT NULL,
  nip      VARCHAR(18)  NOT NULL,
  nama     VARCHAR(100) NOT NULL,
  email    VARCHAR(100) NOT NULL,
  prodi_id INT          NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_dosen_nip (nip),
  CONSTRAINT fk_dosen_user  FOREIGN KEY (user_id)  REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_dosen_prodi FOREIGN KEY (prodi_id) REFERENCES prodi (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE mahasiswa (
  id            INT          NOT NULL AUTO_INCREMENT,
  user_id       INT          NOT NULL,
  nrp           VARCHAR(10)  NOT NULL,
  nama          VARCHAR(100) NOT NULL,
  email         VARCHAR(100) NOT NULL,
  angkatan      YEAR         NOT NULL,
  prodi_id      INT          NOT NULL,
  dosen_wali_id INT          NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mahasiswa_nrp (nrp),
  CONSTRAINT fk_mhs_user  FOREIGN KEY (user_id)       REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_mhs_prodi FOREIGN KEY (prodi_id)      REFERENCES prodi (id),
  CONSTRAINT fk_mhs_wali  FOREIGN KEY (dosen_wali_id) REFERENCES dosen (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE matakuliah (
  id       INT          NOT NULL AUTO_INCREMENT,
  kode     VARCHAR(10)  NOT NULL,
  nama     VARCHAR(100) NOT NULL,
  sks      TINYINT      NOT NULL,
  semester TINYINT      NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mk_kode (kode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Satu mata kuliah bisa dibuka beberapa kelas (A, B, ...) per tahun ajaran
CREATE TABLE kelas (
  id            INT         NOT NULL AUTO_INCREMENT,
  matakuliah_id INT         NOT NULL,
  dosen_id      INT         NOT NULL,
  nama_kelas    VARCHAR(5)  NOT NULL,
  tahun_ajaran  VARCHAR(20) NOT NULL,
  hari          ENUM('Senin','Selasa','Rabu','Kamis','Jumat') NOT NULL,
  jam_mulai     TIME        NOT NULL,
  jam_selesai   TIME        NOT NULL,
  ruang         VARCHAR(20) NOT NULL,
  kuota         TINYINT     NOT NULL DEFAULT 40,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kelas (matakuliah_id, nama_kelas, tahun_ajaran),
  CONSTRAINT fk_kelas_mk    FOREIGN KEY (matakuliah_id) REFERENCES matakuliah (id),
  CONSTRAINT fk_kelas_dosen FOREIGN KEY (dosen_id)      REFERENCES dosen (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- KRS: kelas yang diambil mahasiswa beserta nilainya
CREATE TABLE krs (
  id           INT          NOT NULL AUTO_INCREMENT,
  mahasiswa_id INT          NOT NULL,
  kelas_id     INT          NOT NULL,
  nilai_angka  DECIMAL(5,2) NULL DEFAULT NULL,
  nilai_huruf  VARCHAR(2)   NULL DEFAULT NULL,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_krs (mahasiswa_id, kelas_id),
  CONSTRAINT fk_krs_mhs   FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa (id) ON DELETE CASCADE,
  CONSTRAINT fk_krs_kelas FOREIGN KEY (kelas_id)     REFERENCES kelas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Data awal
-- ------------------------------------------------------------
INSERT INTO prodi (id, kode, nama, jenjang) VALUES
(1, 'IF', 'Teknik Informatika', 'S1'),
(2, 'SI', 'Sistem Informasi', 'S1'),
(3, 'TI', 'Teknologi Informasi', 'S1');

-- Password: admin123 / dosen123 / mhs123
INSERT INTO users (id, username, password, nama, role) VALUES
(1,  'admin',              '$2y$10$a7lqQY6KTx12bpjuLm7vOOKNSHwfwPbv1g67o2jOoTdrfOkmC0MiW', 'Admin Akademik',            'admin'),
(2,  '198001012005011001', '$2y$10$tLh2fF2SSXeYM/fjqcd1keeN88c45Lj2Al3bHezsaZ/9ys.212kUG', 'Dr. Rina Kusuma, S.Kom., M.Kom.', 'dosen'),
(3,  '197905122006041002', '$2y$10$tLh2fF2SSXeYM/fjqcd1keeN88c45Lj2Al3bHezsaZ/9ys.212kUG', 'Bambang Wijaya, S.T., M.T.',  'dosen'),
(4,  '198507232012122003', '$2y$10$tLh2fF2SSXeYM/fjqcd1keeN88c45Lj2Al3bHezsaZ/9ys.212kUG', 'Siti Rahmawati, S.Kom., M.Sc.', 'dosen'),
(5,  '5025231001', '$2y$10$OyRcChRSTR2xwnmUK4g38eoyxChAFCMiIIx1WtjI4iKvJyuR.AfMG', 'Andi Pratama',     'mahasiswa'),
(6,  '5025231002', '$2y$10$OyRcChRSTR2xwnmUK4g38eoyxChAFCMiIIx1WtjI4iKvJyuR.AfMG', 'Bella Safitri',    'mahasiswa'),
(7,  '5025231003', '$2y$10$OyRcChRSTR2xwnmUK4g38eoyxChAFCMiIIx1WtjI4iKvJyuR.AfMG', 'Cahyo Nugroho',    'mahasiswa'),
(8,  '5025231004', '$2y$10$OyRcChRSTR2xwnmUK4g38eoyxChAFCMiIIx1WtjI4iKvJyuR.AfMG', 'Dewi Lestari',     'mahasiswa'),
(9,  '5026231005', '$2y$10$OyRcChRSTR2xwnmUK4g38eoyxChAFCMiIIx1WtjI4iKvJyuR.AfMG', 'Eko Saputra',      'mahasiswa'),
(10, '5026231006', '$2y$10$OyRcChRSTR2xwnmUK4g38eoyxChAFCMiIIx1WtjI4iKvJyuR.AfMG', 'Fitri Handayani',  'mahasiswa'),
(11, '5027231007', '$2y$10$OyRcChRSTR2xwnmUK4g38eoyxChAFCMiIIx1WtjI4iKvJyuR.AfMG', 'Galih Ramadhan',   'mahasiswa'),
(12, '5025241008', '$2y$10$OyRcChRSTR2xwnmUK4g38eoyxChAFCMiIIx1WtjI4iKvJyuR.AfMG', 'Hana Pertiwi',     'mahasiswa');

INSERT INTO dosen (id, user_id, nip, nama, email, prodi_id) VALUES
(1, 2, '198001012005011001', 'Dr. Rina Kusuma, S.Kom., M.Kom.', 'rina.kusuma@kampus.test', 1),
(2, 3, '197905122006041002', 'Bambang Wijaya, S.T., M.T.',      'bambang.wijaya@kampus.test', 1),
(3, 4, '198507232012122003', 'Siti Rahmawati, S.Kom., M.Sc.',   'siti.rahmawati@kampus.test', 2);

INSERT INTO mahasiswa (id, user_id, nrp, nama, email, angkatan, prodi_id, dosen_wali_id) VALUES
(1, 5,  '5025231001', 'Andi Pratama',    'andi.pratama@student.kampus.test',   2023, 1, 1),
(2, 6,  '5025231002', 'Bella Safitri',   'bella.safitri@student.kampus.test',  2023, 1, 1),
(3, 7,  '5025231003', 'Cahyo Nugroho',   'cahyo.nugroho@student.kampus.test',  2023, 1, 2),
(4, 8,  '5025231004', 'Dewi Lestari',    'dewi.lestari@student.kampus.test',   2023, 1, 2),
(5, 9,  '5026231005', 'Eko Saputra',     'eko.saputra@student.kampus.test',    2023, 2, 3),
(6, 10, '5026231006', 'Fitri Handayani', 'fitri.handayani@student.kampus.test', 2023, 2, 3),
(7, 11, '5027231007', 'Galih Ramadhan',  'galih.ramadhan@student.kampus.test', 2023, 3, 2),
(8, 12, '5025241008', 'Hana Pertiwi',    'hana.pertiwi@student.kampus.test',   2024, 1, 1);

INSERT INTO matakuliah (id, kode, nama, sks, semester) VALUES
(1, 'IF184201', 'Basis Data',                          4, 4),
(2, 'IF184202', 'Pemrograman Web',                     3, 4),
(3, 'IF184203', 'Sistem Operasi',                      3, 4),
(4, 'IF184301', 'Pemrograman Berbasis Kerangka Kerja', 3, 5),
(5, 'IF184302', 'Rekayasa Perangkat Lunak',            3, 5),
(6, 'IF184303', 'Jaringan Komputer',                   4, 5),
(7, 'IF184304', 'Kecerdasan Buatan',                   3, 5),
(8, 'IF184305', 'Interaksi Manusia dan Komputer',      3, 5);

-- Tahun ajaran lalu (sudah ada nilai) dan tahun ajaran aktif (masa KRS)
INSERT INTO kelas (id, matakuliah_id, dosen_id, nama_kelas, tahun_ajaran, hari, jam_mulai, jam_selesai, ruang, kuota) VALUES
(1,  1, 2, 'A', '2025/2026 Genap', 'Senin',  '07:00', '10:20', 'IF-101', 40),
(2,  2, 1, 'A', '2025/2026 Genap', 'Selasa', '10:30', '13:00', 'IF-102', 40),
(3,  3, 3, 'A', '2025/2026 Genap', 'Rabu',   '13:30', '16:00', 'IF-103', 40),
(4,  4, 1, 'A', '2026/2027 Gasal', 'Senin',  '07:00', '09:30', 'IF-105', 40),
(5,  4, 1, 'C', '2026/2027 Gasal', 'Kamis',  '13:30', '16:00', 'IF-105', 3),
(6,  5, 2, 'A', '2026/2027 Gasal', 'Senin',  '08:00', '10:30', 'IF-106', 40),
(7,  6, 2, 'A', '2026/2027 Gasal', 'Selasa', '07:00', '10:20', 'IF-107', 40),
(8,  7, 3, 'A', '2026/2027 Gasal', 'Rabu',   '10:30', '13:00', 'IF-108', 40),
(9,  8, 3, 'A', '2026/2027 Gasal', 'Jumat',  '13:30', '16:00', 'IF-108', 40),
(10, 5, 2, 'B', '2026/2027 Gasal', 'Kamis',  '07:00', '09:30', 'IF-106', 40);

INSERT INTO krs (mahasiswa_id, kelas_id, nilai_angka, nilai_huruf) VALUES
-- nilai semester lalu
(1, 1, 88, 'A'),  (1, 2, 79, 'AB'), (1, 3, 70, 'B'),
(2, 1, 91, 'A'),  (2, 2, 85, 'AB'), (2, 3, 82, 'AB'),
(3, 1, 64, 'BC'), (3, 2, 72, 'B'),  (3, 3, 58, 'C'),
(4, 1, 77, 'AB'), (4, 2, 90, 'A'),  (4, 3, 75, 'B'),
-- KRS semester aktif (belum dinilai)
(2, 4, NULL, NULL), (2, 7, NULL, NULL), (2, 8, NULL, NULL),
(3, 4, NULL, NULL), (3, 7, NULL, NULL),
(4, 4, NULL, NULL), (4, 9, NULL, NULL),
(5, 5, NULL, NULL), (6, 5, NULL, NULL), (7, 5, NULL, NULL);
