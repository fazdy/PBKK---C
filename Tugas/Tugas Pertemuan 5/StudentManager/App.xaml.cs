using System.Windows;
using System.Windows.Threading;

namespace StudentManager;

public partial class App : Application
{
    protected override void OnStartup(StartupEventArgs e)
    {
        base.OnStartup(e);
        DispatcherUnhandledException += OnDispatcherUnhandledException;
    }

    // Jaring pengaman: error yang tidak tertangani ditampilkan sebagai pesan
    // sehingga aplikasi tidak langsung tertutup.
    private void OnDispatcherUnhandledException(object sender, DispatcherUnhandledExceptionEventArgs e)
    {
        MessageBox.Show(
            "Terjadi kesalahan yang tidak terduga:\n\n" + e.Exception.Message,
            "Student Management System",
            MessageBoxButton.OK,
            MessageBoxImage.Error);

        e.Handled = true;

        // Jika error terjadi sebelum jendela utama tampil, aplikasi ditutup
        // agar proses tidak berjalan tanpa jendela.
        if (MainWindow is null || !MainWindow.IsVisible)
            Shutdown(-1);
    }
}
