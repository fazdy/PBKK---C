using SIBASA.Helpers;

namespace SIBASA.Models;

/// <summary>Peran pengguna aplikasi.</summary>
public enum UserRole
{
    Admin,
    Petugas
}

/// <summary>Akun pengguna aplikasi (tabel Users).</summary>
public sealed class User
{
    public int UserId { get; set; }
    public string Username { get; set; } = string.Empty;
    public string PasswordHash { get; set; } = string.Empty;
    public string NamaLengkap { get; set; } = string.Empty;
    public UserRole Role { get; set; } = UserRole.Petugas;
    public bool IsActive { get; set; } = true;
    public DateTime CreatedAt { get; set; }
    public DateTime? LastLoginAt { get; set; }

    public bool IsAdmin => Role == UserRole.Admin;
    public string RoleText => Role.ToString();
    public string StatusText => IsActive ? "Aktif" : "Nonaktif";
    public string Inisial => Format.Inisial(NamaLengkap);
    public string LastLoginText => LastLoginAt is { } waktu ? Format.Tanggal(waktu) : "Belum pernah";
}
