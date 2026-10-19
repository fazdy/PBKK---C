using Microsoft.Data.Sqlite;
using SIBASA.Data;
using SIBASA.Helpers;
using SIBASA.Models;

namespace SIBASA.Repositories;

/// <summary>
/// Akses data transaksi Setoran dan Penarikan. Setiap operasi yang mengubah saldo dijalankan
/// di dalam SATU transaksi database: simpan transaksi + ubah saldo + catat aktivitas.
/// Bila salah satu langkah gagal, semuanya dibatalkan (rollback) sehingga saldo selalu konsisten.
/// </summary>
public sealed class TransaksiRepository : RepositoryBase
{
    public TransaksiRepository(SqliteConnectionFactory factory) : base(factory)
    {
    }

    public Task<Setoran> SimpanSetoranAsync(Setoran setoran) =>
        InTransactionAsync(async (c, tx) =>
        {
            // 1. Periksa ulang nasabah dan jenis sampah di dalam transaksi.
            var nasabah = await QuerySingleAsync(c, tx,
                "SELECT NamaLengkap, IsActive FROM Nasabah WHERE NasabahId = $id;",
                r => new InfoNasabah(r.Str("NamaLengkap"), r.Bool("IsActive"), 0),
                ("$id", setoran.NasabahId))
                ?? throw new AppException("Nasabah tidak ditemukan.");
            if (!nasabah.IsActive)
            {
                throw new AppException("Nasabah tidak aktif sehingga tidak dapat melakukan setoran.");
            }

            var namaJenis = await QuerySingleAsync(c, tx,
                "SELECT NamaJenis FROM JenisSampah WHERE JenisId = $id AND IsActive = 1;",
                r => r.Str("NamaJenis"),
                ("$id", setoran.JenisId))
                ?? throw new AppException("Jenis sampah tidak ditemukan atau sudah dinonaktifkan.");

            // 2. Simpan setoran. Nomor transaksi dibuat dari nomor urut tabel (sqlite_sequence).
            await using (var command = Command(c, tx,
                """
                INSERT INTO Setoran (NoTransaksi, NasabahId, JenisId, BeratKg, HargaPerKg, Total, Tanggal, UserId, Catatan)
                VALUES (
                    'STR-' || strftime('%Y%m%d', $tanggal) || '-'
                        || printf('%05d', IFNULL((SELECT seq FROM sqlite_sequence WHERE name = 'Setoran'), 0) + 1),
                    $nasabah, $jenis, $berat, $harga, $total, $tanggal, $user, $catatan)
                RETURNING SetoranId, NoTransaksi;
                """,
                ("$tanggal", DbDate.ToDb(setoran.Tanggal)),
                ("$nasabah", setoran.NasabahId),
                ("$jenis", setoran.JenisId),
                ("$berat", setoran.BeratKg),
                ("$harga", setoran.HargaPerKg),
                ("$total", setoran.Total),
                ("$user", setoran.UserId),
                ("$catatan", setoran.Catatan)))
            await using (var reader = await command.ExecuteReaderAsync())
            {
                await reader.ReadAsync();
                setoran.SetoranId = reader.Int("SetoranId");
                setoran.NoTransaksi = reader.Str("NoTransaksi");
            }

            // 3. Tambahkan nilai setoran ke saldo nasabah.
            await ExecuteAsync(c, tx,
                "UPDATE Nasabah SET Saldo = Saldo + $total WHERE NasabahId = $id;",
                ("$total", setoran.Total), ("$id", setoran.NasabahId));

            // 4. Catat aktivitas di transaksi yang sama.
            setoran.NamaNasabah = nasabah.Nama;
            setoran.NamaJenis = namaJenis;
            await AktivitasRepository.InsertAsync(c, tx, Aktivitas.Buat(
                setoran.UserId, "Setoran", "Transaksi", setoran.SetoranId,
                $"Setoran {setoran.NoTransaksi} · {setoran.NamaNasabah} · {setoran.NamaJenis} " +
                $"{Format.Kg(setoran.BeratKg)} · {Format.Rupiah(setoran.Total)}"));

            return setoran;
        });

    public Task<Penarikan> SimpanPenarikanAsync(Penarikan penarikan) =>
        InTransactionAsync(async (c, tx) =>
        {
            var nasabah = await GetInfoNasabahAsync(c, tx, penarikan.NasabahId)
                ?? throw new AppException("Nasabah tidak ditemukan.");
            if (!nasabah.IsActive)
            {
                throw new AppException("Nasabah tidak aktif sehingga tidak dapat menarik saldo.");
            }

            if (nasabah.Saldo < penarikan.Jumlah)
            {
                throw new AppException($"Saldo tidak cukup. Saldo tersedia {Format.Rupiah(nasabah.Saldo)}.");
            }

            // Kurangi saldo dengan syarat saldo mencukupi (pengaman ganda selain CHECK Saldo >= 0).
            var berubah = await ExecuteAsync(c, tx,
                "UPDATE Nasabah SET Saldo = Saldo - $jumlah WHERE NasabahId = $id AND Saldo >= $jumlah;",
                ("$jumlah", penarikan.Jumlah), ("$id", penarikan.NasabahId));
            if (berubah == 0)
            {
                throw new AppException("Saldo tidak cukup untuk penarikan ini.");
            }

            await using (var command = Command(c, tx,
                """
                INSERT INTO Penarikan (NoTransaksi, NasabahId, Jumlah, Tanggal, UserId, Catatan)
                VALUES (
                    'TRK-' || strftime('%Y%m%d', $tanggal) || '-'
                        || printf('%05d', IFNULL((SELECT seq FROM sqlite_sequence WHERE name = 'Penarikan'), 0) + 1),
                    $nasabah, $jumlah, $tanggal, $user, $catatan)
                RETURNING PenarikanId, NoTransaksi;
                """,
                ("$tanggal", DbDate.ToDb(penarikan.Tanggal)),
                ("$nasabah", penarikan.NasabahId),
                ("$jumlah", penarikan.Jumlah),
                ("$user", penarikan.UserId),
                ("$catatan", penarikan.Catatan)))
            await using (var reader = await command.ExecuteReaderAsync())
            {
                await reader.ReadAsync();
                penarikan.PenarikanId = reader.Int("PenarikanId");
                penarikan.NoTransaksi = reader.Str("NoTransaksi");
            }

            penarikan.NamaNasabah = nasabah.Nama;
            await AktivitasRepository.InsertAsync(c, tx, Aktivitas.Buat(
                penarikan.UserId, "Penarikan", "Transaksi", penarikan.PenarikanId,
                $"Penarikan {penarikan.NoTransaksi} · {penarikan.NamaNasabah} · {Format.Rupiah(penarikan.Jumlah)}"));

            return penarikan;
        });

    /// <summary>
    /// Membatalkan transaksi (soft cancel): status menjadi "Dibatalkan" dan saldo dikoreksi.
    /// Mengembalikan nomor transaksi yang dibatalkan.
    /// </summary>
    public Task<string> BatalkanAsync(JenisTransaksi jenis, int transaksiId, int userId) =>
        InTransactionAsync(async (c, tx) =>
        {
            var sekarang = DbDate.ToDb(DateTime.Now);
            if (jenis == JenisTransaksi.Setoran)
            {
                var data = await QuerySingleAsync(c, tx,
                    """
                    SELECT s.NasabahId, s.Total AS Nominal, s.IsCancelled, s.NoTransaksi, n.NamaLengkap, n.Saldo
                    FROM Setoran s JOIN Nasabah n ON n.NasabahId = s.NasabahId
                    WHERE s.SetoranId = $id;
                    """,
                    MapBatal, ("$id", transaksiId))
                    ?? throw new AppException("Transaksi tidak ditemukan.");
                if (data.IsCancelled)
                {
                    throw new AppException($"Transaksi {data.NoTransaksi} sudah dibatalkan sebelumnya.");
                }

                // Membatalkan setoran berarti mengurangi saldo. Tolak bila saldo akan minus.
                var berubah = await ExecuteAsync(c, tx,
                    "UPDATE Nasabah SET Saldo = Saldo - $nominal WHERE NasabahId = $id AND Saldo >= $nominal;",
                    ("$nominal", data.Nominal), ("$id", data.NasabahId));
                if (berubah == 0)
                {
                    throw new AppException(
                        $"Setoran {data.NoTransaksi} tidak dapat dibatalkan karena saldo {data.NamaNasabah} tinggal " +
                        $"{Format.Rupiah(data.Saldo)}, kurang dari nilai setoran {Format.Rupiah(data.Nominal)} " +
                        "(sebagian saldo sudah ditarik).");
                }

                await ExecuteAsync(c, tx,
                    "UPDATE Setoran SET IsCancelled = 1, CancelledAt = $waktu WHERE SetoranId = $id;",
                    ("$waktu", sekarang), ("$id", transaksiId));

                await AktivitasRepository.InsertAsync(c, tx, Aktivitas.Buat(
                    userId, "Batal", "Transaksi", transaksiId,
                    $"Membatalkan setoran {data.NoTransaksi} · {data.NamaNasabah} · {Format.Rupiah(data.Nominal)}"));
                return data.NoTransaksi;
            }
            else
            {
                var data = await QuerySingleAsync(c, tx,
                    """
                    SELECT p.NasabahId, p.Jumlah AS Nominal, p.IsCancelled, p.NoTransaksi, n.NamaLengkap, n.Saldo
                    FROM Penarikan p JOIN Nasabah n ON n.NasabahId = p.NasabahId
                    WHERE p.PenarikanId = $id;
                    """,
                    MapBatal, ("$id", transaksiId))
                    ?? throw new AppException("Transaksi tidak ditemukan.");
                if (data.IsCancelled)
                {
                    throw new AppException($"Transaksi {data.NoTransaksi} sudah dibatalkan sebelumnya.");
                }

                // Membatalkan penarikan berarti mengembalikan uang ke saldo nasabah.
                await ExecuteAsync(c, tx,
                    "UPDATE Nasabah SET Saldo = Saldo + $nominal WHERE NasabahId = $id;",
                    ("$nominal", data.Nominal), ("$id", data.NasabahId));
                await ExecuteAsync(c, tx,
                    "UPDATE Penarikan SET IsCancelled = 1, CancelledAt = $waktu WHERE PenarikanId = $id;",
                    ("$waktu", sekarang), ("$id", transaksiId));

                await AktivitasRepository.InsertAsync(c, tx, Aktivitas.Buat(
                    userId, "Batal", "Transaksi", transaksiId,
                    $"Membatalkan penarikan {data.NoTransaksi} · {data.NamaNasabah} · {Format.Rupiah(data.Nominal)}"));
                return data.NoTransaksi;
            }
        });

    /// <summary>Gabungan setoran dan penarikan sesuai filter, terbaru di atas.</summary>
    public Task<List<RiwayatTransaksi>> GetRiwayatAsync(RiwayatFilter filter, int batas = 2000) =>
        RunAsync(c => QueryAsync(c, null,
            """
            SELECT * FROM (
                SELECT 'Setoran' AS Jenis, s.SetoranId AS Id, s.NoTransaksi, s.Tanggal, s.NasabahId,
                       n.NoRekening, n.NamaLengkap AS NamaNasabah, j.NamaJenis AS NamaJenisSampah,
                       s.BeratKg, s.HargaPerKg, s.Total AS Nominal, u.NamaLengkap AS Petugas,
                       s.Catatan, s.IsCancelled
                FROM Setoran s
                JOIN Nasabah n ON n.NasabahId = s.NasabahId
                JOIN JenisSampah j ON j.JenisId = s.JenisId
                JOIN Users u ON u.UserId = s.UserId
                UNION ALL
                SELECT 'Penarikan', p.PenarikanId, p.NoTransaksi, p.Tanggal, p.NasabahId,
                       n.NoRekening, n.NamaLengkap, NULL, NULL, NULL, p.Jumlah, u.NamaLengkap,
                       p.Catatan, p.IsCancelled
                FROM Penarikan p
                JOIN Nasabah n ON n.NasabahId = p.NasabahId
                JOIN Users u ON u.UserId = p.UserId
            ) t
            WHERE t.Tanggal >= $dari AND t.Tanggal < $sampai
              AND ($jenis IS NULL OR t.Jenis = $jenis)
              AND ($kata IS NULL
                   OR t.NamaNasabah LIKE $kata ESCAPE '\'
                   OR t.NoRekening LIKE $kata ESCAPE '\'
                   OR t.NoTransaksi LIKE $kata ESCAPE '\')
              AND ($batal = 1 OR t.IsCancelled = 0)
            ORDER BY t.Tanggal DESC, t.Id DESC
            LIMIT $batas;
            """,
            MapRiwayat,
            ("$dari", DbDate.ToDb(filter.Dari.Date)),
            ("$sampai", DbDate.ToDb(filter.Sampai.Date.AddDays(1))),
            ("$jenis", filter.Jenis?.ToString()),
            ("$kata", LikePattern(filter.KataKunci)),
            ("$batal", filter.TampilkanBatal ? 1 : 0),
            ("$batas", batas)));

    private static Task<InfoNasabah?> GetInfoNasabahAsync(SqliteConnection c, SqliteTransaction tx, int nasabahId) =>
        QuerySingleAsync(c, tx,
            "SELECT NamaLengkap, IsActive, Saldo FROM Nasabah WHERE NasabahId = $id;",
            r => new InfoNasabah(r.Str("NamaLengkap"), r.Bool("IsActive"), r.Long("Saldo")),
            ("$id", nasabahId));

    private static DataBatal MapBatal(SqliteDataReader r) => new(
        r.Int("NasabahId"), r.Long("Nominal"), r.Bool("IsCancelled"),
        r.Str("NoTransaksi"), r.Str("NamaLengkap"), r.Long("Saldo"));

    private static RiwayatTransaksi MapRiwayat(SqliteDataReader r) => new()
    {
        Jenis = r.Str("Jenis") == "Setoran" ? JenisTransaksi.Setoran : JenisTransaksi.Penarikan,
        Id = r.Int("Id"),
        NoTransaksi = r.Str("NoTransaksi"),
        Tanggal = r.Date("Tanggal"),
        NasabahId = r.Int("NasabahId"),
        NoRekening = r.Str("NoRekening"),
        NamaNasabah = r.Str("NamaNasabah"),
        NamaJenisSampah = r.StrOrNull("NamaJenisSampah"),
        BeratKg = r.DblOrNull("BeratKg"),
        HargaPerKg = r.LongOrNull("HargaPerKg"),
        Nominal = r.Long("Nominal"),
        Petugas = r.Str("Petugas"),
        Catatan = r.StrOrNull("Catatan"),
        IsCancelled = r.Bool("IsCancelled")
    };

    private sealed record InfoNasabah(string Nama, bool IsActive, long Saldo);

    private sealed record DataBatal(int NasabahId, long Nominal, bool IsCancelled, string NoTransaksi, string NamaNasabah, long Saldo);
}
