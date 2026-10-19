namespace SIBASA.Helpers;

/// <summary>
/// Kesalahan aturan bisnis yang pesannya aman dan layak ditampilkan langsung ke pengguna,
/// misalnya "Saldo tidak cukup" atau "Akun tidak aktif".
/// </summary>
public class AppException : Exception
{
    public AppException(string message) : base(message)
    {
    }
}

/// <summary>
/// Kesalahan validasi formulir. Berisi pesan per kolom (kunci = nama kolom)
/// agar ViewModel bisa menampilkan pesan tepat di bawah kolom yang salah.
/// </summary>
public sealed class ValidationException : AppException
{
    public ValidationException(IReadOnlyDictionary<string, string> errors)
        : base("Periksa kembali isian yang ditandai.")
    {
        Errors = errors;
    }

    public ValidationException(string field, string message)
        : this(new Dictionary<string, string> { [field] = message })
    {
    }

    public IReadOnlyDictionary<string, string> Errors { get; }

    public string? this[string field] => Errors.TryGetValue(field, out var pesan) ? pesan : null;
}
