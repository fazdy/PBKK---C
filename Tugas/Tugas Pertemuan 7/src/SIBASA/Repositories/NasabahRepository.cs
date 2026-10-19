using Microsoft.Data.Sqlite;
using SIBASA.Data;
using SIBASA.Models;

namespace SIBASA.Repositories;

/// <summary>Akses data tabel Nasabah.</summary>
public sealed class NasabahRepository : RepositoryBase
{
    private const string SelectColumns = """
        n.NasabahId, n.NoRekening, n.NamaLengkap, n.NoHp, n.Alamat, n.RT, n.RW, n.Saldo, n.IsActive, n.TanggalDaftar,
        (SELECT COUNT(*) FROM Setoran s WHERE s.NasabahId = n.NasabahId)
          + (SELECT COUNT(*) FROM Penarikan p WHERE p.NasabahId = n.NasabahId) AS JumlahTransaksi
        """;

    public NasabahRepository(SqliteConnectionFactory factory) : base(factory)
    {
    }

    /// <summary>Daftar nasabah sesuai filter. Pencarian nama/no. rekening tidak peka huruf besar/kecil.</summary>
    public Task<List<Nasabah>> GetAllAsync(NasabahFilter filter) =>
        RunAsync(c => QueryAsync(c, null,
            $"""
            SELECT {SelectColumns}
            FROM Nasabah n
            WHERE ($kata IS NULL OR n.NamaLengkap LIKE $kata ESCAPE '\' OR n.NoRekening LIKE $kata ESCAPE '\')
              AND ($rw IS NULL OR n.RW = $rw)
              AND ($aktif IS NULL OR n.IsActive = $aktif)
            ORDER BY n.NamaLengkap COLLATE NOCASE;
            """,
            Map,
            ("$kata", LikePattern(filter.KataKunci)),
            ("$rw", filter.RW),
            ("$aktif", filter.IsActive is null ? null : filter.IsActive.Value ? 1 : 0)));

    /// <summary>Nasabah aktif untuk dipilih di halaman Transaksi.</summary>
    public Task<List<Nasabah>> GetAktifAsync(string? kataKunci) =>
        GetAllAsync(new NasabahFilter(kataKunci, null, true));

    public Task<Nasabah?> GetByIdAsync(int nasabahId) =>
        RunAsync(c => QuerySingleAsync(c, null,
            $"SELECT {SelectColumns} FROM Nasabah n WHERE n.NasabahId = $id;",
            Map, ("$id", nasabahId)));

    public Task<List<string>> GetRwListAsync() =>
        RunAsync(c => QueryAsync(c, null,
            "SELECT DISTINCT RW FROM Nasabah ORDER BY RW;",
            r => r.Str("RW")));

    /// <summary>
    /// Menyimpan nasabah baru. Nomor rekening dibuat otomatis dari nomor urut tabel
    /// (sqlite_sequence) sehingga nomor tidak pernah dipakai ulang walau ada data yang dihapus.
    /// </summary>
    public Task<(int Id, string NoRekening)> InsertAsync(Nasabah nasabah) =>
        RunAsync(async c =>
        {
            await using var command = Command(c, null,
                """
                INSERT INTO Nasabah (NoRekening, NamaLengkap, NoHp, Alamat, RT, RW, Saldo, IsActive, TanggalDaftar)
                VALUES (
                    printf('BS-%04d', IFNULL((SELECT seq FROM sqlite_sequence WHERE name = 'Nasabah'), 0) + 1),
                    $nama, $hp, $alamat, $rt, $rw, 0, $aktif, $tanggal)
                RETURNING NasabahId, NoRekening;
                """,
                ("$nama", nasabah.NamaLengkap),
                ("$hp", nasabah.NoHp),
                ("$alamat", nasabah.Alamat),
                ("$rt", nasabah.RT),
                ("$rw", nasabah.RW),
                ("$aktif", nasabah.IsActive ? 1 : 0),
                ("$tanggal", DbDate.ToDb(DateTime.Now)));
            await using var reader = await command.ExecuteReaderAsync();
            await reader.ReadAsync();
            return (reader.Int("NasabahId"), reader.Str("NoRekening"));
        });

    /// <summary>Mengubah data profil nasabah. Nomor rekening dan saldo sengaja tidak ikut diubah.</summary>
    public Task<int> UpdateAsync(Nasabah nasabah) =>
        RunAsync(c => ExecuteAsync(c, null,
            """
            UPDATE Nasabah
            SET NamaLengkap = $nama, NoHp = $hp, Alamat = $alamat, RT = $rt, RW = $rw, IsActive = $aktif
            WHERE NasabahId = $id;
            """,
            ("$nama", nasabah.NamaLengkap),
            ("$hp", nasabah.NoHp),
            ("$alamat", nasabah.Alamat),
            ("$rt", nasabah.RT),
            ("$rw", nasabah.RW),
            ("$aktif", nasabah.IsActive ? 1 : 0),
            ("$id", nasabah.NasabahId)));

    public Task<int> SetActiveAsync(int nasabahId, bool isActive) =>
        RunAsync(c => ExecuteAsync(c, null,
            "UPDATE Nasabah SET IsActive = $aktif WHERE NasabahId = $id;",
            ("$aktif", isActive ? 1 : 0), ("$id", nasabahId)));

    public Task<int> DeleteAsync(int nasabahId) =>
        RunAsync(c => ExecuteAsync(c, null,
            "DELETE FROM Nasabah WHERE NasabahId = $id;",
            ("$id", nasabahId)));

    public Task<bool> HasTransactionsAsync(int nasabahId) =>
        RunAsync(async c => await ScalarLongAsync(c, null,
            """
            SELECT (SELECT COUNT(*) FROM Setoran WHERE NasabahId = $id)
                 + (SELECT COUNT(*) FROM Penarikan WHERE NasabahId = $id);
            """,
            ("$id", nasabahId)) > 0);

    private static Nasabah Map(SqliteDataReader r) => new()
    {
        NasabahId = r.Int("NasabahId"),
        NoRekening = r.Str("NoRekening"),
        NamaLengkap = r.Str("NamaLengkap"),
        NoHp = r.StrOrNull("NoHp"),
        Alamat = r.StrOrNull("Alamat"),
        RT = r.Str("RT"),
        RW = r.Str("RW"),
        Saldo = r.Long("Saldo"),
        IsActive = r.Bool("IsActive"),
        TanggalDaftar = r.Date("TanggalDaftar"),
        JumlahTransaksi = r.Int("JumlahTransaksi")
    };
}
