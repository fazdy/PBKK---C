using Avalonia;
using Avalonia.Styling;

namespace SIBASA.Services;

/// <summary>
/// Mengganti tema terang/gelap untuk seluruh aplikasi dan menyimpan pilihannya.
/// Semua warna memakai DynamicResource, jadi tampilan langsung berubah tanpa restart.
/// </summary>
public sealed class ThemeService
{
    private readonly SettingsService _settings;

    public ThemeService(SettingsService settings)
    {
        _settings = settings;
    }

    public bool IsDark { get; private set; }

    public event EventHandler? ThemeChanged;

    /// <summary>Menerapkan tema terakhir yang tersimpan saat aplikasi dibuka.</summary>
    public void Initialize() => Apply(string.Equals(_settings.Current.Theme, "Dark", StringComparison.OrdinalIgnoreCase));

    public void SetDark(bool dark)
    {
        if (dark == IsDark)
        {
            return;
        }

        Apply(dark);
        _settings.Current.Theme = dark ? "Dark" : "Light";
        _settings.Save();
    }

    public void Toggle() => SetDark(!IsDark);

    private void Apply(bool dark)
    {
        IsDark = dark;
        if (Application.Current is { } app)
        {
            app.RequestedThemeVariant = dark ? ThemeVariant.Dark : ThemeVariant.Light;
        }

        ThemeChanged?.Invoke(this, EventArgs.Empty);
    }
}
