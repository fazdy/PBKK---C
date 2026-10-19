namespace SIBASA.Helpers;

/// <summary>Informasi aplikasi untuk ditampilkan di layar login dan Pengaturan.</summary>
public static class AppInfo
{
    public const string Nama = "SIBASA";
    public const string NamaLengkap = "Sistem Informasi Bank Sampah";

    public static string Version
    {
        get
        {
            var versi = typeof(AppInfo).Assembly.GetName().Version;
            return versi is null ? "1.0.0" : $"{versi.Major}.{versi.Minor}.{versi.Build}";
        }
    }

    public static string Platform =>
        OperatingSystem.IsMacOS() ? "macOS" :
        OperatingSystem.IsWindows() ? "Windows" :
        OperatingSystem.IsLinux() ? "Linux" : "Lainnya";

    /// <summary>Simbol tombol pintasan: ⌘ di macOS, Ctrl di Windows/Linux.</summary>
    public static string ModifierKey => OperatingSystem.IsMacOS() ? "⌘" : "Ctrl+";
}
