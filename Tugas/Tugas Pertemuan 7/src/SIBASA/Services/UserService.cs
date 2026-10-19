using System.Text.RegularExpressions;
using SIBASA.Helpers;
using SIBASA.Models;
using SIBASA.Repositories;

namespace SIBASA.Services;

/// <summary>Pengelolaan akun pengguna (khusus Admin) dan ganti password sendiri.</summary>
public sealed partial class UserService : ServiceBase
{
    private readonly UserRepository _users;

    public UserService(UserRepository users, AktivitasRepository aktivitas, SessionService session)
        : base(session, aktivitas)
    {
        _users = users;
    }

    public Task<List<User>> GetAllAsync()
    {
        Session.RequireRole(UserRole.Admin);
        return _users.GetAllAsync();
    }

    /// <summary>
    /// Menyimpan pengguna baru (UserId = 0) atau perubahan pengguna.
    /// Password wajib untuk pengguna baru; untuk pengguna lama, isi password berarti reset password.
    /// </summary>
    public async Task<User> SaveAsync(User input, string? password, string? konfirmasi)
    {
        var admin = Session.RequireRole(UserRole.Admin);
        var isBaru = input.UserId == 0;

        var data = new User
        {
            UserId = input.UserId,
            Username = input.Username?.Trim().ToLowerInvariant() ?? string.Empty,
            NamaLengkap = input.NamaLengkap?.Trim() ?? string.Empty,
            Role = input.Role,
            IsActive = input.IsActive
        };

        var errors = new ErrorBag();
        if (isBaru && !FormatUsername().IsMatch(data.Username))
        {
            errors.Add(nameof(User.Username), "Username 4–30 karakter: huruf kecil, angka, titik, atau garis bawah.");
        }

        if (data.NamaLengkap.Length is < 3 or > 100)
        {
            errors.Add(nameof(User.NamaLengkap), "Nama lengkap harus 3–100 karakter.");
        }

        var isiPassword = !string.IsNullOrEmpty(password);
        if (isBaru && !isiPassword)
        {
            errors.Add("Password", "Password wajib diisi untuk pengguna baru.");
        }
        else if (isiPassword && ValidasiKekuatan(password!) is { } pesanPassword)
        {
            errors.Add("Password", pesanPassword);
        }

        if (isiPassword && password != konfirmasi)
        {
            errors.Add("Konfirmasi", "Konfirmasi password tidak sama.");
        }

        if (isBaru && FormatUsername().IsMatch(data.Username) && await _users.UsernameExistsAsync(data.Username, null))
        {
            errors.Add(nameof(User.Username), "Username sudah dipakai.");
        }

        User? lama = null;
        if (!isBaru)
        {
            lama = await _users.GetByIdAsync(data.UserId)
                ?? throw new AppException("Pengguna tidak ditemukan.");
            data.Username = lama.Username;

            // Admin tidak boleh mengunci dirinya sendiri.
            if (lama.UserId == admin.UserId)
            {
                if (!data.IsActive)
                {
                    errors.Add(nameof(User.IsActive), "Anda tidak dapat menonaktifkan akun sendiri.");
                }

                if (data.Role != UserRole.Admin)
                {
                    errors.Add(nameof(User.Role), "Anda tidak dapat menurunkan peran akun sendiri.");
                }
            }

            // Harus selalu ada minimal satu Admin aktif.
            var kehilanganAdmin = lama is { Role: UserRole.Admin, IsActive: true }
                                  && (data.Role != UserRole.Admin || !data.IsActive);
            if (kehilanganAdmin && await _users.CountActiveAdminsAsync() <= 1)
            {
                errors.Add(nameof(User.Role), "Minimal harus ada satu Admin aktif.");
            }
        }

        errors.ThrowIfAny();

        if (isBaru)
        {
            data.PasswordHash = await Task.Run(() => PasswordHasher.Hash(password!));
            data.UserId = await _users.InsertAsync(data);
            data.CreatedAt = DateTime.Now;
            await CatatAktivitasAsync("Tambah", "Pengguna", data.UserId, $"Menambah pengguna {data.Username} ({data.Role})");
            return data;
        }

        await _users.UpdateAsync(data);
        if (isiPassword)
        {
            var hash = await Task.Run(() => PasswordHasher.Hash(password!));
            await _users.UpdatePasswordAsync(data.UserId, hash);
        }

        data.CreatedAt = lama!.CreatedAt;
        data.LastLoginAt = lama.LastLoginAt;
        await CatatAktivitasAsync("Ubah", "Pengguna", data.UserId,
            $"Mengubah pengguna {data.Username}{(isiPassword ? " dan me-reset password" : string.Empty)}");
        return data;
    }

    /// <summary>Mengganti password akun yang sedang login.</summary>
    public async Task ChangeOwnPasswordAsync(string? passwordLama, string? passwordBaru, string? konfirmasi)
    {
        var user = Session.RequireLogin();

        var errors = new ErrorBag();
        if (string.IsNullOrEmpty(passwordLama))
        {
            errors.Add("PasswordLama", "Password lama wajib diisi.");
        }

        if (string.IsNullOrEmpty(passwordBaru))
        {
            errors.Add("PasswordBaru", "Password baru wajib diisi.");
        }
        else if (ValidasiKekuatan(passwordBaru) is { } pesan)
        {
            errors.Add("PasswordBaru", pesan);
        }
        else if (passwordBaru == passwordLama)
        {
            errors.Add("PasswordBaru", "Password baru harus berbeda dari password lama.");
        }

        if (!string.IsNullOrEmpty(passwordBaru) && passwordBaru != konfirmasi)
        {
            errors.Add("Konfirmasi", "Konfirmasi password tidak sama.");
        }

        errors.ThrowIfAny();

        var terbaru = await _users.GetByIdAsync(user.UserId)
            ?? throw new AppException("Akun tidak ditemukan.");
        var cocok = await Task.Run(() => PasswordHasher.Verify(passwordLama!, terbaru.PasswordHash));
        if (!cocok)
        {
            throw new ValidationException("PasswordLama", "Password lama salah.");
        }

        var hash = await Task.Run(() => PasswordHasher.Hash(passwordBaru!));
        await _users.UpdatePasswordAsync(user.UserId, hash);
        await CatatAktivitasAsync("Ubah", "Pengguna", user.UserId, $"{user.NamaLengkap} mengganti password");
    }

    /// <summary>Password minimal 8 karakter dan mengandung huruf serta angka.</summary>
    public static string? ValidasiKekuatan(string password)
    {
        if (password.Length < 8)
        {
            return "Password minimal 8 karakter.";
        }

        if (!password.Any(char.IsLetter) || !password.Any(char.IsDigit))
        {
            return "Password harus mengandung huruf dan angka.";
        }

        return null;
    }

    [GeneratedRegex(@"^[a-z0-9._]{4,30}$")]
    private static partial Regex FormatUsername();
}
