namespace SIBASA.Helpers;

/// <summary>
/// Lokasi folder data milik pengguna. Database, log, dan pengaturan disimpan di sini,
/// bukan di folder program, sehingga aplikasi tidak butuh hak administrator.
///   macOS   : ~/Library/Application Support/SIBASA
///   Windows : C:\Users\nama\AppData\Local\SIBASA
/// </summary>
public static class AppPaths
{
    public static string DataDirectory { get; } = ResolveDataDirectory();

    public static string LogDirectory => Path.Combine(DataDirectory, "logs");

    public static string SettingsFile => Path.Combine(DataDirectory, "settings.json");

    private static string ResolveDataDirectory()
    {
        // Lokasi bisa diganti lewat variabel lingkungan (dipakai pengujian otomatis / mode portabel).
        var kustom = Environment.GetEnvironmentVariable("SIBASA_DATA_DIR");
        if (!string.IsNullOrWhiteSpace(kustom))
        {
            return kustom;
        }

        var basis = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData);
        if (string.IsNullOrWhiteSpace(basis))
        {
            // Cadangan bila sistem tidak memberikan folder data (sangat jarang terjadi).
            basis = AppContext.BaseDirectory;
        }

        return Path.Combine(basis, "SIBASA");
    }
}
