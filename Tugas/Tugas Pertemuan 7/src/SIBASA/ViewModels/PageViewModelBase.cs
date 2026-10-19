using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Helpers;
using SIBASA.Services;

namespace SIBASA.ViewModels;

/// <summary>
/// Dasar ViewModel halaman di dalam Shell. Menyediakan judul halaman, indikator proses (IsBusy),
/// banner error dengan tombol "Coba lagi", dan penanganan error yang seragam.
/// </summary>
public abstract partial class PageViewModelBase : ViewModelBase
{
    private int _busyCount;

    protected PageViewModelBase(AppServices services)
    {
        Services = services;
    }

    protected AppServices Services { get; }

    protected NotificationService Notifications => Services.Ui.Notifications;

    protected DialogService Dialog => Services.Ui.Dialog;

    public abstract string Title { get; }

    public abstract string Subtitle { get; }

    public bool IsAdmin => Services.Session.IsAdmin;

    [ObservableProperty]
    private bool _isBusy;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(HasError))]
    private string? _errorMessage;

    public bool HasError => !string.IsNullOrEmpty(ErrorMessage);

    /// <summary>Dipanggil Shell setiap kali halaman dibuka.</summary>
    public virtual Task LoadAsync() => Task.CompletedTask;

    /// <summary>Dipanggil Shell saat pindah ke halaman lain (lepas langganan event, dsb.).</summary>
    public virtual void OnNavigatedFrom()
    {
    }

    [RelayCommand]
    private Task RetryAsync() => LoadAsync();

    /// <summary>
    /// Menjalankan aksi async dengan indikator proses dan penanganan error:
    ///  - ValidationException : pesan per kolom diteruskan ke onValidation;
    ///  - AppException/akses ditolak : pesan ditampilkan sebagai toast;
    ///  - error teknis : dicatat ke log, pengguna melihat pesan yang ramah.
    /// Mengembalikan true bila aksi berhasil.
    /// </summary>
    protected async Task<bool> RunAsync(Func<Task> action, Action<ValidationException>? onValidation = null, bool isLoad = false)
    {
        _busyCount++;
        IsBusy = true;
        try
        {
            await action();
            if (isLoad)
            {
                ErrorMessage = null;
            }

            return true;
        }
        catch (ValidationException ex)
        {
            if (onValidation is not null)
            {
                onValidation(ex);
                Notifications.Warning(ex.Message);
            }
            else
            {
                Notifications.Warning(ex.Errors.Values.FirstOrDefault() ?? ex.Message);
            }

            return false;
        }
        catch (Exception ex)
        {
            var pesan = ErrorMessages.FromException(ex);
            if (ErrorMessages.IsTechnical(ex))
            {
                Log.Error($"Kesalahan di halaman {Title}", ex);
            }

            if (isLoad)
            {
                ErrorMessage = pesan;
            }

            Notifications.Error(pesan);
            return false;
        }
        finally
        {
            _busyCount--;
            IsBusy = _busyCount > 0;
        }
    }

    /// <summary>Menunggu jeda singkat sebelum mencari ulang agar database tidak di-query di setiap ketikan.</summary>
    protected static async Task<bool> DebounceAsync(CancellationToken token, int milliseconds = 300)
    {
        try
        {
            await Task.Delay(milliseconds, token);
            return true;
        }
        catch (TaskCanceledException)
        {
            return false;
        }
    }
}
