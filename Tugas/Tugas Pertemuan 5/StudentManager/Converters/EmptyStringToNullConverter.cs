using System.Globalization;
using System.Windows.Data;

namespace StudentManager.Converters;

/// <summary>
/// Dipakai pada SelectedItem ComboBox. Nilai "" pada model diubah menjadi null
/// agar pilihan ComboBox ikut kosong saat form di-reset, dan sebaliknya
/// null dari ComboBox dikembalikan menjadi "" ke model.
/// </summary>
public class EmptyStringToNullConverter : IValueConverter
{
    // ViewModel → ComboBox
    public object? Convert(object? value, Type targetType, object? parameter, CultureInfo culture) =>
        value is string text && string.IsNullOrWhiteSpace(text) ? null : value;

    // ComboBox → ViewModel
    public object ConvertBack(object? value, Type targetType, object? parameter, CultureInfo culture) =>
        value ?? string.Empty;
}
