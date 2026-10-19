using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Helpers;
using SIBASA.Services;

namespace SIBASA.ViewModels;

/// <summary>
/// Layar awal: membaca appsettings.json dan menyiapkan database.
/// Bila gagal (konfigurasi hilang, file database rusak/terkunci), tampil pesan dan tombol "Coba lagi".
/// </summary>
public partial class StartupViewModel : ViewModelBase
{
    private readonly UiServices _ui;
    private readonly Action<AppServices, bool> _onReady;

    public StartupViewModel(UiServices ui, Action<AppServices, bool> onReady)
    {
        _ui = ui;
        _onReady = onReady;
    }

    [ObservableProperty]
    private bool _isWorking = true;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(HasError))]
    private string? _errorMessage;

    [ObservableProperty]
    private string? _databasePath;

    public bool HasError => !string.IsNullOrEmpty(ErrorMessage);

    public string VersionText => $"Versi {AppInfo.Version}";

    public string LogPath => AppPaths.LogDirectory;

    [RelayCommand]
    public async Task InitializeAsync()
    {
        IsWorking = true;
        ErrorMessage = null;
        try
        {
            var configuration = AppServices.LoadConfiguration();
            var services = new AppServices(configuration, _ui);
            DatabasePath = services.DbInitializer.DatabasePath;

            var databaseBaru = await services.DbInitializer.InitializeAsync();
            _onReady(services, databaseBaru);
        }
        catch (Exception ex)
        {
            Log.Error("Gagal menyiapkan aplikasi atau database", ex);
            ErrorMessage = ex switch
            {
                FileNotFoundException or InvalidOperationException => ex.Message,
                _ => ErrorMessages.FromException(ex)
            };
        }
        finally
        {
            IsWorking = false;
        }
    }
}
