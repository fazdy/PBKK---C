using SIBASA.Models;

namespace SIBASA.Services;

/// <summary>
/// Menyimpan pengguna yang sedang login dan memeriksa hak akses.
/// Service memanggil RequireRole() sebelum aksi terbatas, jadi pembatasan tidak hanya di UI.
/// </summary>
public sealed class SessionService
{
    public User? CurrentUser { get; private set; }

    public bool IsLoggedIn => CurrentUser is not null;

    public bool IsAdmin => CurrentUser?.Role == UserRole.Admin;

    public void Start(User user) => CurrentUser = user;

    public void End() => CurrentUser = null;

    public User RequireLogin() =>
        CurrentUser ?? throw new UnauthorizedAccessException("Sesi telah berakhir. Silakan login kembali.");

    public User RequireRole(UserRole role)
    {
        var user = RequireLogin();
        if (user.Role != role)
        {
            throw new UnauthorizedAccessException($"Akses ditolak. Fitur ini khusus {role}.");
        }

        return user;
    }
}
