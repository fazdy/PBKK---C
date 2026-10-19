using Microsoft.Extensions.Configuration;
using SIBASA.Data;
using SIBASA.Repositories;
using SIBASA.Services;

namespace SIBASA;

/// <summary>Service yang khusus untuk tampilan dan selalu tersedia, bahkan sebelum database siap.</summary>
public sealed class UiServices
{
    public UiServices(SettingsService settings, ThemeService theme, NotificationService notifications, DialogService dialog)
    {
        Settings = settings;
        Theme = theme;
        Notifications = notifications;
        Dialog = dialog;
    }

    public SettingsService Settings { get; }
    public ThemeService Theme { get; }
    public NotificationService Notifications { get; }
    public DialogService Dialog { get; }
}

/// <summary>
/// Composition root: tempat semua Repository dan Service dirakit satu kali.
/// ViewModel menerima objek yang dibutuhkan lewat constructor (dependency injection manual),
/// sehingga tiap lapisan tidak membuat dependensinya sendiri dan mudah diuji.
/// </summary>
public sealed class AppServices
{
    public AppServices(IConfiguration configuration, UiServices ui)
    {
        Ui = ui;
        ConnectionFactory = SqliteConnectionFactory.FromConfiguration(configuration);
        SeedSampleData = ReadBool(configuration, "Database:SeedSampleData", defaultValue: true);
        ShowDemoAccounts = ReadBool(configuration, "App:ShowDemoAccounts", defaultValue: false);

        DbInitializer = new DbInitializer(ConnectionFactory, SeedSampleData);

        var userRepo = new UserRepository(ConnectionFactory);
        var nasabahRepo = new NasabahRepository(ConnectionFactory);
        var kategoriRepo = new KategoriSampahRepository(ConnectionFactory);
        var jenisRepo = new JenisSampahRepository(ConnectionFactory);
        var transaksiRepo = new TransaksiRepository(ConnectionFactory);
        var aktivitasRepo = new AktivitasRepository(ConnectionFactory);
        var dashboardRepo = new DashboardRepository(ConnectionFactory);

        Session = new SessionService();
        Auth = new AuthService(userRepo, aktivitasRepo, Session);
        Nasabah = new NasabahService(nasabahRepo, aktivitasRepo, Session);
        JenisSampah = new JenisSampahService(jenisRepo, kategoriRepo, aktivitasRepo, Session);
        Transaksi = new TransaksiService(transaksiRepo, nasabahRepo, jenisRepo, aktivitasRepo, Session);
        Users = new UserService(userRepo, aktivitasRepo, Session);
        Dashboard = new DashboardService(dashboardRepo, Session);
    }

    public UiServices Ui { get; }
    public SqliteConnectionFactory ConnectionFactory { get; }
    public DbInitializer DbInitializer { get; }
    public bool SeedSampleData { get; }
    public bool ShowDemoAccounts { get; }

    public SessionService Session { get; }
    public AuthService Auth { get; }
    public NasabahService Nasabah { get; }
    public JenisSampahService JenisSampah { get; }
    public TransaksiService Transaksi { get; }
    public UserService Users { get; }
    public DashboardService Dashboard { get; }

    /// <summary>Membaca appsettings.json dari folder aplikasi (di samping file .exe / di dalam .app).</summary>
    public static IConfiguration LoadConfiguration()
    {
        var path = Path.Combine(AppContext.BaseDirectory, "appsettings.json");
        if (!File.Exists(path))
        {
            throw new FileNotFoundException(
                "File konfigurasi appsettings.json tidak ditemukan di folder aplikasi. " +
                "Salin appsettings.example.json menjadi appsettings.json.", path);
        }

        return new ConfigurationBuilder()
            .SetBasePath(AppContext.BaseDirectory)
            .AddJsonFile("appsettings.json", optional: false, reloadOnChange: false)
            .Build();
    }

    private static bool ReadBool(IConfiguration configuration, string key, bool defaultValue) =>
        bool.TryParse(configuration[key], out var value) ? value : defaultValue;
}
