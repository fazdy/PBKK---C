using System.Globalization;

namespace SIBASA.Helpers;

/// <summary>
/// Format angka, uang, berat, dan tanggal dengan gaya Indonesia (id-ID),
/// misalnya Rp12.500, 2,50 kg, dan 19 Okt 2026 14:05.
/// </summary>
public static class Format
{
    public static readonly CultureInfo Indonesia = CultureInfo.GetCultureInfo("id-ID");

    public static string Rupiah(long nilai) =>
        nilai < 0
            ? "-Rp" + Math.Abs(nilai).ToString("N0", Indonesia)
            : "Rp" + nilai.ToString("N0", Indonesia);

    public static string Kg(double berat) => berat.ToString("N2", Indonesia) + " kg";

    public static string Kg(decimal berat) => berat.ToString("N2", Indonesia) + " kg";

    public static string Angka(double nilai, int desimal = 0) => nilai.ToString("N" + desimal, Indonesia);

    public static string Tanggal(DateTime waktu) => waktu.ToString("dd MMM yyyy HH:mm", Indonesia);

    public static string TanggalSaja(DateTime waktu) => waktu.ToString("dd MMM yyyy", Indonesia);

    public static string Jam(DateTime waktu) => waktu.ToString("HH:mm", Indonesia);

    public static string TanggalPanjang(DateTime waktu) => waktu.ToString("dddd, d MMMM yyyy", Indonesia);

    public static string BulanSingkat(DateTime bulan) => bulan.ToString("MMM yy", Indonesia);

    /// <summary>Waktu relatif singkat untuk daftar aktivitas, misalnya "5 menit lalu".</summary>
    public static string Relatif(DateTime waktu, DateTime sekarang)
    {
        var selisih = sekarang - waktu;
        if (selisih.TotalSeconds < 0) return Tanggal(waktu);
        if (selisih.TotalMinutes < 1) return "baru saja";
        if (selisih.TotalMinutes < 60) return $"{(int)selisih.TotalMinutes} menit lalu";
        if (selisih.TotalHours < 24 && waktu.Date == sekarang.Date) return $"{(int)selisih.TotalHours} jam lalu";
        if (waktu.Date == sekarang.Date.AddDays(-1)) return "kemarin " + Jam(waktu);
        return Tanggal(waktu);
    }

    /// <summary>Inisial nama untuk avatar, misalnya "Rina Marlina" menjadi "RM".</summary>
    public static string Inisial(string? nama)
    {
        if (string.IsNullOrWhiteSpace(nama)) return "?";
        var bagian = nama.Split(' ', StringSplitOptions.RemoveEmptyEntries | StringSplitOptions.TrimEntries);
        var huruf = bagian.Take(2).Select(b => char.ToUpper(b[0], Indonesia));
        return string.Concat(huruf);
    }
}
