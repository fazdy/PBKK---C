using System.Collections.ObjectModel;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Helpers;
using SIBASA.Models;

namespace SIBASA.ViewModels;

/// <summary>Satu menu di sidebar.</summary>
public sealed record NavItem(string Key, string Title, string IconKey, bool AdminOnly, string Shortcut)
{
    public string Tooltip => $"{Title} ({Shortcut})";
}

/// <summary>
/// Kerangka aplikasi setelah login: sidebar navigasi, header, dan area halaman.
/// Menu disaring sesuai peran; membuka halaman khusus Admin sebagai Petugas
/// (misalnya lewat pintasan keyboard) menampilkan halaman "Akses ditolak".
/// </summary>
public partial class ShellViewModel : ViewModelBase, IDisposable
{
    private readonly AppServices _services;
    private readonly Action _onLoggedOut;
    private readonly IReadOnlyList<NavItem> _semuaMenu;
    private bool _sinkronMenu;

    public ShellViewModel(AppServices services, Action onLoggedOut)
    {
        _services = services;
        _onLoggedOut = onLoggedOut;
        CurrentUser = services.Session.RequireLogin();

        var m = AppInfo.ModifierKey;
        _semuaMenu =
        [
            new NavItem("dashboard", "Dashboard", "Dashboard", false, m + "1"),
            new NavItem("nasabah", "Nasabah", "Nasabah", false, m + "2"),
            new NavItem("jenis", "Jenis Sampah", "Recycle", false, m + "3"),
            new NavItem("transaksi", "Transaksi", "Transaksi", false, m + "4"),
            new NavItem("riwayat", "Riwayat", "Riwayat", false, m + "5"),
            new NavItem("pengguna", "Pengguna", "Pengguna", true, m + "6"),
            new NavItem("pengaturan", "Pengaturan", "Pengaturan", false, m + "7")
        ];
        NavItems = new ObservableCollection<NavItem>(_semuaMenu.Where(item => !item.AdminOnly || CurrentUser.IsAdmin));

        IsDarkTheme = services.Ui.Theme.IsDark;
        services.Ui.Theme.ThemeChanged += OnThemeChanged;

        NavigateTo("dashboard");
    }

    public User CurrentUser { get; }

    public ObservableCollection<NavItem> NavItems { get; }

    public string TodayText => Format.TanggalPanjang(DateTime.Now);

    public string VersionText => $"{AppInfo.Nama} v{AppInfo.Version}";

    [ObservableProperty]
    private NavItem? _selectedNavItem;

    [ObservableProperty]
    private PageViewModelBase? _currentPage;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(ThemeToggleTooltip))]
    private bool _isDarkTheme;

    public string ThemeToggleTooltip => IsDarkTheme ? "Ganti ke tema terang" : "Ganti ke tema gelap";

    partial void OnSelectedNavItemChanged(NavItem? value)
    {
        if (!_sinkronMenu && value is not null)
        {
            NavigateTo(value.Key);
        }
    }

    /// <summary>Membuka halaman berdasarkan kunci menu (dipakai sidebar dan pintasan keyboard).</summary>
    public void NavigateTo(string key)
    {
        var menu = _semuaMenu.FirstOrDefault(item => item.Key == key);
        if (menu is null)
        {
            return;
        }

        var diizinkan = !menu.AdminOnly || CurrentUser.IsAdmin;
        PageViewModelBase halaman = diizinkan
            ? BuatHalaman(key)
            : new AksesDitolakViewModel(_services, menu.Title, () => NavigateTo("dashboard"));

        CurrentPage?.OnNavigatedFrom();
        CurrentPage = halaman;

        _sinkronMenu = true;
        SelectedNavItem = diizinkan ? NavItems.FirstOrDefault(item => item.Key == key) : null;
        _sinkronMenu = false;

        // LoadAsync menangani error-nya sendiri (lihat PageViewModelBase.RunAsync).
        _ = halaman.LoadAsync();
    }

    private PageViewModelBase BuatHalaman(string key) => key switch
    {
        "nasabah" => new NasabahViewModel(_services),
        "jenis" => new JenisSampahViewModel(_services),
        "transaksi" => new TransaksiViewModel(_services),
        "riwayat" => new RiwayatViewModel(_services),
        "pengguna" => new PenggunaViewModel(_services),
        "pengaturan" => new PengaturanViewModel(_services),
        _ => new DashboardViewModel(_services)
    };

    [RelayCommand]
    private void ToggleTheme() => _services.Ui.Theme.Toggle();

    [RelayCommand]
    private async Task LogoutAsync()
    {
        var keluar = await _services.Ui.Dialog.ConfirmAsync(
            "Keluar dari SIBASA?",
            "Sesi Anda akan diakhiri. Anda perlu login kembali untuk mengakses data.",
            "Keluar",
            "Batal");
        if (!keluar)
        {
            return;
        }

        CurrentPage?.OnNavigatedFrom();
        await _services.Auth.LogoutAsync();
        _onLoggedOut();
    }

    private void OnThemeChanged(object? sender, EventArgs e) => IsDarkTheme = _services.Ui.Theme.IsDark;

    public void Dispose()
    {
        _services.Ui.Theme.ThemeChanged -= OnThemeChanged;
        CurrentPage?.OnNavigatedFrom();
    }
}
