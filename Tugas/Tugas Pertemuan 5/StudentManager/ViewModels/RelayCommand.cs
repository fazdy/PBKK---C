using System.Windows.Input;

namespace StudentManager.ViewModels;

/// <summary>
/// Implementasi ICommand: menghubungkan Button di View dengan method di ViewModel
/// tanpa event Click di code-behind.
/// </summary>
public class RelayCommand : ICommand
{
    private readonly Action<object?> _execute;
    private readonly Predicate<object?>? _canExecute;

    // Command tanpa parameter, contoh: new RelayCommand(Save)
    public RelayCommand(Action execute)
        : this(_ => execute())
    {
    }

    // Command dengan parameter (CommandParameter), contoh: new RelayCommand(Edit)
    public RelayCommand(Action<object?> execute, Predicate<object?>? canExecute = null)
    {
        _execute = execute ?? throw new ArgumentNullException(nameof(execute));
        _canExecute = canExecute;
    }

    public bool CanExecute(object? parameter) => _canExecute?.Invoke(parameter) ?? true;

    public void Execute(object? parameter) => _execute(parameter);

    public event EventHandler? CanExecuteChanged;

    public void RaiseCanExecuteChanged() => CanExecuteChanged?.Invoke(this, EventArgs.Empty);
}
