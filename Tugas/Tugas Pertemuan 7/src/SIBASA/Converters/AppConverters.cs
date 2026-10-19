using Avalonia;
using Avalonia.Data.Converters;
using Avalonia.Media;
using SIBASA.Helpers;

namespace SIBASA.Converters;

/// <summary>
/// Converter untuk binding di XAML, misalnya:
/// Text="{Binding Saldo, Converter={x:Static conv:AppConverters.Rupiah}}".
/// </summary>
public static class AppConverters
{
    /// <summary>long → "Rp12.500"</summary>
    public static readonly IValueConverter Rupiah = new FuncValueConverter<long, string>(nilai => Format.Rupiah(nilai));

    /// <summary>double → "2,50 kg"</summary>
    public static readonly IValueConverter Kg = new FuncValueConverter<double, string>(berat => Format.Kg(berat));

    /// <summary>DateTime → "19 Okt 2026 14:05"</summary>
    public static readonly IValueConverter Tanggal = new FuncValueConverter<DateTime, string>(waktu => Format.Tanggal(waktu));

    /// <summary>Nama ikon ("Dashboard") → geometri ikon dari resource "Icon.Dashboard".</summary>
    public static readonly IValueConverter Icon = new FuncValueConverter<string?, Geometry?>(CariIkon);

    /// <summary>Jenis aksi aktivitas ("Setoran", "Hapus", ...) → ikon yang sesuai.</summary>
    public static readonly IValueConverter AksiIcon = new FuncValueConverter<string?, Geometry?>(aksi => CariIkon(aksi switch
    {
        "Login" => "Account",
        "Logout" => "Logout",
        "Tambah" => "Plus",
        "Ubah" => "Edit",
        "Hapus" => "Delete",
        "Setoran" => "CashPlus",
        "Penarikan" => "CashMinus",
        "Batal" => "Cancel",
        "Aktifkan" => "UserCheck",
        "Nonaktifkan" => "UserOff",
        _ => "Info"
    }));

    private static Geometry? CariIkon(string? nama)
    {
        if (string.IsNullOrEmpty(nama) || Application.Current is not { } app)
        {
            return null;
        }

        return app.TryGetResource("Icon." + nama, null, out var hasil) ? hasil as Geometry : null;
    }
}
