using Microsoft.Data.Sqlite;
using Microsoft.Extensions.Configuration;
using SIBASA.Models;
using SIBASA.Services;

namespace SIBASA.Tests;

/// <summary>
/// Lingkungan uji: setiap tes memakai database SQLite sementara yang baru
/// (dibuat dari skrip SQL yang sama dengan aplikasi), lalu dihapus setelah tes selesai.
/// </summary>
public sealed class TestEnvironment : IDisposable
{
    public TestEnvironment(bool seedSampleData = true)
    {
        Folder = Path.Combine(Path.GetTempPath(), "sibasa-test-" + Guid.NewGuid().ToString("N"));
        Directory.CreateDirectory(Folder);
        DatabasePath = Path.Combine(Folder, "test.db");

        var configuration = new ConfigurationBuilder()
            .AddInMemoryCollection(new Dictionary<string, string?>
            {
                ["ConnectionStrings:SibasaDb"] = $"Data Source={DatabasePath};Foreign Keys=True;Default Timeout=5;Pooling=False",
                ["Database:SeedSampleData"] = seedSampleData.ToString(),
                ["App:ShowDemoAccounts"] = "false"
            })
            .Build();

        var settings = new SettingsService(Path.Combine(Folder, "settings.json"));
        var ui = new UiServices(settings, new ThemeService(settings), new NotificationService(), new DialogService());
        Services = new AppServices(configuration, ui);
        DatabaseBaru = Services.DbInitializer.InitializeAsync().GetAwaiter().GetResult();
    }

    public string Folder { get; }

    public string DatabasePath { get; }

    public AppServices Services { get; }

    public bool DatabaseBaru { get; }

    public Task<User> LoginAdminAsync() => Services.Auth.LoginAsync("admin", "Admin#123");

    public Task<User> LoginPetugasAsync() => Services.Auth.LoginAsync("petugas", "Petugas#123");

    /// <summary>Menjalankan query skalar langsung ke database untuk memeriksa hasil.</summary>
    public long Scalar(string sql)
    {
        using var connection = new SqliteConnection($"Data Source={DatabasePath};Pooling=False");
        connection.Open();
        using var command = connection.CreateCommand();
        command.CommandText = sql;
        return Convert.ToInt64(command.ExecuteScalar() ?? 0L);
    }

    public void Dispose()
    {
        SqliteConnection.ClearAllPools();
        try
        {
            Directory.Delete(Folder, recursive: true);
        }
        catch (IOException)
        {
            // File sementara akan dibersihkan sistem operasi bila masih terkunci.
        }
    }
}
