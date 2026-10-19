-- =====================================================================
-- SIBASA - Sistem Informasi Bank Sampah
-- 02_create_tables.sql : pembuatan tabel, relasi, constraint, dan indeks
-- ---------------------------------------------------------------------
-- Konvensi:
--   * Tanggal disimpan sebagai TEXT berformat 'yyyy-MM-dd HH:mm:ss' (waktu lokal).
--   * Nilai uang disimpan sebagai INTEGER dalam rupiah (tanpa sen).
--   * Boolean disimpan sebagai INTEGER 0/1.
-- Aplikasi menjalankan skrip ini otomatis di dalam satu transaksi.
-- =====================================================================

-- Akun pengguna aplikasi (Admin dan Petugas).
CREATE TABLE IF NOT EXISTS Users (
    UserId        INTEGER PRIMARY KEY AUTOINCREMENT,
    Username      TEXT    NOT NULL COLLATE NOCASE UNIQUE,
    PasswordHash  TEXT    NOT NULL,                          -- PBKDF2-SHA256$iterasi$salt$hash
    NamaLengkap   TEXT    NOT NULL,
    Role          TEXT    NOT NULL CHECK (Role IN ('Admin', 'Petugas')),
    IsActive      INTEGER NOT NULL DEFAULT 1 CHECK (IsActive IN (0, 1)),
    CreatedAt     TEXT    NOT NULL DEFAULT (datetime('now', 'localtime')),
    LastLoginAt   TEXT
);

-- Warga yang menabung sampah.
CREATE TABLE IF NOT EXISTS Nasabah (
    NasabahId     INTEGER PRIMARY KEY AUTOINCREMENT,
    NoRekening    TEXT    NOT NULL UNIQUE,                   -- format BS-0001
    NamaLengkap   TEXT    NOT NULL CHECK (length(NamaLengkap) BETWEEN 3 AND 100),
    NoHp          TEXT,
    Alamat        TEXT    CHECK (Alamat IS NULL OR length(Alamat) <= 200),
    RT            TEXT    NOT NULL,
    RW            TEXT    NOT NULL,
    Saldo         INTEGER NOT NULL DEFAULT 0 CHECK (Saldo >= 0),   -- saldo tidak boleh minus
    IsActive      INTEGER NOT NULL DEFAULT 1 CHECK (IsActive IN (0, 1)),
    TanggalDaftar TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);

-- Kelompok jenis sampah (Plastik, Kertas, Logam, ...).
CREATE TABLE IF NOT EXISTS KategoriSampah (
    KategoriId    INTEGER PRIMARY KEY AUTOINCREMENT,
    NamaKategori  TEXT    NOT NULL COLLATE NOCASE UNIQUE CHECK (length(NamaKategori) BETWEEN 2 AND 40),
    Keterangan    TEXT    CHECK (Keterangan IS NULL OR length(Keterangan) <= 150)
);

-- Jenis sampah beserta harga per kilogram.
CREATE TABLE IF NOT EXISTS JenisSampah (
    JenisId       INTEGER PRIMARY KEY AUTOINCREMENT,
    KategoriId    INTEGER NOT NULL REFERENCES KategoriSampah (KategoriId) ON DELETE RESTRICT,
    NamaJenis     TEXT    NOT NULL COLLATE NOCASE UNIQUE CHECK (length(NamaJenis) BETWEEN 2 AND 60),
    HargaPerKg    INTEGER NOT NULL CHECK (HargaPerKg BETWEEN 1 AND 1000000),
    IsActive      INTEGER NOT NULL DEFAULT 1 CHECK (IsActive IN (0, 1))
);

-- Transaksi setor sampah. Harga disalin agar perubahan harga tidak mengubah transaksi lama.
CREATE TABLE IF NOT EXISTS Setoran (
    SetoranId     INTEGER PRIMARY KEY AUTOINCREMENT,
    NoTransaksi   TEXT    NOT NULL UNIQUE,                   -- format STR-yyyyMMdd-00001
    NasabahId     INTEGER NOT NULL REFERENCES Nasabah (NasabahId) ON DELETE RESTRICT,
    JenisId       INTEGER NOT NULL REFERENCES JenisSampah (JenisId) ON DELETE RESTRICT,
    BeratKg       REAL    NOT NULL CHECK (BeratKg > 0 AND BeratKg <= 1000),
    HargaPerKg    INTEGER NOT NULL CHECK (HargaPerKg > 0),
    Total         INTEGER NOT NULL CHECK (Total >= 0),       -- ROUND(BeratKg x HargaPerKg)
    Tanggal       TEXT    NOT NULL,
    UserId        INTEGER NOT NULL REFERENCES Users (UserId) ON DELETE RESTRICT,
    Catatan       TEXT,
    IsCancelled   INTEGER NOT NULL DEFAULT 0 CHECK (IsCancelled IN (0, 1)),
    CancelledAt   TEXT
);

-- Transaksi tarik saldo.
CREATE TABLE IF NOT EXISTS Penarikan (
    PenarikanId   INTEGER PRIMARY KEY AUTOINCREMENT,
    NoTransaksi   TEXT    NOT NULL UNIQUE,                   -- format TRK-yyyyMMdd-00001
    NasabahId     INTEGER NOT NULL REFERENCES Nasabah (NasabahId) ON DELETE RESTRICT,
    Jumlah        INTEGER NOT NULL CHECK (Jumlah > 0),
    Tanggal       TEXT    NOT NULL,
    UserId        INTEGER NOT NULL REFERENCES Users (UserId) ON DELETE RESTRICT,
    Catatan       TEXT,
    IsCancelled   INTEGER NOT NULL DEFAULT 0 CHECK (IsCancelled IN (0, 1)),
    CancelledAt   TEXT
);

-- Catatan aktivitas pengguna untuk panel "Aktivitas terbaru" (tanpa data rahasia).
CREATE TABLE IF NOT EXISTS Aktivitas (
    AktivitasId   INTEGER PRIMARY KEY AUTOINCREMENT,
    UserId        INTEGER REFERENCES Users (UserId) ON DELETE SET NULL,
    JenisAksi     TEXT    NOT NULL,      -- Login, Logout, Tambah, Ubah, Hapus, Setoran, Penarikan, Batal
    Entitas       TEXT    NOT NULL,      -- Nasabah, JenisSampah, Kategori, Pengguna, Transaksi, Sesi
    EntitasId     INTEGER,
    Keterangan    TEXT    NOT NULL,
    Waktu         TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);

-- Indeks untuk mempercepat pencarian, filter tanggal, dan agregasi dashboard.
CREATE INDEX IF NOT EXISTS IX_Nasabah_Nama       ON Nasabah (NamaLengkap COLLATE NOCASE);
CREATE INDEX IF NOT EXISTS IX_JenisSampah_Kat    ON JenisSampah (KategoriId);
CREATE INDEX IF NOT EXISTS IX_Setoran_Tanggal    ON Setoran (Tanggal);
CREATE INDEX IF NOT EXISTS IX_Setoran_Nasabah    ON Setoran (NasabahId);
CREATE INDEX IF NOT EXISTS IX_Setoran_Jenis      ON Setoran (JenisId);
CREATE INDEX IF NOT EXISTS IX_Penarikan_Tanggal  ON Penarikan (Tanggal);
CREATE INDEX IF NOT EXISTS IX_Penarikan_Nasabah  ON Penarikan (NasabahId);
CREATE INDEX IF NOT EXISTS IX_Aktivitas_Waktu    ON Aktivitas (Waktu);

-- Versi skema. DbInitializer memakai angka ini untuk mengenali database yang kompatibel.
PRAGMA user_version = 1;
