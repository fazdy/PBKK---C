using Microsoft.Data.Sqlite;
using SIBASA.Data;
using SIBASA.Models;

namespace SIBASA.Repositories;

/// <summary>Akses data tabel Aktivitas (catatan aktivitas pengguna).</summary>
public sealed class AktivitasRepository : RepositoryBase
{
    public AktivitasRepository(SqliteConnectionFactory factory) : base(factory)
    {
    }

    public Task CatatAsync(Aktivitas aktivitas) =>
        RunAsync(c => InsertAsync(c, null, aktivitas));

    public Task<List<Aktivitas>> GetTerbaruAsync(int jumlah) =>
        RunAsync(c => GetTerbaruAsync(c, jumlah));

    /// <summary>Dipakai juga oleh repository lain agar aktivitas tercatat di dalam transaksi yang sama.</summary>
    internal static async Task InsertAsync(SqliteConnection connection, SqliteTransaction? transaction, Aktivitas a)
    {
        await using var command = Command(connection, transaction,
            """
            INSERT INTO Aktivitas (UserId, JenisAksi, Entitas, EntitasId, Keterangan, Waktu)
            VALUES ($user, $aksi, $entitas, $entitasId, $ket, $waktu);
            """,
            ("$user", a.UserId),
            ("$aksi", a.JenisAksi),
            ("$entitas", a.Entitas),
            ("$entitasId", a.EntitasId),
            ("$ket", a.Keterangan),
            ("$waktu", DbDate.ToDb(a.Waktu)));
        await command.ExecuteNonQueryAsync();
    }

    internal static Task<List<Aktivitas>> GetTerbaruAsync(SqliteConnection connection, int jumlah) =>
        QueryAsync(connection, null,
            """
            SELECT a.AktivitasId, a.UserId, IFNULL(u.NamaLengkap, 'Sistem') AS NamaUser,
                   a.JenisAksi, a.Entitas, a.EntitasId, a.Keterangan, a.Waktu
            FROM Aktivitas a
            LEFT JOIN Users u ON u.UserId = a.UserId
            ORDER BY a.Waktu DESC, a.AktivitasId DESC
            LIMIT $jumlah;
            """,
            r => new Aktivitas
            {
                AktivitasId = r.Int("AktivitasId"),
                UserId = r.IntOrNull("UserId"),
                NamaUser = r.Str("NamaUser"),
                JenisAksi = r.Str("JenisAksi"),
                Entitas = r.Str("Entitas"),
                EntitasId = r.IntOrNull("EntitasId"),
                Keterangan = r.Str("Keterangan"),
                Waktu = r.Date("Waktu")
            },
            ("$jumlah", jumlah));
}
