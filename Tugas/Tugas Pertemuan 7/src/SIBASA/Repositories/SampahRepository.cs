using Microsoft.Data.Sqlite;
using SIBASA.Data;
using SIBASA.Models;

namespace SIBASA.Repositories;

/// <summary>Akses data tabel KategoriSampah.</summary>
public sealed class KategoriSampahRepository : RepositoryBase
{
    public KategoriSampahRepository(SqliteConnectionFactory factory) : base(factory)
    {
    }

    public Task<List<KategoriSampah>> GetAllAsync() =>
        RunAsync(c => QueryAsync(c, null,
            """
            SELECT k.KategoriId, k.NamaKategori, k.Keterangan,
                   (SELECT COUNT(*) FROM JenisSampah j WHERE j.KategoriId = k.KategoriId) AS JumlahJenis
            FROM KategoriSampah k
            ORDER BY k.KategoriId;
            """,
            Map));

    public Task<bool> NameExistsAsync(string nama, int? excludeId) =>
        RunAsync(async c => await ScalarLongAsync(c, null,
            "SELECT COUNT(*) FROM KategoriSampah WHERE NamaKategori = $nama AND ($exclude IS NULL OR KategoriId <> $exclude);",
            ("$nama", nama.Trim()), ("$exclude", excludeId)) > 0);

    public Task<int> InsertAsync(KategoriSampah kategori) =>
        RunAsync(async c => (int)await ScalarLongAsync(c, null,
            "INSERT INTO KategoriSampah (NamaKategori, Keterangan) VALUES ($nama, $ket) RETURNING KategoriId;",
            ("$nama", kategori.NamaKategori), ("$ket", kategori.Keterangan)));

    public Task<int> UpdateAsync(KategoriSampah kategori) =>
        RunAsync(c => ExecuteAsync(c, null,
            "UPDATE KategoriSampah SET NamaKategori = $nama, Keterangan = $ket WHERE KategoriId = $id;",
            ("$nama", kategori.NamaKategori), ("$ket", kategori.Keterangan), ("$id", kategori.KategoriId)));

    public Task<bool> HasJenisAsync(int kategoriId) =>
        RunAsync(async c => await ScalarLongAsync(c, null,
            "SELECT COUNT(*) FROM JenisSampah WHERE KategoriId = $id;",
            ("$id", kategoriId)) > 0);

    public Task<int> DeleteAsync(int kategoriId) =>
        RunAsync(c => ExecuteAsync(c, null,
            "DELETE FROM KategoriSampah WHERE KategoriId = $id;",
            ("$id", kategoriId)));

    private static KategoriSampah Map(SqliteDataReader r) => new()
    {
        KategoriId = r.Int("KategoriId"),
        NamaKategori = r.Str("NamaKategori"),
        Keterangan = r.StrOrNull("Keterangan"),
        JumlahJenis = r.Int("JumlahJenis")
    };
}

/// <summary>Akses data tabel JenisSampah.</summary>
public sealed class JenisSampahRepository : RepositoryBase
{
    private const string SelectFrom = """
        SELECT j.JenisId, j.KategoriId, k.NamaKategori, j.NamaJenis, j.HargaPerKg, j.IsActive,
               (SELECT COUNT(*) FROM Setoran s WHERE s.JenisId = j.JenisId) AS JumlahPemakaian
        FROM JenisSampah j
        JOIN KategoriSampah k ON k.KategoriId = j.KategoriId
        """;

    public JenisSampahRepository(SqliteConnectionFactory factory) : base(factory)
    {
    }

    public Task<List<JenisSampah>> GetAllAsync(JenisSampahFilter filter) =>
        RunAsync(c => QueryAsync(c, null,
            $"""
            {SelectFrom}
            WHERE ($kata IS NULL OR j.NamaJenis LIKE $kata ESCAPE '\')
              AND ($kategori IS NULL OR j.KategoriId = $kategori)
            ORDER BY k.KategoriId, j.NamaJenis COLLATE NOCASE;
            """,
            Map,
            ("$kata", LikePattern(filter.KataKunci)),
            ("$kategori", filter.KategoriId)));

    /// <summary>Jenis sampah aktif untuk pilihan di form setoran.</summary>
    public Task<List<JenisSampah>> GetAktifAsync() =>
        RunAsync(c => QueryAsync(c, null,
            $"{SelectFrom} WHERE j.IsActive = 1 ORDER BY k.KategoriId, j.NamaJenis COLLATE NOCASE;",
            Map));

    public Task<JenisSampah?> GetByIdAsync(int jenisId) =>
        RunAsync(c => QuerySingleAsync(c, null,
            $"{SelectFrom} WHERE j.JenisId = $id;",
            Map, ("$id", jenisId)));

    public Task<bool> NameExistsAsync(string nama, int? excludeId) =>
        RunAsync(async c => await ScalarLongAsync(c, null,
            "SELECT COUNT(*) FROM JenisSampah WHERE NamaJenis = $nama AND ($exclude IS NULL OR JenisId <> $exclude);",
            ("$nama", nama.Trim()), ("$exclude", excludeId)) > 0);

    public Task<int> InsertAsync(JenisSampah jenis) =>
        RunAsync(async c => (int)await ScalarLongAsync(c, null,
            """
            INSERT INTO JenisSampah (KategoriId, NamaJenis, HargaPerKg, IsActive)
            VALUES ($kategori, $nama, $harga, $aktif)
            RETURNING JenisId;
            """,
            ("$kategori", jenis.KategoriId),
            ("$nama", jenis.NamaJenis),
            ("$harga", jenis.HargaPerKg),
            ("$aktif", jenis.IsActive ? 1 : 0)));

    public Task<int> UpdateAsync(JenisSampah jenis) =>
        RunAsync(c => ExecuteAsync(c, null,
            """
            UPDATE JenisSampah
            SET KategoriId = $kategori, NamaJenis = $nama, HargaPerKg = $harga, IsActive = $aktif
            WHERE JenisId = $id;
            """,
            ("$kategori", jenis.KategoriId),
            ("$nama", jenis.NamaJenis),
            ("$harga", jenis.HargaPerKg),
            ("$aktif", jenis.IsActive ? 1 : 0),
            ("$id", jenis.JenisId)));

    public Task<bool> IsUsedAsync(int jenisId) =>
        RunAsync(async c => await ScalarLongAsync(c, null,
            "SELECT COUNT(*) FROM Setoran WHERE JenisId = $id;",
            ("$id", jenisId)) > 0);

    public Task<int> DeleteAsync(int jenisId) =>
        RunAsync(c => ExecuteAsync(c, null,
            "DELETE FROM JenisSampah WHERE JenisId = $id;",
            ("$id", jenisId)));

    private static JenisSampah Map(SqliteDataReader r) => new()
    {
        JenisId = r.Int("JenisId"),
        KategoriId = r.Int("KategoriId"),
        NamaKategori = r.Str("NamaKategori"),
        NamaJenis = r.Str("NamaJenis"),
        HargaPerKg = r.Long("HargaPerKg"),
        IsActive = r.Bool("IsActive"),
        JumlahPemakaian = r.Int("JumlahPemakaian")
    };
}
