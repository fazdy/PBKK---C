using System.Text.RegularExpressions;
using SIBASA.Data;
using SIBASA.Helpers;
using SIBASA.Services;

namespace SIBASA.Tests;

public class PasswordHasherTests
{
    [Fact]
    public void Hash_LaluVerify_HanyaPasswordBenarYangCocok()
    {
        var hash = PasswordHasher.Hash("Rahasia2026");

        Assert.StartsWith("PBKDF2-SHA256$600000$", hash);
        Assert.True(PasswordHasher.Verify("Rahasia2026", hash));
        Assert.False(PasswordHasher.Verify("rahasia2026", hash));
    }

    [Fact]
    public void Hash_PasswordSama_HasilBerbedaKarenaSaltAcak()
    {
        Assert.NotEqual(PasswordHasher.Hash("Admin#123"), PasswordHasher.Hash("Admin#123"));
    }

    [Theory]
    [InlineData("")]
    [InlineData("bukan-format-hash")]
    [InlineData("PBKDF2-SHA256$abc$salt$hash")]
    [InlineData("PBKDF2-SHA256$1000$@@@$@@@")]
    public void Verify_FormatTidakValid_SelaluFalse(string stored)
    {
        Assert.False(PasswordHasher.Verify("Admin#123", stored));
    }

    [Fact]
    public void HashDiSkripSeed_CocokDenganPasswordAkunDemo()
    {
        // Hash di 03_seed_data.sql dibuat di luar aplikasi; tes ini memastikan formatnya sama persis.
        var sql = DbInitializer.ReadEmbeddedScript("03_seed_data.sql");
        var hashes = Regex.Matches(sql, @"'(PBKDF2-SHA256\$[^']+)'").Select(m => m.Groups[1].Value).ToList();

        Assert.Equal(2, hashes.Count);
        Assert.True(PasswordHasher.Verify("Admin#123", hashes[0]));
        Assert.True(PasswordHasher.Verify("Petugas#123", hashes[1]));
    }
}

public class DatabaseTests
{
    [Fact]
    public void Initialize_DatabaseBaru_MembuatTabelDanDataContoh()
    {
        using var env = new TestEnvironment();

        Assert.True(env.DatabaseBaru);
        Assert.Equal(2, env.Scalar("SELECT COUNT(*) FROM Users"));
        Assert.Equal(20, env.Scalar("SELECT COUNT(*) FROM Nasabah"));
        Assert.Equal(5, env.Scalar("SELECT COUNT(*) FROM KategoriSampah"));
        Assert.Equal(16, env.Scalar("SELECT COUNT(*) FROM JenisSampah"));
        Assert.Equal(120, env.Scalar("SELECT COUNT(*) FROM Setoran"));
        Assert.Equal(1, env.Scalar("PRAGMA user_version"));
    }

    [Fact]
    public async Task Initialize_DijalankanLagi_TidakMembuatUlang()
    {
        using var env = new TestEnvironment();

        var baru = await env.Services.DbInitializer.InitializeAsync();

        Assert.False(baru);
        Assert.Equal(20, env.Scalar("SELECT COUNT(*) FROM Nasabah"));
    }

    [Fact]
    public async Task Initialize_TanpaDataContoh_MembuatAdminAwalSaja()
    {
        using var env = new TestEnvironment(seedSampleData: false);

        Assert.Equal(1, env.Scalar("SELECT COUNT(*) FROM Users"));
        Assert.Equal(0, env.Scalar("SELECT COUNT(*) FROM Nasabah"));
        var admin = await env.Services.Auth.LoginAsync("admin", "Admin#123");
        Assert.True(admin.IsAdmin);
    }

    [Fact]
    public void SaldoDataContoh_SamaDenganSetoranDikurangiPenarikan()
    {
        using var env = new TestEnvironment();

        var selisih = env.Scalar("""
            SELECT COUNT(*) FROM Nasabah n
            WHERE n.Saldo <> (SELECT IFNULL(SUM(Total), 0) FROM Setoran s WHERE s.NasabahId = n.NasabahId AND s.IsCancelled = 0)
                           - (SELECT IFNULL(SUM(Jumlah), 0) FROM Penarikan p WHERE p.NasabahId = n.NasabahId AND p.IsCancelled = 0)
            """);

        Assert.Equal(0, selisih);
        Assert.Equal(0, env.Scalar("SELECT COUNT(*) FROM Nasabah WHERE Saldo < 0"));
    }
}

public class AuthServiceTests
{
    [Fact]
    public async Task Login_KredensialBenar_MengisiSesi()
    {
        using var env = new TestEnvironment();

        var user = await env.LoginAdminAsync();

        Assert.Equal("admin", user.Username);
        Assert.True(env.Services.Session.IsAdmin);
        Assert.NotNull(user.LastLoginAt);
    }

    [Theory]
    [InlineData("admin", "salah123")]
    [InlineData("tidakada", "Admin#123")]
    public async Task Login_KredensialSalah_PesanGenerik(string username, string password)
    {
        using var env = new TestEnvironment();

        var ex = await Assert.ThrowsAsync<AppException>(() => env.Services.Auth.LoginAsync(username, password));

        Assert.Equal("Username atau password salah.", ex.Message);
        Assert.False(env.Services.Session.IsLoggedIn);
    }

    [Fact]
    public async Task Login_KolomKosong_Ditolak()
    {
        using var env = new TestEnvironment();

        var ex = await Assert.ThrowsAsync<AppException>(() => env.Services.Auth.LoginAsync("", ""));

        Assert.Equal("Username dan password wajib diisi.", ex.Message);
    }

    [Fact]
    public async Task Login_AkunNonaktif_Ditolak()
    {
        using var env = new TestEnvironment();
        await env.LoginAdminAsync();
        var petugas = (await env.Services.Users.GetAllAsync()).Single(u => u.Username == "petugas");
        petugas.IsActive = false;
        await env.Services.Users.SaveAsync(petugas, null, null);
        await env.Services.Auth.LogoutAsync();

        var ex = await Assert.ThrowsAsync<AppException>(() => env.LoginPetugasAsync());

        Assert.Equal("Akun tidak aktif. Hubungi admin.", ex.Message);
    }

    [Fact]
    public async Task Logout_MengosongkanSesi()
    {
        using var env = new TestEnvironment();
        await env.LoginAdminAsync();

        await env.Services.Auth.LogoutAsync();

        Assert.False(env.Services.Session.IsLoggedIn);
        await Assert.ThrowsAsync<UnauthorizedAccessException>(() => env.Services.Nasabah.GetRwListAsync());
    }
}
