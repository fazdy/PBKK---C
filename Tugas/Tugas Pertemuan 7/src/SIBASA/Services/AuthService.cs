using SIBASA.Helpers;
using SIBASA.Models;
using SIBASA.Repositories;

namespace SIBASA.Services;

/// <summary>Login dan logout pengguna.</summary>
public sealed class AuthService : ServiceBase
{
    // Hash palsu agar waktu proses login sama, baik username ada maupun tidak
    // (mencegah penyerang menebak username yang valid dari lamanya respons).
    private static readonly Lazy<string> DummyHash = new(() => PasswordHasher.Hash(Guid.NewGuid().ToString("N")));

    private readonly UserRepository _users;

    public AuthService(UserRepository users, AktivitasRepository aktivitas, SessionService session)
        : base(session, aktivitas)
    {
        _users = users;
    }

    public async Task<User> LoginAsync(string? username, string? password)
    {
        if (string.IsNullOrWhiteSpace(username) || string.IsNullOrEmpty(password))
        {
            throw new AppException("Username dan password wajib diisi.");
        }

        var user = await _users.GetByUsernameAsync(username.Trim());

        // Verifikasi PBKDF2 cukup berat, jadi dijalankan di thread latar.
        var cocok = await Task.Run(() => PasswordHasher.Verify(password, user?.PasswordHash ?? DummyHash.Value));
        if (user is null || !cocok)
        {
            Log.Warning($"Login gagal untuk username '{username.Trim()}'.");
            throw new AppException("Username atau password salah.");
        }

        // Status aktif diperiksa setelah password benar agar tidak membocorkan keberadaan akun.
        if (!user.IsActive)
        {
            throw new AppException("Akun tidak aktif. Hubungi admin.");
        }

        var sekarang = DateTime.Now;
        await _users.UpdateLastLoginAsync(user.UserId, sekarang);
        user.LastLoginAt = sekarang;

        Session.Start(user);
        await CatatAktivitasAsync("Login", "Sesi", user.UserId, $"{user.NamaLengkap} masuk ke aplikasi");
        return user;
    }

    public async Task LogoutAsync()
    {
        if (Session.CurrentUser is { } user)
        {
            await CatatAktivitasAsync("Logout", "Sesi", user.UserId, $"{user.NamaLengkap} keluar dari aplikasi");
        }

        Session.End();
    }
}
