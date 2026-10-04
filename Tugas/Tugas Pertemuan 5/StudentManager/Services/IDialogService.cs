namespace StudentManager.Services;

/// <summary>
/// Abstraksi dialog. ViewModel cukup memanggil interface ini sehingga
/// tidak bergantung langsung pada MessageBox (komponen milik View).
/// </summary>
public interface IDialogService
{
    void ShowWarning(string message, string title);

    void ShowError(string message, string title);

    bool Confirm(string message, string title);
}
