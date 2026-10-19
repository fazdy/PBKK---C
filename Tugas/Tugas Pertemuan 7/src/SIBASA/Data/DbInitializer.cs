using Microsoft.Data.Sqlite;
using SIBASA.Helpers;
using SIBASA.Services;

namespace SIBASA.Data;

/// <summary>
/// Menyiapkan database saat aplikasi dibuka. Bila file database belum ada atau masih kosong,
/// skrip SQL dari folder database/ (di-embed ke aplikasi) dijalankan berurutan:
///   01_create_database.sql -> 02_create_tables.sql -> 03_seed_data.sql (opsional).
/// </summary>
public sealed class DbInitializer
{
    /// <summary>Versi skema yang dikenali aplikasi ini (PRAGMA user_version).</summary>
    public const int SchemaVersion = 1;

    private readonly SqliteConnectionFactory _factory;
    private readonly bool _seedSampleData;

    public DbInitializer(SqliteConnectionFactory factory, bool seedSampleData)
    {
        _factory = factory;
        _seedSampleData = seedSampleData;
    }

    public string DatabasePath => _factory.DatabasePath;

    /// <summary>Menyiapkan database. Mengembalikan true bila database baru saja dibuat.</summary>
    public Task<bool> InitializeAsync() => Task.Run(async () =>
    {
        if (!_factory.IsInMemory)
        {
            var folder = Path.GetDirectoryName(_factory.DatabasePath);
            if (!string.IsNullOrEmpty(folder))
            {
                Directory.CreateDirectory(folder);
            }
        }

        await using var connection = _factory.Create();
        await connection.OpenAsync();

        var tabelUsersAda = await ScalarAsync(connection, null,
            "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'Users';") > 0;

        if (tabelUsersAda)
        {
            var versi = await ScalarAsync(connection, null, "PRAGMA user_version;");
            if (versi > SchemaVersion)
            {
                throw new AppException(
                    $"Database dibuat oleh versi SIBASA yang lebih baru (skema v{versi}). Perbarui aplikasi terlebih dahulu.");
            }

            if (versi < SchemaVersion)
            {
                throw new AppException(
                    "Struktur database tidak dikenali. Tutup aplikasi, hapus file database, lalu buka lagi agar dibuat ulang.");
            }

            return false;
        }

        // Database baru: PRAGMA journal_mode tidak boleh di dalam transaksi, jadi skrip 01 dijalankan terpisah.
        await ExecuteScriptAsync(connection, null, "01_create_database.sql");

        await using var transaction = connection.BeginTransaction();
        try
        {
            await ExecuteScriptAsync(connection, transaction, "02_create_tables.sql");
            if (_seedSampleData)
            {
                await ExecuteScriptAsync(connection, transaction, "03_seed_data.sql");
            }

            await EnsureDefaultAdminAsync(connection, transaction);
            await transaction.CommitAsync();
        }
        catch
        {
            await transaction.RollbackAsync();
            throw;
        }

        Log.Info($"Database baru dibuat di {_factory.DatabasePath} (data contoh: {_seedSampleData}).");
        return true;
    });

    /// <summary>
    /// Bila tabel Users masih kosong (data contoh dimatikan), buat satu akun admin awal
    /// agar aplikasi tetap bisa dipakai. Password wajib diganti setelah login pertama.
    /// </summary>
    private static async Task EnsureDefaultAdminAsync(SqliteConnection connection, SqliteTransaction transaction)
    {
        if (await ScalarAsync(connection, transaction, "SELECT COUNT(*) FROM Users;") > 0)
        {
            return;
        }

        await using var command = connection.CreateCommand();
        command.Transaction = transaction;
        command.CommandText = """
            INSERT INTO Users (Username, PasswordHash, NamaLengkap, Role, IsActive, CreatedAt)
            VALUES ($username, $hash, $nama, 'Admin', 1, $waktu);
            """;
        command.Parameters.AddWithValue("$username", "admin");
        command.Parameters.AddWithValue("$hash", PasswordHasher.Hash("Admin#123"));
        command.Parameters.AddWithValue("$nama", "Administrator");
        command.Parameters.AddWithValue("$waktu", DbDate.ToDb(DateTime.Now));
        await command.ExecuteNonQueryAsync();
    }

    private static async Task ExecuteScriptAsync(SqliteConnection connection, SqliteTransaction? transaction, string fileName)
    {
        var sql = ReadEmbeddedScript(fileName);
        await using var command = connection.CreateCommand();
        command.Transaction = transaction;
        command.CommandText = sql; // Microsoft.Data.Sqlite menjalankan semua pernyataan dalam skrip secara berurutan.
        await command.ExecuteNonQueryAsync();
    }

    private static async Task<long> ScalarAsync(SqliteConnection connection, SqliteTransaction? transaction, string sql)
    {
        await using var command = connection.CreateCommand();
        command.Transaction = transaction;
        command.CommandText = sql;
        var hasil = await command.ExecuteScalarAsync();
        return Convert.ToInt64(hasil ?? 0L);
    }

    public static string ReadEmbeddedScript(string fileName)
    {
        var resourceName = "Database." + fileName;
        using var stream = typeof(DbInitializer).Assembly.GetManifestResourceStream(resourceName)
            ?? throw new InvalidOperationException($"Skrip SQL '{fileName}' tidak ditemukan di dalam aplikasi.");
        using var reader = new StreamReader(stream);
        return reader.ReadToEnd();
    }
}
