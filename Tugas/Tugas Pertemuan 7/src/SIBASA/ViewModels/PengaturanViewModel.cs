using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Helpers;
using SIBASA.Models;

namespace SIBASA.ViewModels;

/// <summary>Halaman Pengaturan: tema tampilan, profil, ganti password, dan informasi aplikasi.</summary>
public partial class PengaturanViewModel : PageViewModelBase
{
    public PengaturanViewModel(AppServices services) : base(services)
    {
        IsDarkTheme = services.Ui.Theme.IsDark;
        services.Ui.Theme.ThemeChanged += OnThemeChanged;
    }

    public override string Title => "Pengaturan";

    public override string Subtitle => "Preferensi tampilan, profil, dan keamanan akun";

    // ---------------- Tema ----------------
    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(IsLightTheme))]
    private bool _isDarkTheme;

    /// <summary>Pasangan RadioButton "Terang" (selalu kebalikan IsDarkTheme).</summary>
    public bool IsLightTheme
    {
        get => !IsDarkTheme;
        set
        {
            if (value)
            {
                IsDarkTheme = false;
            }
        }
    }

    partial void OnIsDarkThemeChanged(bool value) => Services.Ui.Theme.SetDark(value);

    // ---------------- Profil ----------------
    public User? Pengguna => Services.Session.CurrentUser;
    public string NamaText => Pengguna?.NamaLengkap ?? "-";
    public string UsernameText => Pengguna?.Username ?? "-";
    public string RoleText => Pengguna?.RoleText ?? "-";
    public string InisialText => Pengguna?.Inisial ?? "?";
    public string LastLoginText => Pengguna?.LastLoginText ?? "-";

    // ---------------- Ganti password ----------------
    [ObservableProperty] private string _passwordLama = string.Empty;
    [ObservableProperty] private string _passwordBaru = string.Empty;
    [ObservableProperty] private string _konfirmasi = string.Empty;
    [ObservableProperty] private string? _passwordLamaError;
    [ObservableProperty] private string? _passwordBaruError;
    [ObservableProperty] private string? _konfirmasiError;

    partial void OnPasswordLamaChanged(string value) => PasswordLamaError = null;
    partial void OnPasswordBaruChanged(string value) => PasswordBaruError = null;
    partial void OnKonfirmasiChanged(string value) => KonfirmasiError = null;

    [RelayCommand]
    private async Task GantiPasswordAsync()
    {
        await RunAsync(async () =>
        {
            await Services.Users.ChangeOwnPasswordAsync(PasswordLama, PasswordBaru, Konfirmasi);
            PasswordLama = string.Empty;
            PasswordBaru = string.Empty;
            Konfirmasi = string.Empty;
            Notifications.Success("Password berhasil diganti. Gunakan password baru saat login berikutnya.");
        }, ex =>
        {
            PasswordLamaError = ex["PasswordLama"];
            PasswordBaruError = ex["PasswordBaru"];
            KonfirmasiError = ex["Konfirmasi"];
        });
    }

    // ---------------- Tentang aplikasi ----------------
    public string VersiText => $"{AppInfo.Nama} {AppInfo.Version} · {AppInfo.Platform}";
    public string DatabasePath => Services.ConnectionFactory.DatabasePath;
    public string LogPath => AppPaths.LogDirectory;
    public string SettingsPath => AppPaths.SettingsFile;
    public string TeknologiText => ".NET 10 · Avalonia UI 11.3 · CommunityToolkit.Mvvm · SQLite · LiveCharts2";
    public string PintasanText => $"{AppInfo.ModifierKey}1 – {AppInfo.ModifierKey}7 untuk berpindah halaman";

    public override void OnNavigatedFrom() => Services.Ui.Theme.ThemeChanged -= OnThemeChanged;

    private void OnThemeChanged(object? sender, EventArgs e) => IsDarkTheme = Services.Ui.Theme.IsDark;
}
