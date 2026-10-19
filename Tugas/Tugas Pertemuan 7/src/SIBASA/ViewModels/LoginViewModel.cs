using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Helpers;
using SIBASA.Services;

namespace SIBASA.ViewModels;

/// <summary>ViewModel halaman login.</summary>
public partial class LoginViewModel : ViewModelBase, IDisposable
{
    private readonly AppServices _services;
    private readonly Action _onLoggedIn;

    public LoginViewModel(AppServices services, Action onLoggedIn)
    {
        _services = services;
        _onLoggedIn = onLoggedIn;
        IsDarkTheme = services.Ui.Theme.IsDark;
        services.Ui.Theme.ThemeChanged += OnThemeChanged;
    }

    [ObservableProperty]
    private string _username = string.Empty;

    [ObservableProperty]
    private string _password = string.Empty;

    [ObservableProperty]
    private bool _revealPassword;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(HasError))]
    private string? _errorMessage;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(LoginButtonText))]
    private bool _isBusy;

    [ObservableProperty]
    private bool _isDarkTheme;

    public bool HasError => !string.IsNullOrEmpty(ErrorMessage);

    public bool ShowDemoAccounts => _services.ShowDemoAccounts;

    public string LoginButtonText => IsBusy ? "Memeriksa…" : "Masuk";

    public string VersionText => $"Versi {AppInfo.Version} · {AppInfo.Platform}";

    // Pesan error hilang begitu pengguna mulai mengetik ulang.
    partial void OnUsernameChanged(string value) => ErrorMessage = null;

    partial void OnPasswordChanged(string value) => ErrorMessage = null;

    [RelayCommand]
    private async Task LoginAsync()
    {
        if (IsBusy)
        {
            return;
        }

        ErrorMessage = null;
        IsBusy = true;
        try
        {
            await _services.Auth.LoginAsync(Username, Password);
            Password = string.Empty;
            _onLoggedIn();
        }
        catch (Exception ex)
        {
            if (ErrorMessages.IsTechnical(ex))
            {
                Log.Error("Login gagal karena kesalahan teknis", ex);
            }

            ErrorMessage = ErrorMessages.FromException(ex);
        }
        finally
        {
            IsBusy = false;
        }
    }

    /// <summary>Mengisi form dengan akun demo (hanya tampil bila App:ShowDemoAccounts = true).</summary>
    [RelayCommand]
    private void IsiAkunDemo(string? peran)
    {
        if (peran == "admin")
        {
            Username = "admin";
            Password = "Admin#123";
        }
        else
        {
            Username = "petugas";
            Password = "Petugas#123";
        }
    }

    [RelayCommand]
    private void ToggleTheme() => _services.Ui.Theme.Toggle();

    private void OnThemeChanged(object? sender, EventArgs e) => IsDarkTheme = _services.Ui.Theme.IsDark;

    public void Dispose() => _services.Ui.Theme.ThemeChanged -= OnThemeChanged;
}
