using Microsoft.Data.Sqlite;
using SIBASA.Helpers;

namespace SIBASA.Services;

/// <summary>Menerjemahkan exception teknis menjadi pesan yang dapat dipahami dan ditindaklanjuti pengguna.</summary>
public static class ErrorMessages
{
    public static string FromException(Exception exception) => exception switch
    {
        AppException app => app.Message,
        UnauthorizedAccessException akses => akses.Message,
        SqliteException sqlite => FromSqlite(sqlite),
        IOException => "File data aplikasi tidak dapat diakses. Pastikan folder data dapat ditulis.",
        _ => "Terjadi kesalahan tak terduga. Detail teknis sudah dicatat di file log."
    };

    /// <summary>True bila error bukan kesalahan biasa pengguna, sehingga perlu dicatat ke log.</summary>
    public static bool IsTechnical(Exception exception) =>
        exception is not (AppException or UnauthorizedAccessException);

    public static string FromSqlite(SqliteException exception) => exception.SqliteErrorCode switch
    {
        5 or 6 => "Database sedang dipakai aplikasi lain (misalnya DB Browser for SQLite). " +
                  "Simpan atau tutup aplikasi tersebut, lalu coba lagi.",
        8 => "Database hanya dapat dibaca. Periksa izin folder data aplikasi.",
        11 or 26 => "File database rusak atau bukan database SIBASA. Tutup aplikasi, pindahkan file database, lalu buka lagi agar dibuat ulang.",
        13 => "Penyimpanan penuh. Kosongkan ruang disk, lalu coba lagi.",
        14 => "File database tidak dapat dibuka. Periksa lokasi dan izin folder data aplikasi.",
        19 => exception.SqliteExtendedErrorCode switch
        {
            2067 or 1555 => "Data yang sama sudah ada. Nilai tersebut harus unik.",
            787 => "Data masih dipakai oleh data lain sehingga tidak dapat dihapus atau diubah.",
            275 => "Data melanggar aturan database (misalnya saldo tidak boleh minus).",
            1299 => "Ada kolom wajib yang belum diisi.",
            _ => "Data melanggar aturan database."
        },
        _ => $"Terjadi kesalahan database (kode {exception.SqliteErrorCode}). Detail teknis sudah dicatat di file log."
    };
}
