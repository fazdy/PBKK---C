-- ============================================================
-- Database Latihan 1: Tokobuah (CodeIgniter 3)
-- Impor lewat phpMyAdmin (tab Import) atau:
--   mysql -u root < database/tokobuah.sql
-- Akun uji:
--   admin / admin123  (role admin, boleh masuk panel admin)
--   budi  / budi123   (role customer, ditolak saat login admin)
-- ============================================================

CREATE DATABASE IF NOT EXISTS tokobuah
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE tokobuah;

DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

-- Tabel produk (PBKK-2-5 Model & CRUD, PBKK-2-6 File Upload)
CREATE TABLE products (
  product_id  VARCHAR(64)  NOT NULL,
  name        VARCHAR(255) NOT NULL,
  price       INT          NOT NULL DEFAULT 0,
  image       VARCHAR(255) NOT NULL DEFAULT 'default.jpg',
  description TEXT,
  PRIMARY KEY (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel pengguna (PBKK-2-7 Mengatur Akses User)
CREATE TABLE users (
  user_id    INT          NOT NULL AUTO_INCREMENT,
  username   VARCHAR(32)  NOT NULL,
  password   VARCHAR(255) NOT NULL,
  email      VARCHAR(255) NOT NULL,
  full_name  VARCHAR(64)  NOT NULL,
  phone      VARCHAR(20)  DEFAULT NULL,
  role       ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  last_login TIMESTAMP    NULL DEFAULT NULL,
  photo      VARCHAR(64)  NOT NULL DEFAULT 'user_no_image.jpg',
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_active  TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO products (product_id, name, price, image, description) VALUES
('6540a1b2c3d01', 'Apel Malang',    32000, 'apel.png',     'Apel hijau segar dari kebun Batu, Malang. Rasanya manis keasaman dan renyah, cocok untuk camilan maupun jus.'),
('6540a1b2c3d02', 'Jeruk Pontianak', 25000, 'jeruk.png',    'Jeruk siam Pontianak dengan kulit tipis dan banyak air. Kaya vitamin C, nikmat diperas menjadi jus segar.'),
('6540a1b2c3d03', 'Mangga Harum Manis', 30000, 'mangga.png', 'Mangga arumanis asal Probolinggo. Daging buah tebal, tidak berserat, dan beraroma harum saat matang.'),
('6540a1b2c3d04', 'Pisang Cavendish', 22000, 'pisang.png',   'Pisang cavendish satu sisir berisi 12 buah. Sumber energi cepat yang praktis dibawa ke kampus.'),
('6540a1b2c3d05', 'Semangka Merah',  18000, 'semangka.png', 'Semangka merah tanpa biji, dijual per kilogram. Manis, berair, dan menyegarkan di siang hari.'),
('6540a1b2c3d06', 'Anggur Hijau',    65000, 'anggur.png',   'Anggur hijau impor tanpa biji, dijual per 500 gram. Renyah dan manis, cocok untuk salad buah.');

INSERT INTO users (username, password, email, full_name, phone, role) VALUES
('admin', '$2y$10$A9D4JBhiGu8TgpKZ2a41iulE2.ljDxIZSpPf435E151n9Uy.9Emnm', 'admin@tokobuah.test', 'Administrator Tokobuah', '081234567890', 'admin'),
('budi',  '$2y$10$vlaSmrmRGXevPd/imOMGYu.WVUJ0Apu2jfelKG.3f5jncgh7OfdE2', 'budi@tokobuah.test',  'Budi Santoso',          '081298765432', 'customer');
