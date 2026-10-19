using System.Security.Cryptography;

namespace SIBASA.Services;

/// <summary>
/// Hash password dengan PBKDF2-HMAC-SHA256 (600.000 iterasi, salt acak 16 byte per pengguna).
/// Format tersimpan: PBKDF2-SHA256$iterasi$saltBase64$hashBase64.
/// Password asli tidak pernah disimpan, sehingga jika database bocor password tetap sulit ditebak.
/// </summary>
public static class PasswordHasher
{
    private const string Prefix = "PBKDF2-SHA256";
    private const int SaltSize = 16;
    private const int HashSize = 32;
    public const int Iterations = 600_000;

    public static string Hash(string password)
    {
        ArgumentNullException.ThrowIfNull(password);
        var salt = RandomNumberGenerator.GetBytes(SaltSize);
        var hash = Rfc2898DeriveBytes.Pbkdf2(password, salt, Iterations, HashAlgorithmName.SHA256, HashSize);
        return $"{Prefix}${Iterations}${Convert.ToBase64String(salt)}${Convert.ToBase64String(hash)}";
    }

    public static bool Verify(string password, string storedHash)
    {
        if (string.IsNullOrEmpty(password) || string.IsNullOrEmpty(storedHash))
        {
            return false;
        }

        var bagian = storedHash.Split('$');
        if (bagian.Length != 4 || bagian[0] != Prefix || !int.TryParse(bagian[1], out var iterasi) || iterasi <= 0)
        {
            return false;
        }

        byte[] salt;
        byte[] expected;
        try
        {
            salt = Convert.FromBase64String(bagian[2]);
            expected = Convert.FromBase64String(bagian[3]);
        }
        catch (FormatException)
        {
            return false;
        }

        var actual = Rfc2898DeriveBytes.Pbkdf2(password, salt, iterasi, HashAlgorithmName.SHA256, expected.Length);

        // Perbandingan waktu-konstan mencegah serangan timing.
        return CryptographicOperations.FixedTimeEquals(actual, expected);
    }
}
