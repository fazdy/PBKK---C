using SIBASA.Helpers;
using SIBASA.Models;

namespace SIBASA.Tests;

public class NasabahServiceTests
{
    [Fact]
    public async Task Tambah_DataValid_NoRekeningOtomatisDanInputDirapikan()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        var hasil = await env.Services.Nasabah.SaveAsync(new Nasabah
        {
            NamaLengkap = "  Rahmat   Hidayat ",
            NoHp = "0812-3456-7899",
            RT = "2",
            RW = "3",
            IsActive = true
        });

        Assert.Equal("BS-0021", hasil.NoRekening);
        var tersimpan = await env.Services.Nasabah.GetByIdAsync(hasil.NasabahId);
        Assert.NotNull(tersimpan);
        Assert.Equal("Rahmat Hidayat", tersimpan!.NamaLengkap);
        Assert.Equal("081234567899", tersimpan.NoHp);
        Assert.Equal("02", tersimpan.RT);
        Assert.Equal("03", tersimpan.RW);
        Assert.Equal(0, tersimpan.Saldo);
    }

    [Fact]
    public async Task Tambah_DataTidakValid_PesanPerKolom()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        var ex = await Assert.ThrowsAsync<ValidationException>(() => env.Services.Nasabah.SaveAsync(new Nasabah
        {
            NamaLengkap = "",
            NoHp = "12ab",
            RT = "",
            RW = "1234"
        }));

        Assert.NotNull(ex[nameof(Nasabah.NamaLengkap)]);
        Assert.NotNull(ex[nameof(Nasabah.NoHp)]);
        Assert.NotNull(ex[nameof(Nasabah.RT)]);
        Assert.NotNull(ex[nameof(Nasabah.RW)]);
        Assert.Equal(20, env.Scalar("SELECT COUNT(*) FROM Nasabah"));
    }

    [Fact]
    public async Task Cari_TidakMembedakanHurufBesarKecil_DanBisaNoRekeningSebagian()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        var hasilNama = await env.Services.Nasabah.GetAllAsync(new NasabahFilter("siti"));
        var hasilRekening = await env.Services.Nasabah.GetAllAsync(new NasabahFilter("0012"));
        var hasilKosong = await env.Services.Nasabah.GetAllAsync(new NasabahFilter("zzzz"));

        Assert.Contains(hasilNama, n => n.NamaLengkap == "Siti Aminah");
        Assert.Single(hasilRekening);
        Assert.Equal("BS-0012", hasilRekening[0].NoRekening);
        Assert.Empty(hasilKosong);
    }

    [Fact]
    public async Task Filter_RwDanStatus_BisaDigabung()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        var hasil = await env.Services.Nasabah.GetAllAsync(new NasabahFilter(null, "02", true));

        Assert.NotEmpty(hasil);
        Assert.All(hasil, n => Assert.True(n.RW == "02" && n.IsActive));
    }

    [Fact]
    public async Task Hapus_NasabahSudahBertransaksi_Ditolak()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        var ex = await Assert.ThrowsAsync<AppException>(() => env.Services.Nasabah.DeleteAsync(1));

        Assert.Contains("tidak dapat dihapus", ex.Message);
        Assert.Equal(20, env.Scalar("SELECT COUNT(*) FROM Nasabah"));
    }

    [Fact]
    public async Task Hapus_NasabahBaruTanpaTransaksi_Berhasil()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();
        var baru = await env.Services.Nasabah.SaveAsync(new Nasabah { NamaLengkap = "Calon Hapus", RT = "1", RW = "1" });

        await env.Services.Nasabah.DeleteAsync(baru.NasabahId);

        Assert.Null(await env.Services.Nasabah.GetByIdAsync(baru.NasabahId));
    }

    [Fact]
    public async Task Nonaktifkan_SaldoMasihAda_Ditolak()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        var ex = await Assert.ThrowsAsync<AppException>(() => env.Services.Nasabah.SetActiveAsync(1, false));

        Assert.Contains("lakukan penarikan", ex.Message);
        Assert.Equal(1, env.Scalar("SELECT IsActive FROM Nasabah WHERE NasabahId = 1"));
    }

    [Fact]
    public async Task NomorRekening_TidakDipakaiUlangSetelahDihapus()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();
        var pertama = await env.Services.Nasabah.SaveAsync(new Nasabah { NamaLengkap = "Pertama", RT = "1", RW = "1" });
        await env.Services.Nasabah.DeleteAsync(pertama.NasabahId);

        var kedua = await env.Services.Nasabah.SaveAsync(new Nasabah { NamaLengkap = "Kedua", RT = "1", RW = "1" });

        Assert.Equal("BS-0021", pertama.NoRekening);
        Assert.Equal("BS-0022", kedua.NoRekening);
    }
}
