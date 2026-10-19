using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Services;

namespace SIBASA.ViewModels;

/// <summary>
/// ViewModel jendela utama. Mengatur tampilan yang aktif:
/// Startup (menyiapkan database) → Login → Shell (sidebar + halaman) → kembali ke Login saat logout.
/// Dialog konfirmasi dan toast notifikasi juga ditampilkan di jendela ini.
/// </summary>
public partial class MainWindowViewModel : ViewModelBase
{
    private readonly UiServices _ui;
    private AppServices? _services;

    [ObservableProperty]
    private ViewModelBase? _currentView;

    public MainWindowViewModel(UiServices ui)
    {
        _ui = ui;
        CurrentView = new StartupViewModel(ui, OnStartupReady);
    }

    public NotificationService Notifications => _ui.Notifications;

    public DialogService Dialog => _ui.Dialog;

    /// <summary>Mulai menyiapkan database. Dipanggil sekali setelah jendela tampil.</summary>
    public Task StartAsync() =>
        CurrentView is StartupViewModel startup ? startup.InitializeAsync() : Task.CompletedTask;

    partial void OnCurrentViewChanged(ViewModelBase? oldValue, ViewModelBase? newValue)
    {
        // Lepas langganan event milik tampilan lama agar tidak bocor memori.
        (oldValue as IDisposable)?.Dispose();
    }

    private void OnStartupReady(AppServices services, bool databaseBaru)
    {
        _services = services;
        ShowLogin();
        if (databaseBaru)
        {
            _ui.Notifications.Info(services.SeedSampleData
                ? "Database baru dibuat lengkap dengan data contoh."
                : "Database baru dibuat. Masuk dengan akun admin awal.");
        }
    }

    private void ShowLogin() => CurrentView = new LoginViewModel(_services!, OnLoggedIn);

    private void OnLoggedIn() => CurrentView = new ShellViewModel(_services!, ShowLogin);

    /// <summary>Pintasan keyboard Ctrl/⌘ + angka untuk berpindah halaman.</summary>
    [RelayCommand]
    private void NavigateShortcut(string? key)
    {
        if (CurrentView is ShellViewModel shell && !string.IsNullOrEmpty(key) && !Dialog.IsOpen)
        {
            shell.NavigateTo(key);
        }
    }
}
