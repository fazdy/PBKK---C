using SIBASA.Helpers;
using SIBASA.Models;

namespace SIBASA.Tests;

public class JenisSampahServiceTests
{
    [Fact]
    public async Task Petugas_TidakBolehMengubahHarga()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();
        var jenis = (await env.Services.JenisSampah.GetJenisAsync(new JenisSampahFilter())).First();
        jenis.HargaPerKg = 9999;

        await Assert.ThrowsAsync<UnauthorizedAccessException>(() => env.Services.JenisSampah.SaveJenisAsync(jenis));
    }

    [Fact]
    public async Task NamaJenisDuplikat_Ditolak()
    {
        using var env = new TestEnvironment();
        await env.LoginAdminAsync();

        var ex = await Assert.ThrowsAsync<ValidationException>(() => env.Services.JenisSampah.SaveJenisAsync(
            new JenisSampah { NamaJenis = "botol pet bening", KategoriId = 1, HargaPerKg = 1000 }));

        Assert.Equal("Nama jenis sudah dipakai.", ex[nameof(JenisSampah.NamaJenis)]);
    }

    [Fact]
    public async Task UbahHarga_TidakMengubahTotalSetoranLama()
    {
        using var env = new TestEnvironment();
        await env.LoginAdminAsync();
        var totalLama = env.Scalar("SELECT SUM(Total) FROM Setoran WHERE JenisId = 1");
        var jenis = (await env.Services.JenisSampah.GetJenisAsync(new JenisSampahFilter("Botol PET Bening"))).Single();
        jenis.HargaPerKg = 4500;

        await env.Services.JenisSampah.SaveJenisAsync(jenis);

        Assert.Equal(4500, env.Scalar("SELECT HargaPerKg FROM JenisSampah WHERE JenisId = 1"));
        Assert.Equal(totalLama, env.Scalar("SELECT SUM(Total) FROM Setoran WHERE JenisId = 1"));
    }

    [Fact]
    public async Task HapusKategoriYangMasihPunyaJenis_Ditolak()
    {
        using var env = new TestEnvironment();
        await env.LoginAdminAsync();
        var kategori = (await env.Services.JenisSampah.GetKategoriAsync()).First();

        await Assert.ThrowsAsync<AppException>(() => env.Services.JenisSampah.DeleteKategoriAsync(kategori));
    }
}

public class UserServiceTests
{
    [Fact]
    public async Task TambahPetugasBaru_LaluBisaLogin()
    {
        using var env = new TestEnvironment();
        await env.LoginAdminAsync();

        await env.Services.Users.SaveAsync(
            new User { Username = "Petugas2", NamaLengkap = "Petugas Kedua", Role = UserRole.Petugas, IsActive = true },
            "Petugas2026", "Petugas2026");
        await env.Services.Auth.LogoutAsync();

        var user = await env.Services.Auth.LoginAsync("petugas2", "Petugas2026");
        Assert.Equal(UserRole.Petugas, user.Role);
    }

    [Theory]
    [InlineData("pendek1")]
    [InlineData("hanyahuruf")]
    [InlineData("12345678")]
    public async Task PasswordLemah_Ditolak(string password)
    {
        using var env = new TestEnvironment();
        await env.LoginAdminAsync();

        var ex = await Assert.ThrowsAsync<ValidationException>(() => env.Services.Users.SaveAsync(
            new User { Username = "baru01", NamaLengkap = "Pengguna Baru", Role = UserRole.Petugas }, password, password));

        Assert.NotNull(ex["Password"]);
    }

    [Fact]
    public async Task AdminTidakBolehMenonaktifkanDiriSendiri()
    {
        using var env = new TestEnvironment();
        var admin = await env.LoginAdminAsync();
        admin.IsActive = false;

        var ex = await Assert.ThrowsAsync<ValidationException>(() => env.Services.Users.SaveAsync(admin, null, null));

        Assert.NotNull(ex[nameof(User.IsActive)]);
    }

    [Fact]
    public async Task PetugasTidakBolehMembukaDaftarPengguna()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        await Assert.ThrowsAsync<UnauthorizedAccessException>(() => env.Services.Users.GetAllAsync());
    }

    [Fact]
    public async Task GantiPassword_PasswordLamaSalah_Ditolak_LaluBenarBerhasil()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        var ex = await Assert.ThrowsAsync<ValidationException>(() =>
            env.Services.Users.ChangeOwnPasswordAsync("salah123", "BaruSekali1", "BaruSekali1"));
        Assert.Equal("Password lama salah.", ex["PasswordLama"]);

        await env.Services.Users.ChangeOwnPasswordAsync("Petugas#123", "BaruSekali1", "BaruSekali1");
        await env.Services.Auth.LogoutAsync();
        var user = await env.Services.Auth.LoginAsync("petugas", "BaruSekali1");
        Assert.Equal("petugas", user.Username);
    }
}

public class DashboardServiceTests
{
    [Fact]
    public async Task Ringkasan_DihitungDariDatabase()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        var ringkasan = await env.Services.Dashboard.GetRingkasanAsync();

        Assert.Equal(19, ringkasan.NasabahAktif);
        Assert.Equal(20, ringkasan.NasabahTerdaftar);
        Assert.Equal(env.Scalar("SELECT SUM(Saldo) FROM Nasabah"), ringkasan.TotalSaldo);
        Assert.Equal(6, ringkasan.SetoranPerBulan.Count);
        Assert.Equal(5, ringkasan.KomposisiKategori.Count);
        Assert.True(ringkasan.TransaksiHariIni > 0);
        Assert.NotEmpty(ringkasan.AktivitasTerbaru);
    }

    [Fact]
    public async Task Ringkasan_SetelahSetoran_AngkaBerubah()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();
        var sebelum = await env.Services.Dashboard.GetRingkasanAsync();

        await env.Services.Transaksi.SimpanSetoranAsync(3, 1, 2m, null);
        var sesudah = await env.Services.Dashboard.GetRingkasanAsync();

        Assert.Equal(sebelum.SetoranHariIni + 1, sesudah.SetoranHariIni);
        Assert.Equal(sebelum.KgBulanIni + 2, sesudah.KgBulanIni, 3);
        Assert.Equal(sebelum.TotalSaldo + 8000, sesudah.TotalSaldo);
    }

    [Fact]
    public async Task Ringkasan_DatabaseKosong_NilaiNol()
    {
        using var env = new TestEnvironment(seedSampleData: false);
        await env.LoginAdminAsync();

        var ringkasan = await env.Services.Dashboard.GetRingkasanAsync();

        Assert.Equal(0, ringkasan.NasabahAktif);
        Assert.Equal(0, ringkasan.TotalSaldo);
        Assert.All(ringkasan.SetoranPerBulan, b => Assert.Equal(0, b.Kg));
        Assert.Empty(ringkasan.KomposisiKategori);
    }
}
