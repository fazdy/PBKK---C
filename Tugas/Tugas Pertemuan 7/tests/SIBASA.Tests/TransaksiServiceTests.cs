using SIBASA.Helpers;
using SIBASA.Models;
using SIBASA.Services;

namespace SIBASA.Tests;

public class TransaksiServiceTests
{
    private const int BotolPetBening = 1; // Rp4.000/kg pada data contoh

    [Theory]
    [InlineData("2.5", 4000, 10000)]
    [InlineData("1.005", 300, 302)]   // 301,5 dibulatkan ke atas
    [InlineData("0.333", 3000, 999)]
    [InlineData("0.01", 400, 4)]
    public void HitungTotal_BeratKaliHarga_DibulatkanKeRupiahTerdekat(string berat, long harga, long total)
    {
        Assert.Equal(total, TransaksiService.HitungTotal(decimal.Parse(berat, System.Globalization.CultureInfo.InvariantCulture), harga));
    }

    [Fact]
    public async Task Setoran_Valid_MenambahSaldoDanMembuatNomorTransaksi()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();
        var sebelum = (await env.Services.Nasabah.GetByIdAsync(3))!.Saldo;

        var setoran = await env.Services.Transaksi.SimpanSetoranAsync(3, BotolPetBening, 2.5m, "uji");

        Assert.Equal(10000, setoran.Total);
        Assert.StartsWith("STR-", setoran.NoTransaksi);
        Assert.EndsWith("-00121", setoran.NoTransaksi);
        Assert.Equal(sebelum + 10000, (await env.Services.Nasabah.GetByIdAsync(3))!.Saldo);
    }

    [Theory]
    [InlineData("0")]
    [InlineData("0.004")]
    [InlineData("2000")]
    public async Task Setoran_BeratDiLuarRentang_Ditolak(string berat)
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        var ex = await Assert.ThrowsAsync<ValidationException>(() =>
            env.Services.Transaksi.SimpanSetoranAsync(3, BotolPetBening, decimal.Parse(berat, System.Globalization.CultureInfo.InvariantCulture), null));

        Assert.NotNull(ex["Berat"]);
        Assert.Equal(120, env.Scalar("SELECT COUNT(*) FROM Setoran"));
    }

    [Fact]
    public async Task Setoran_JenisNonaktif_Ditolak()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();

        var ex = await Assert.ThrowsAsync<ValidationException>(() =>
            env.Services.Transaksi.SimpanSetoranAsync(3, 16, 1m, null)); // Styrofoam (nonaktif)

        Assert.NotNull(ex["Jenis"]);
    }

    [Fact]
    public async Task Penarikan_MelebihiSaldo_DitolakDenganInfoSaldo()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();
        var saldo = (await env.Services.Nasabah.GetByIdAsync(3))!.Saldo;

        var ex = await Assert.ThrowsAsync<ValidationException>(() =>
            env.Services.Transaksi.SimpanPenarikanAsync(3, saldo + 1, null));

        Assert.Equal($"Saldo tidak cukup. Saldo tersedia {Format.Rupiah(saldo)}.", ex["Jumlah"]);
        Assert.Equal(saldo, (await env.Services.Nasabah.GetByIdAsync(3))!.Saldo);
    }

    [Fact]
    public async Task Penarikan_Valid_MengurangiSaldo()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();
        var saldo = (await env.Services.Nasabah.GetByIdAsync(3))!.Saldo;

        var penarikan = await env.Services.Transaksi.SimpanPenarikanAsync(3, 5000, null);

        Assert.StartsWith("TRK-", penarikan.NoTransaksi);
        Assert.Equal(saldo - 5000, (await env.Services.Nasabah.GetByIdAsync(3))!.Saldo);
    }

    [Fact]
    public async Task BatalkanPenarikan_SaldoKembali()
    {
        using var env = new TestEnvironment();
        await env.LoginAdminAsync();
        var saldo = (await env.Services.Nasabah.GetByIdAsync(3))!.Saldo;
        var penarikan = await env.Services.Transaksi.SimpanPenarikanAsync(3, 5000, null);
        var baris = (await env.Services.Transaksi.GetHariIniAsync()).Single(t => t.NoTransaksi == penarikan.NoTransaksi);

        await env.Services.Transaksi.BatalkanAsync(baris);

        Assert.Equal(saldo, (await env.Services.Nasabah.GetByIdAsync(3))!.Saldo);
        Assert.Equal(1, env.Scalar($"SELECT IsCancelled FROM Penarikan WHERE PenarikanId = {penarikan.PenarikanId}"));
    }

    [Fact]
    public async Task BatalkanSetoran_SaldoSudahDitarik_DitolakDanSaldoTetap()
    {
        using var env = new TestEnvironment();
        await env.LoginAdminAsync();
        var nasabah = await env.Services.Nasabah.SaveAsync(new Nasabah { NamaLengkap = "Uji Batal", RT = "1", RW = "1" });
        var setoran = await env.Services.Transaksi.SimpanSetoranAsync(nasabah.NasabahId, BotolPetBening, 2.5m, null);
        await env.Services.Transaksi.SimpanPenarikanAsync(nasabah.NasabahId, 10000, null);
        var baris = (await env.Services.Transaksi.GetHariIniAsync()).Single(t => t.NoTransaksi == setoran.NoTransaksi);

        var ex = await Assert.ThrowsAsync<AppException>(() => env.Services.Transaksi.BatalkanAsync(baris));

        Assert.Contains("tidak dapat dibatalkan", ex.Message);
        Assert.Equal(0, (await env.Services.Nasabah.GetByIdAsync(nasabah.NasabahId))!.Saldo);
        Assert.Equal(0, env.Scalar($"SELECT IsCancelled FROM Setoran WHERE SetoranId = {setoran.SetoranId}"));
    }

    [Fact]
    public async Task Petugas_TidakBolehMembatalkanTransaksi()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();
        var baris = (await env.Services.Transaksi.GetHariIniAsync()).First(t => !t.IsCancelled);

        await Assert.ThrowsAsync<UnauthorizedAccessException>(() => env.Services.Transaksi.BatalkanAsync(baris));
    }

    [Fact]
    public async Task Riwayat_FilterJenisDanTanggal()
    {
        using var env = new TestEnvironment();
        await env.LoginPetugasAsync();
        var hariIni = DateTime.Today;

        var setoran = await env.Services.Transaksi.GetRiwayatAsync(new RiwayatFilter(hariIni.AddDays(-30), hariIni, JenisTransaksi.Setoran));
        var tahunLalu = await env.Services.Transaksi.GetRiwayatAsync(new RiwayatFilter(hariIni.AddYears(-2), hariIni.AddYears(-1)));

        Assert.NotEmpty(setoran);
        Assert.All(setoran, t => Assert.True(t.IsSetoran && !t.IsCancelled));
        Assert.Empty(tahunLalu);
        await Assert.ThrowsAsync<ValidationException>(() =>
            env.Services.Transaksi.GetRiwayatAsync(new RiwayatFilter(hariIni, hariIni.AddDays(-1))));
    }
}
