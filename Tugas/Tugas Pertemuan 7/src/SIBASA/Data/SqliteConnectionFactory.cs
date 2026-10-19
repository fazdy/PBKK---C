using Microsoft.Data.Sqlite;
using Microsoft.Extensions.Configuration;
using SIBASA.Helpers;

namespace SIBASA.Data;

/// <summary>
/// Membuat koneksi SQLite dari connection string di appsettings.json.
/// Token {DataDir} pada connection string diganti dengan folder data pengguna (lihat AppPaths).
/// </summary>
public sealed class SqliteConnectionFactory
{
    public const string ConnectionStringName = "SibasaDb";

    public SqliteConnectionFactory(string connectionString)
    {
        var builder = new SqliteConnectionStringBuilder(connectionString);
        if (string.IsNullOrWhiteSpace(builder.DataSource))
        {
            throw new InvalidOperationException("Connection string tidak memiliki 'Data Source'.");
        }

        var isMemory = builder.DataSource == ":memory:" || builder.Mode == SqliteOpenMode.Memory;
        if (!isMemory && !Path.IsPathRooted(builder.DataSource))
        {
            // Path relatif dianggap relatif terhadap folder data pengguna.
            builder.DataSource = Path.Combine(AppPaths.DataDirectory, builder.DataSource);
        }

        ConnectionString = builder.ToString();
        DatabasePath = builder.DataSource;
        IsInMemory = isMemory;
    }

    public string ConnectionString { get; }

    public string DatabasePath { get; }

    public bool IsInMemory { get; }

    public SqliteConnection Create() => new(ConnectionString);

    /// <summary>Membaca connection string "SibasaDb" dari konfigurasi.</summary>
    public static SqliteConnectionFactory FromConfiguration(IConfiguration configuration)
    {
        var raw = configuration.GetConnectionString(ConnectionStringName);
        if (string.IsNullOrWhiteSpace(raw))
        {
            throw new InvalidOperationException(
                $"Connection string '{ConnectionStringName}' tidak ditemukan di appsettings.json.");
        }

        var resolved = raw.Replace("{DataDir}", AppPaths.DataDirectory, StringComparison.OrdinalIgnoreCase);
        return new SqliteConnectionFactory(resolved);
    }
}
