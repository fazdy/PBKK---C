using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;

namespace SIBASA.Services;

/// <summary>Isi dialog konfirmasi yang sedang tampil.</summary>
public sealed class DialogRequest
{
    public DialogRequest(string title, string message, string confirmText, string cancelText, bool isDanger, bool showCancel)
    {
        Title = title;
        Message = message;
        ConfirmText = confirmText;
        CancelText = cancelText;
        IsDanger = isDanger;
        ShowCancel = showCancel;
    }

    public string Title { get; }
    public string Message { get; }
    public string ConfirmText { get; }
    public string CancelText { get; }
    public bool IsDanger { get; }
    public bool ShowCancel { get; }

    public string IconKey => IsDanger ? "Warning" : "Info";

    internal TaskCompletionSource<bool> Completion { get; } = new(TaskCreationOptions.RunContinuationsAsynchronously);
}

/// <summary>
/// Dialog konfirmasi berbasis MVVM: ViewModel cukup memanggil
/// <c>await Dialog.ConfirmAsync(...)</c> lalu menerima true (Ya) atau false (Batal).
/// Tampilan dialog ada di MainWindow dan terikat ke properti Current.
/// </summary>
public sealed partial class DialogService : ObservableObject
{
    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(IsOpen))]
    private DialogRequest? _current;

    public bool IsOpen => Current is not null;

    public Task<bool> ConfirmAsync(
        string title,
        string message,
        string confirmText = "Ya",
        string cancelText = "Batal",
        bool isDanger = false)
    {
        return Tampilkan(new DialogRequest(title, message, confirmText, cancelText, isDanger, showCancel: true));
    }

    /// <summary>Dialog informasi dengan satu tombol (misalnya "Tentang SIBASA").</summary>
    public Task InfoAsync(string title, string message, string okText = "Tutup") =>
        Tampilkan(new DialogRequest(title, message, okText, string.Empty, isDanger: false, showCancel: false));

    private Task<bool> Tampilkan(DialogRequest request)
    {
        // Bila masih ada dialog terbuka, anggap dibatalkan.
        Current?.Completion.TrySetResult(false);
        Current = request;
        return request.Completion.Task;
    }

    [RelayCommand]
    private void Confirm() => Close(true);

    [RelayCommand]
    private void Cancel() => Close(false);

    private void Close(bool result)
    {
        var request = Current;
        if (request is null)
        {
            return;
        }

        Current = null;
        request.Completion.TrySetResult(result);
    }
}
