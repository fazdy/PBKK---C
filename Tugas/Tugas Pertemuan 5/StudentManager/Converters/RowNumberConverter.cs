using System.Globalization;
using System.Windows.Data;

namespace StudentManager.Converters;

/// <summary>
/// Mengubah indeks baris DataGrid (dimulai dari 0) menjadi nomor urut
/// (dimulai dari 1) untuk kolom "No".
/// </summary>
public class RowNumberConverter : IValueConverter
{
    public object Convert(object value, Type targetType, object parameter, CultureInfo culture) =>
        value is int index ? (index + 1).ToString(culture) : string.Empty;

    public object ConvertBack(object value, Type targetType, object parameter, CultureInfo culture) =>
        Binding.DoNothing;
}
