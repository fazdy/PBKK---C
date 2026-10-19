using Avalonia;
using Avalonia.Controls.ApplicationLifetimes;
using Avalonia.Data.Core.Plugins;
using Avalonia.Markup.Xaml;
using Avalonia.Threading;
using SIBASA.Helpers;
using SIBASA.Services;
using SIBASA.ViewModels;
using SIBASA.Views;

namespace SIBASA;

public partial class App : Application
{
    private UiServices? _ui;

    public override void Initialize()
    {
        AvaloniaXamlLoader.Load(this);
    }

    public override void OnFrameworkInitializationCompleted()
    {
        if (ApplicationLifetime is IClassicDesktopStyleApplicationLifetime desktop)
        {
            // Validasi ditangani Service + ViewModel, jadi validasi DataAnnotations bawaan Avalonia dimatikan.
            DisableAvaloniaDataAnnotationValidation();

            // Service tampilan dibuat lebih dulu agar tema tersimpan langsung diterapkan.
            var settings = new SettingsService();
            settings.Load();
            var theme = new ThemeService(settings);
            theme.Initialize();
            _ui = new UiServices(settings, theme, new NotificationService(), new DialogService());

            // Error tak terduga di UI thread dicatat ke log dan ditampilkan sebagai pesan, aplikasi tidak ditutup.
            Dispatcher.UIThread.UnhandledException += OnUiThreadUnhandledException;

            var viewModel = new MainWindowViewModel(_ui);
            desktop.MainWindow = new MainWindow { DataContext = viewModel };

            // Siapkan database setelah jendela tampil, supaya layar awal langsung terlihat.
            Dispatcher.UIThread.Post(() => _ = viewModel.StartAsync(), DispatcherPriority.Background);
        }

        base.OnFrameworkInitializationCompleted();
    }

    private void OnUiThreadUnhandledException(object? sender, DispatcherUnhandledExceptionEventArgs e)
    {
        Log.Error("Exception tak tertangani di UI thread", e.Exception);
        _ui?.Notifications.Error("Terjadi kesalahan tak terduga. Detail teknis sudah dicatat di file log.");
        e.Handled = true;
    }

    private void OnTentangClick(object? sender, EventArgs e)
    {
        _ = _ui?.Dialog.InfoAsync(
            $"Tentang {AppInfo.Nama}",
            $"{AppInfo.Nama} — {AppInfo.NamaLengkap}\nVersi {AppInfo.Version} ({AppInfo.Platform})\n\n" +
            "Aplikasi desktop pencatatan tabungan bank sampah: setor sampah, jadi saldo.\n" +
            "Dibangun dengan .NET 10, Avalonia UI, CommunityToolkit.Mvvm, SQLite, dan LiveCharts2.");
    }

    private static void DisableAvaloniaDataAnnotationValidation()
    {
        var plugins = BindingPlugins.DataValidators.OfType<DataAnnotationsValidationPlugin>().ToArray();
        foreach (var plugin in plugins)
        {
            BindingPlugins.DataValidators.Remove(plugin);
        }
    }
}
