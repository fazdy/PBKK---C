using System.Globalization;
using Avalonia;
using SIBASA.Helpers;

namespace SIBASA;

internal sealed class Program
{
    // Jangan memakai API Avalonia atau kode yang bergantung pada SynchronizationContext
    // sebelum AppMain dipanggil, karena framework belum siap.
    [STAThread]
    public static void Main(string[] args)
    {
        // Angka, tanggal, dan rupiah memakai format Indonesia (koma sebagai pemisah desimal).
        CultureInfo.DefaultThreadCurrentCulture = Format.Indonesia;
        CultureInfo.DefaultThreadCurrentUICulture = Format.Indonesia;
        CultureInfo.CurrentCulture = Format.Indonesia;
        CultureInfo.CurrentUICulture = Format.Indonesia;

        // Jaring pengaman terakhir: error yang lolos tetap tercatat di file log.
        AppDomain.CurrentDomain.UnhandledException += (_, e) =>
        {
            if (e.ExceptionObject is Exception ex)
            {
                Log.Error("Exception fatal di luar UI thread", ex);
            }
        };
        TaskScheduler.UnobservedTaskException += (_, e) =>
        {
            Log.Error("Exception pada Task yang tidak ditunggu", e.Exception);
            e.SetObserved();
        };

        BuildAvaloniaApp().StartWithClassicDesktopLifetime(args);
    }

    // Konfigurasi Avalonia (juga dipakai oleh previewer XAML).
    public static AppBuilder BuildAvaloniaApp()
        => AppBuilder.Configure<App>()
            .UsePlatformDetect()
            .WithInterFont()
            .LogToTrace();
}
