using System.Windows;

namespace StudentManager.Services;

/// <summary>
/// Implementasi IDialogService menggunakan MessageBox WPF.
/// </summary>
public class DialogService : IDialogService
{
    public void ShowWarning(string message, string title) =>
        Show(message, title, MessageBoxButton.OK, MessageBoxImage.Warning);

    public void ShowError(string message, string title) =>
        Show(message, title, MessageBoxButton.OK, MessageBoxImage.Error);

    public bool Confirm(string message, string title) =>
        Show(message, title, MessageBoxButton.YesNo, MessageBoxImage.Question) == MessageBoxResult.Yes;

    private static MessageBoxResult Show(string message, string title,
        MessageBoxButton button, MessageBoxImage icon)
    {
        // Jika jendela utama sudah tampil, dialog dibuka di tengah jendela tersebut.
        Window? owner = Application.Current?.MainWindow;

        return owner is { IsVisible: true }
            ? MessageBox.Show(owner, message, title, button, icon)
            : MessageBox.Show(message, title, button, icon);
    }
}
