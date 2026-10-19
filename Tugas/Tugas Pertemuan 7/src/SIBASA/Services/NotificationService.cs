using System.Collections.ObjectModel;
using Avalonia.Threading;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;

namespace SIBASA.Services;

public enum ToastKind
{
    Success,
    Error,
    Warning,
    Info
}

/// <summary>Satu notifikasi toast yang tampil di pojok kanan atas.</summary>
public sealed partial class ToastMessage : ObservableObject
{
    private readonly Action<ToastMessage> _close;

    public ToastMessage(string text, ToastKind kind, Action<ToastMessage> close)
    {
        Text = text;
        Kind = kind;
        _close = close;
    }

    public string Text { get; }
    public ToastKind Kind { get; }

    public bool IsSuccess => Kind == ToastKind.Success;
    public bool IsError => Kind == ToastKind.Error;
    public bool IsWarning => Kind == ToastKind.Warning;
    public bool IsInfo => Kind == ToastKind.Info;

    public string Title => Kind switch
    {
        ToastKind.Success => "Berhasil",
        ToastKind.Error => "Gagal",
        ToastKind.Warning => "Perhatian",
        _ => "Info"
    };

    public string IconKey => Kind switch
    {
        ToastKind.Success => "Success",
        ToastKind.Error => "Error",
        ToastKind.Warning => "Warning",
        _ => "Info"
    };

    [RelayCommand]
    private void Close() => _close(this);
}

/// <summary>Menampilkan notifikasi berhasil/gagal setelah aksi penting. Toast hilang otomatis.</summary>
public sealed class NotificationService
{
    private const int MaksimumToast = 4;

    public ObservableCollection<ToastMessage> Items { get; } = new();

    public void Success(string text) => Show(text, ToastKind.Success);

    public void Error(string text) => Show(text, ToastKind.Error);

    public void Warning(string text) => Show(text, ToastKind.Warning);

    public void Info(string text) => Show(text, ToastKind.Info);

    public void Show(string text, ToastKind kind)
    {
        // Koleksi yang terikat ke UI hanya boleh diubah dari UI thread.
        Dispatcher.UIThread.Post(() =>
        {
            var toast = new ToastMessage(text, kind, Remove);
            Items.Add(toast);
            while (Items.Count > MaksimumToast)
            {
                Items.RemoveAt(0);
            }

            var durasi = kind == ToastKind.Error ? TimeSpan.FromSeconds(7) : TimeSpan.FromSeconds(4);
            DispatcherTimer.RunOnce(() => Remove(toast), durasi);
        });
    }

    private void Remove(ToastMessage toast) => Items.Remove(toast);
}
