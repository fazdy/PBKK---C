namespace SIBASA.Helpers;

/// <summary>Penampung pesan validasi per kolom. Panggil ThrowIfAny() setelah semua kolom diperiksa.</summary>
public sealed class ErrorBag
{
    private readonly Dictionary<string, string> _errors = new();

    public bool HasErrors => _errors.Count > 0;

    /// <summary>Menambahkan pesan. Bila kolom sudah punya pesan, pesan pertama yang dipakai.</summary>
    public void Add(string field, string message) => _errors.TryAdd(field, message);

    public void ThrowIfAny()
    {
        if (HasErrors)
        {
            throw new ValidationException(new Dictionary<string, string>(_errors));
        }
    }
}
