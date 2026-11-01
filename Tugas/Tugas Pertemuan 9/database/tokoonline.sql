-- ============================================================
-- Database Latihan 2.2: KampusMart - Toko Online (CodeIgniter 3)
-- Impor lewat phpMyAdmin (tab Import) atau:
--   mysql -u root < database/tokoonline.sql
-- Akun uji (email / password):
--   admin@kampusmart.test / admin123  (admin toko)
--   budi@kampusmart.test  / budi123   (pelanggan)
--   siti@kampusmart.test  / siti123   (pelanggan)
-- ============================================================

CREATE DATABASE IF NOT EXISTS tokoonline
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE tokoonline;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS pesanan_detail;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id         INT          NOT NULL AUTO_INCREMENT,
  nama       VARCHAR(100) NOT NULL,
  email      VARCHAR(100) NOT NULL,
  password   VARCHAR(255) NOT NULL,
  telepon    VARCHAR(20)  DEFAULT NULL,
  alamat     TEXT,
  role       ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE kategori (
  id   INT          NOT NULL AUTO_INCREMENT,
  nama VARCHAR(50)  NOT NULL,
  slug VARCHAR(60)  NOT NULL,
  ikon VARCHAR(40)  NOT NULL DEFAULT 'bi-tag',
  PRIMARY KEY (id),
  UNIQUE KEY uq_kategori_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE produk (
  id          INT          NOT NULL AUTO_INCREMENT,
  kategori_id INT          NOT NULL,
  nama        VARCHAR(100) NOT NULL,
  slug        VARCHAR(120) NOT NULL,
  deskripsi   TEXT,
  harga       INT          NOT NULL,
  stok        INT          NOT NULL DEFAULT 0,
  gambar      VARCHAR(255) NOT NULL DEFAULT 'default.png',
  aktif       TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_produk_slug (slug),
  CONSTRAINT fk_produk_kategori FOREIGN KEY (kategori_id) REFERENCES kategori (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pesanan (
  id            INT          NOT NULL AUTO_INCREMENT,
  kode          VARCHAR(20)  NOT NULL,
  user_id       INT          NOT NULL,
  nama_penerima VARCHAR(100) NOT NULL,
  telepon       VARCHAR(20)  NOT NULL,
  alamat        TEXT         NOT NULL,
  catatan       VARCHAR(255) DEFAULT NULL,
  metode_bayar  ENUM('transfer','cod') NOT NULL DEFAULT 'transfer',
  subtotal      INT          NOT NULL,
  ongkir        INT          NOT NULL,
  total         INT          NOT NULL,
  status        ENUM('menunggu','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  created_at    DATETIME     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pesanan_kode (kode),
  CONSTRAINT fk_pesanan_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Nama & harga produk disalin agar riwayat pesanan tidak berubah saat produk diedit
CREATE TABLE pesanan_detail (
  id          INT          NOT NULL AUTO_INCREMENT,
  pesanan_id  INT          NOT NULL,
  produk_id   INT          NULL,
  nama_produk VARCHAR(100) NOT NULL,
  harga       INT          NOT NULL,
  qty         INT          NOT NULL,
  subtotal    INT          NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_detail_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan (id) ON DELETE CASCADE,
  CONSTRAINT fk_detail_produk  FOREIGN KEY (produk_id)  REFERENCES produk (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Data awal
-- ------------------------------------------------------------
INSERT INTO users (id, nama, email, password, telepon, alamat, role) VALUES
(1, 'Admin KampusMart', 'admin@kampusmart.test', '$2y$10$a7lqQY6KTx12bpjuLm7vOOKNSHwfwPbv1g67o2jOoTdrfOkmC0MiW', '0315550001', 'Gudang KampusMart, Surabaya', 'admin'),
(2, 'Budi Santoso',     'budi@kampusmart.test',  '$2y$10$/KnePPQOgjsDq9EW1yMzTecK0iSjbojJ.LTumPJAWTzXkf7IAavMy', '081234500001', 'Asrama Mahasiswa Blok C No. 12, Keputih, Sukolilo, Surabaya', 'customer'),
(3, 'Siti Aminah',      'siti@kampusmart.test',  '$2y$10$N3tS07C2i3y2pk0fwIUqNOeh0/whtTrjW5A27IG09h5oV7NNoKwrG', '081234500002', 'Jl. Gebang Lor No. 45, Sukolilo, Surabaya', 'customer');

INSERT INTO kategori (id, nama, slug, ikon) VALUES
(1, 'Alat Tulis',      'alat-tulis',     'bi-pen'),
(2, 'Buku & Kertas',   'buku-kertas',    'bi-journal-bookmark'),
(3, 'Tas & Aksesori',  'tas-aksesori',   'bi-backpack'),
(4, 'Elektronik',      'elektronik',     'bi-cpu');

INSERT INTO produk (id, kategori_id, nama, slug, deskripsi, harga, stok, gambar) VALUES
(1,  1, 'Pulpen Gel 0.5 mm',       'pulpen-gel',          'Pulpen gel tinta hitam ujung 0.5 mm. Tulisan halus, cepat kering, dan nyaman digenggam untuk mencatat kuliah seharian.', 4500, 120, 'pulpen-gel.png'),
(2,  1, 'Pensil 2B',               'pensil-2b',           'Pensil 2B untuk ujian lembar jawaban komputer. Grafit pekat dan mudah dihapus.', 3000, 200, 'pensil-2b.png'),
(3,  1, 'Spidol Warna isi 6',      'stabilo-highlighter', 'Satu set spidol warna-warni untuk menandai catatan dan membuat mind map.', 32000, 40, 'stabilo-highlighter.png'),
(4,  1, 'Penggaris Besi 30 cm',    'penggaris-besi',      'Penggaris stainless 30 cm, presisi dan tidak mudah bengkok. Cocok untuk menggambar teknik.', 9000, 60, 'penggaris-besi.png'),
(5,  1, 'Gunting Kertas',          'gunting-kertas',      'Gunting serbaguna dengan pegangan karet anti selip.', 12000, 35, 'gunting-kertas.png'),
(6,  2, 'Buku Tulis A5 isi 58',    'buku-tulis-a5',       'Buku tulis bergaris ukuran A5, kertas 70 gsm yang tidak tembus tinta.', 6000, 150, 'buku-tulis-a5.png'),
(7,  2, 'Binder A5 + Isi 100',     'binder-a5',           'Binder A5 26 ring lengkap dengan 100 lembar kertas isi. Catatan tiap mata kuliah bisa dipisah dengan rapi.', 35000, 25, 'binder-a5.png'),
(8,  2, 'Sticky Notes 3x3',        'sticky-notes',        'Kertas tempel 100 lembar untuk pengingat tugas dan deadline.', 8000, 80, 'sticky-notes.png'),
(9,  3, 'Tas Ransel Laptop 15"',   'tas-ransel-laptop',   'Ransel dengan kompartemen laptop 15 inci berbusa, tahan cipratan air, dan punya port USB.', 225000, 12, 'tas-ransel-laptop.png'),
(10, 3, 'Tumbler 750 ml',          'botol-minum',         'Tumbler plastik bebas BPA 750 ml dengan sedotan. Hemat dan ramah lingkungan.', 45000, 30, 'botol-minum.png'),
(11, 4, 'Kalkulator Ilmiah',       'kalkulator-ilmiah',   'Kalkulator ilmiah 240 fungsi untuk kalkulus, statistika, dan aljabar matriks.', 185000, 15, 'kalkulator-ilmiah.png'),
(12, 4, 'Flashdisk 64 GB',         'flashdisk-64gb',      'Flashdisk USB 3.2 kapasitas 64 GB, kecepatan baca hingga 100 MB/s.', 75000, 50, 'flashdisk-64gb.png'),
(13, 4, 'Headphone Bluetooth',     'headphone',           'Headphone nirkabel dengan peredam bising aktif, baterai hingga 30 jam.', 150000, 0, 'headphone.png'),
(14, 4, 'Lampu Belajar LED',       'lampu-belajar',       'Lampu meja LED 3 mode warna dengan pengatur kecerahan, nyaman untuk begadang mengerjakan tugas.', 89000, 18, 'lampu-belajar.png'),
(15, 4, 'Mouse Wireless',          'mouse-wireless',      'Mouse nirkabel 2.4 GHz tanpa suara klik, baterai awet hingga 12 bulan.', 65000, 40, 'mouse-wireless.png');

-- Pesanan contoh dalam 7 hari terakhir (untuk grafik dashboard admin)
INSERT INTO pesanan (id, kode, user_id, nama_penerima, telepon, alamat, metode_bayar, subtotal, ongkir, total, status, created_at) VALUES
(1, 'KM-0001', 2, 'Budi Santoso', '081234500001', 'Asrama Mahasiswa Blok C No. 12, Keputih, Sukolilo, Surabaya', 'transfer', 236000, 10000, 246000, 'selesai',  DATE_SUB(NOW(), INTERVAL 6 DAY)),
(2, 'KM-0002', 3, 'Siti Aminah',  '081234500002', 'Jl. Gebang Lor No. 45, Sukolilo, Surabaya',                  'cod',      51000,  10000, 61000,  'selesai',  DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 'KM-0003', 2, 'Budi Santoso', '081234500001', 'Asrama Mahasiswa Blok C No. 12, Keputih, Sukolilo, Surabaya', 'transfer', 185000, 10000, 195000, 'dikirim',  DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 'KM-0004', 3, 'Siti Aminah',  '081234500002', 'Jl. Gebang Lor No. 45, Sukolilo, Surabaya',                  'transfer', 134000, 10000, 144000, 'diproses', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 'KM-0005', 3, 'Siti Aminah',  '081234500002', 'Jl. Gebang Lor No. 45, Sukolilo, Surabaya',                  'cod',      18000,  10000, 28000,  'menunggu', DATE_SUB(NOW(), INTERVAL 2 HOUR));

INSERT INTO pesanan_detail (pesanan_id, produk_id, nama_produk, harga, qty, subtotal) VALUES
(1, 9,  'Tas Ransel Laptop 15"', 225000, 1, 225000),
(1, 2,  'Pensil 2B',             3000,   2, 6000),
(1, 4,  'Penggaris Besi 30 cm',  5000,   1, 5000),
(2, 7,  'Binder A5 + Isi 100',   35000,  1, 35000),
(2, 8,  'Sticky Notes 3x3',      8000,   2, 16000),
(3, 11, 'Kalkulator Ilmiah',     185000, 1, 185000),
(4, 15, 'Mouse Wireless',        65000,  1, 65000),
(4, 10, 'Tumbler 750 ml',        45000,  1, 45000),
(4, 6,  'Buku Tulis A5 isi 58',  6000,   4, 24000),
(5, 1,  'Pulpen Gel 0.5 mm',     4500,   4, 18000);
