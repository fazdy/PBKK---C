-- =====================================================================
-- SIBASA - Sistem Informasi Bank Sampah
-- 03_seed_data.sql : data contoh untuk demo dan pengujian
-- ---------------------------------------------------------------------
-- * Semua nama, alamat, dan nomor HP bersifat fiktif.
-- * Harga per kg hanya ilustrasi, bukan harga pasar resmi.
-- * Tanggal transaksi dibuat RELATIF terhadap hari ini (datetime('now')),
--   sehingga dashboard selalu menampilkan data 6 bulan terakhir, kapan pun
--   database dibuat.
-- * Akun demo (password disimpan sebagai hash PBKDF2-SHA256, 600.000 iterasi):
--     admin   / Admin#123    (peran Admin)
--     petugas / Petugas#123  (peran Petugas)
-- Aplikasi menjalankan skrip ini otomatis bila "Database:SeedSampleData" = true.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Pengguna
-- ---------------------------------------------------------------------
INSERT INTO Users (Username, PasswordHash, NamaLengkap, Role, IsActive, CreatedAt) VALUES
('admin',   'PBKDF2-SHA256$600000$xM2Q8JOm4Ane3F7XGu7SoA==$FUPFfl4x5fyiuZD+kOBKS2VRz0lWY7QPKlOmWhQpAzQ=', 'Administrator', 'Admin',   1, datetime('now', 'localtime', '-210 days')),
('petugas', 'PBKDF2-SHA256$600000$nT5MY/GfKlr3SrF9CY3RxQ==$vNJDTVLhUafZrrRE7dveOf3Q0/damApUfy/eNsT/Elg=', 'Rina Marlina',  'Petugas', 1, datetime('now', 'localtime', '-205 days'));

-- ---------------------------------------------------------------------
-- 2. Kategori dan jenis sampah
-- ---------------------------------------------------------------------
INSERT INTO KategoriSampah (NamaKategori, Keterangan) VALUES
('Plastik',         'Botol, gelas, kemasan, dan plastik keras'),
('Kertas',          'Kardus, kertas HVS, koran, dan duplex'),
('Logam',           'Kaleng, besi, dan tembaga'),
('Kaca',            'Botol kaca dan pecahan kaca'),
('Minyak Jelantah', 'Minyak goreng bekas dalam jeriken tertutup');

-- Urutan baris menentukan JenisId 1..16 (dipakai pola setoran di bawah).
INSERT INTO JenisSampah (KategoriId, NamaJenis, HargaPerKg, IsActive) VALUES
(1, 'Botol PET Bening',              4000, 1),   -- 1
(1, 'Botol PET Berwarna',            2500, 1),   -- 2
(1, 'Gelas Plastik',                 3000, 1),   -- 3
(1, 'Plastik Keras (Ember/Jeriken)', 2000, 1),   -- 4
(1, 'Plastik Kresek',                 400, 1),   -- 5
(2, 'Kardus',                        2000, 1),   -- 6
(2, 'Kertas HVS/Arsip',              2500, 1),   -- 7
(2, 'Koran Bekas',                   1800, 1),   -- 8
(2, 'Kertas Duplex',                  800, 1),   -- 9
(3, 'Kaleng Aluminium',             10000, 1),   -- 10
(3, 'Besi',                          3500, 1),   -- 11
(3, 'Tembaga',                      60000, 1),   -- 12
(4, 'Botol Kaca Utuh',                800, 1),   -- 13
(4, 'Beling (Pecahan Kaca)',          300, 1),   -- 14
(5, 'Minyak Jelantah',               5000, 1),   -- 15
(1, 'Styrofoam Bersih',               500, 0);   -- 16 (nonaktif, contoh status)

-- ---------------------------------------------------------------------
-- 3. Nasabah (20 orang, nomor 20 nonaktif dan belum pernah bertransaksi)
-- ---------------------------------------------------------------------
INSERT INTO Nasabah (NoRekening, NamaLengkap, NoHp, Alamat, RT, RW, Saldo, IsActive, TanggalDaftar) VALUES
('BS-0001', 'Ahmad Fauzi',       '081234560001', 'Jl. Kenanga No. 3',    '01', '01', 0, 1, datetime(date('now', 'localtime'), '-199 days', '+9 hours')),
('BS-0002', 'Siti Aminah',       '081234560002', 'Jl. Kenanga No. 11',   '02', '01', 0, 1, datetime(date('now', 'localtime'), '-198 days', '+9 hours')),
('BS-0003', 'Budi Santoso',      '081234560003', 'Jl. Melati No. 7',     '03', '01', 0, 1, datetime(date('now', 'localtime'), '-197 days', '+10 hours')),
('BS-0004', 'Dewi Anggraini',    '081234560004', 'Jl. Mawar No. 2',      '01', '02', 0, 1, datetime(date('now', 'localtime'), '-195 days', '+10 hours')),
('BS-0005', 'Eko Prasetyo',      '081234560005', 'Jl. Mawar No. 15',     '02', '02', 0, 1, datetime(date('now', 'localtime'), '-194 days', '+11 hours')),
('BS-0006', 'Fitri Handayani',   '081234560006', 'Jl. Anggrek No. 4',    '03', '02', 0, 1, datetime(date('now', 'localtime'), '-190 days', '+9 hours')),
('BS-0007', 'Gunawan Wibisono',  '081234560007', 'Jl. Anggrek No. 21',   '04', '02', 0, 1, datetime(date('now', 'localtime'), '-188 days', '+13 hours')),
('BS-0008', 'Hesti Rahmawati',   '081234560008', 'Jl. Flamboyan No. 9',  '01', '03', 0, 1, datetime(date('now', 'localtime'), '-185 days', '+9 hours')),
('BS-0009', 'Imam Syafii',       '081234560009', 'Jl. Flamboyan No. 18', '02', '03', 0, 1, datetime(date('now', 'localtime'), '-183 days', '+14 hours')),
('BS-0010', 'Joko Susilo',       '081234560010', 'Jl. Cempaka No. 6',    '03', '03', 0, 1, datetime(date('now', 'localtime'), '-180 days', '+10 hours')),
('BS-0011', 'Kartini Wulandari', '081234560011', 'Jl. Kenanga No. 25',   '04', '01', 0, 1, datetime(date('now', 'localtime'), '-176 days', '+9 hours')),
('BS-0012', 'Lukman Hakim',      '081234560012', 'Jl. Melati No. 30',    '05', '01', 0, 1, datetime(date('now', 'localtime'), '-172 days', '+15 hours')),
('BS-0013', 'Maya Puspitasari',  '081234560013', 'Jl. Cempaka No. 14',   '04', '03', 0, 1, datetime(date('now', 'localtime'), '-168 days', '+10 hours')),
('BS-0014', 'Nur Hidayah',       '081234560014', 'Jl. Mawar No. 27',     '05', '02', 0, 1, datetime(date('now', 'localtime'), '-160 days', '+11 hours')),
('BS-0015', 'Oki Setiawan',      '081234560015', 'Jl. Cempaka No. 31',   '05', '03', 0, 1, datetime(date('now', 'localtime'), '-150 days', '+9 hours')),
('BS-0016', 'Putri Ayu Lestari', '081234560016', 'Jl. Kenanga No. 8',    '01', '01', 0, 1, datetime(date('now', 'localtime'), '-140 days', '+16 hours')),
('BS-0017', 'Rudi Kurniawan',    '081234560017', 'Jl. Anggrek No. 12',   '02', '02', 0, 1, datetime(date('now', 'localtime'), '-120 days', '+10 hours')),
('BS-0018', 'Sri Wahyuni',       '081234560018', 'Jl. Flamboyan No. 2',  '03', '03', 0, 1, datetime(date('now', 'localtime'), '-100 days', '+9 hours')),
('BS-0019', 'Teguh Pambudi',     '081234560019', 'Jl. Melati No. 19',    '04', '01', 0, 1, datetime(date('now', 'localtime'), '-80 days',  '+13 hours')),
('BS-0020', 'Umi Kalsum',        '081234560020', 'Jl. Mawar No. 33',     '05', '02', 0, 0, datetime(date('now', 'localtime'), '-60 days',  '+10 hours'));

-- ---------------------------------------------------------------------
-- 4. Setoran (120 transaksi tersebar ~6 bulan terakhir, 6 di antaranya hari ini)
--    Dibuat dengan recursive CTE agar deterministik dan ringkas:
--    * nasabah : ((n * 11) % 19) + 1          -> nasabah 1..19
--    * jenis   : pola berbobot (plastik & kertas lebih sering)
--    * berat   : 0,5 - 8,4 kg, disesuaikan per jenis (tembaga lebih ringan)
--    * setoran ke-64 dibatalkan sebagai contoh status "Dibatalkan"
-- ---------------------------------------------------------------------
WITH RECURSIVE seq(n) AS (
    SELECT 1
    UNION ALL
    SELECT n + 1 FROM seq WHERE n < 120
),
pola AS (
    SELECT n,
           ((n * 11) % 19) + 1 AS NasabahId,
           CAST(substr('01010102030305060606070708091010110413141515021201', ((n * 7) % 25) * 2 + 1, 2) AS INTEGER) AS JenisId,
           ((n * 37) % 80) / 10.0 + 0.5 AS BeratDasar,
           MAX(0, ((115 - n) * 3) / 2) AS HariLalu
    FROM seq
),
rinci AS (
    SELECT p.n, p.NasabahId, p.JenisId,
           ROUND(CASE
                     WHEN p.JenisId = 12 THEN p.BeratDasar / 8.0      -- tembaga: sedikit tapi mahal
                     WHEN p.JenisId IN (10, 15) THEN p.BeratDasar / 2.0
                     WHEN p.JenisId IN (6, 7, 8) THEN p.BeratDasar * 1.5
                     ELSE p.BeratDasar
                 END, 2) AS BeratKg,
           CASE
               WHEN p.HariLalu = 0
                   THEN datetime('now', 'localtime', printf('-%d minutes', (121 - p.n) * 17))
               ELSE datetime(date('now', 'localtime'),
                             printf('-%d days', p.HariLalu),
                             printf('+%d hours', 8 + (p.n % 9)),
                             printf('+%d minutes', (p.n * 13) % 60))
           END AS Tanggal
    FROM pola p
)
INSERT INTO Setoran (NoTransaksi, NasabahId, JenisId, BeratKg, HargaPerKg, Total, Tanggal, UserId, Catatan, IsCancelled, CancelledAt)
SELECT 'STR-' || strftime('%Y%m%d', r.Tanggal) || '-' || printf('%05d', r.n),
       r.NasabahId,
       r.JenisId,
       r.BeratKg,
       j.HargaPerKg,
       CAST(ROUND(r.BeratKg * j.HargaPerKg) AS INTEGER),
       r.Tanggal,
       CASE WHEN r.n % 3 = 0
            THEN (SELECT UserId FROM Users WHERE Username = 'admin')
            ELSE (SELECT UserId FROM Users WHERE Username = 'petugas') END,
       CASE WHEN r.n = 64 THEN 'Salah input berat, diganti transaksi baru'
            WHEN r.n % 17 = 0 THEN 'Sampah sudah dipilah dan bersih'
            ELSE NULL END,
       CASE WHEN r.n = 64 THEN 1 ELSE 0 END,
       CASE WHEN r.n = 64 THEN datetime(r.Tanggal, '+2 hours') ELSE NULL END
FROM rinci r
JOIN JenisSampah j ON j.JenisId = r.JenisId
ORDER BY r.n;

-- ---------------------------------------------------------------------
-- 5. Penarikan: nasabah kelipatan 3 menarik 40% dari setoran yang berumur
--    lebih dari 20 hari (dibulatkan ke bawah per Rp1.000), dalam 6 hari terakhir.
--    Karena hanya sebagian setoran yang ditarik, saldo tidak pernah minus.
-- ---------------------------------------------------------------------
INSERT INTO Penarikan (NoTransaksi, NasabahId, Jumlah, Tanggal, UserId, Catatan)
SELECT 'TRK-' || strftime('%Y%m%d', t.Tanggal) || '-' || printf('%05d', t.rn),
       t.NasabahId,
       t.Jumlah,
       t.Tanggal,
       (SELECT UserId FROM Users WHERE Username = 'petugas'),
       'Penarikan tunai'
FROM (
    SELECT ROW_NUMBER() OVER (ORDER BY s.NasabahId) AS rn,
           s.NasabahId,
           CAST(SUM(s.Total) * 0.4 / 1000 AS INTEGER) * 1000 AS Jumlah,
           CASE WHEN s.NasabahId / 3 - 1 = 0
                THEN datetime('now', 'localtime', '-35 minutes')
                ELSE datetime(date('now', 'localtime'), printf('-%d days', s.NasabahId / 3 - 1), '+10 hours', '+15 minutes')
           END AS Tanggal
    FROM Setoran s
    WHERE s.IsCancelled = 0
      AND s.NasabahId % 3 = 0
      AND s.Tanggal < datetime('now', 'localtime', '-20 days')
    GROUP BY s.NasabahId
    HAVING SUM(s.Total) * 0.4 >= 1000
) t
ORDER BY t.rn;

-- ---------------------------------------------------------------------
-- 6. Hitung saldo awal = total setoran aktif - total penarikan aktif
-- ---------------------------------------------------------------------
UPDATE Nasabah
SET Saldo = (SELECT IFNULL(SUM(s.Total), 0) FROM Setoran s
             WHERE s.NasabahId = Nasabah.NasabahId AND s.IsCancelled = 0)
          - (SELECT IFNULL(SUM(p.Jumlah), 0) FROM Penarikan p
             WHERE p.NasabahId = Nasabah.NasabahId AND p.IsCancelled = 0);

-- ---------------------------------------------------------------------
-- 7. Aktivitas terbaru (transaksi 3 hari terakhir + contoh pembatalan)
-- ---------------------------------------------------------------------
INSERT INTO Aktivitas (UserId, JenisAksi, Entitas, EntitasId, Keterangan, Waktu)
SELECT s.UserId, 'Setoran', 'Transaksi', s.SetoranId,
       'Setoran ' || s.NoTransaksi || ' · ' || n.NamaLengkap || ' · ' || j.NamaJenis || ' '
           || replace(printf('%.2f', s.BeratKg), '.', ',') || ' kg · Rp'
           || replace(printf('%,d', s.Total), ',', '.'),
       s.Tanggal
FROM Setoran s
JOIN Nasabah n ON n.NasabahId = s.NasabahId
JOIN JenisSampah j ON j.JenisId = s.JenisId
WHERE s.IsCancelled = 0 AND s.Tanggal >= datetime('now', 'localtime', '-3 days');

INSERT INTO Aktivitas (UserId, JenisAksi, Entitas, EntitasId, Keterangan, Waktu)
SELECT p.UserId, 'Penarikan', 'Transaksi', p.PenarikanId,
       'Penarikan ' || p.NoTransaksi || ' · ' || n.NamaLengkap || ' · Rp'
           || replace(printf('%,d', p.Jumlah), ',', '.'),
       p.Tanggal
FROM Penarikan p
JOIN Nasabah n ON n.NasabahId = p.NasabahId
WHERE p.Tanggal >= datetime('now', 'localtime', '-3 days');

INSERT INTO Aktivitas (UserId, JenisAksi, Entitas, EntitasId, Keterangan, Waktu)
SELECT (SELECT UserId FROM Users WHERE Username = 'admin'), 'Batal', 'Transaksi', s.SetoranId,
       'Membatalkan setoran ' || s.NoTransaksi || ' (salah input berat)',
       s.CancelledAt
FROM Setoran s
WHERE s.IsCancelled = 1;
