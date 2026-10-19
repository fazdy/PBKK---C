using System.Globalization;
using Microsoft.Data.Sqlite;

namespace SIBASA.Data;

/// <summary>
/// Konversi tanggal dan pembacaan kolom. Tanggal disimpan sebagai TEXT 'yyyy-MM-dd HH:mm:ss'
/// sehingga bisa diurutkan dan dibandingkan langsung di SQL.
/// </summary>
public static class DbDate
{
    public const string Pattern = "yyyy-MM-dd HH:mm:ss";

    public static string ToDb(DateTime value) => value.ToString(Pattern, CultureInfo.InvariantCulture);

    public static DateTime FromDb(string value) =>
        DateTime.TryParseExact(value, Pattern, CultureInfo.InvariantCulture, DateTimeStyles.None, out var hasil)
            ? hasil
            : DateTime.Parse(value, CultureInfo.InvariantCulture);
}

/// <summary>Helper membaca kolom berdasarkan nama agar kode repository lebih ringkas dan jelas.</summary>
public static class SqliteReaderExtensions
{
    public static string Str(this SqliteDataReader r, string kolom) => r.GetString(r.GetOrdinal(kolom));

    public static string? StrOrNull(this SqliteDataReader r, string kolom)
    {
        var i = r.GetOrdinal(kolom);
        return r.IsDBNull(i) ? null : r.GetString(i);
    }

    public static int Int(this SqliteDataReader r, string kolom) => r.GetInt32(r.GetOrdinal(kolom));

    public static int? IntOrNull(this SqliteDataReader r, string kolom)
    {
        var i = r.GetOrdinal(kolom);
        return r.IsDBNull(i) ? null : r.GetInt32(i);
    }

    public static long Long(this SqliteDataReader r, string kolom) => r.GetInt64(r.GetOrdinal(kolom));

    public static long? LongOrNull(this SqliteDataReader r, string kolom)
    {
        var i = r.GetOrdinal(kolom);
        return r.IsDBNull(i) ? null : r.GetInt64(i);
    }

    public static double Dbl(this SqliteDataReader r, string kolom) => r.GetDouble(r.GetOrdinal(kolom));

    public static double? DblOrNull(this SqliteDataReader r, string kolom)
    {
        var i = r.GetOrdinal(kolom);
        return r.IsDBNull(i) ? null : r.GetDouble(i);
    }

    public static bool Bool(this SqliteDataReader r, string kolom) => r.GetInt64(r.GetOrdinal(kolom)) != 0;

    public static DateTime Date(this SqliteDataReader r, string kolom) => DbDate.FromDb(r.Str(kolom));

    public static DateTime? DateOrNull(this SqliteDataReader r, string kolom) =>
        r.StrOrNull(kolom) is { } teks ? DbDate.FromDb(teks) : null;
}
