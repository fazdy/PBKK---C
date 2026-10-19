using SIBASA.Helpers;
using SIBASA.Models;
using SIBASA.Repositories;

namespace SIBASA.Services;

/// <summary>Aturan bisnis transaksi setoran, penarikan, riwayat, dan pembatalan.</summary>
public sealed class TransaksiService : ServiceBase
{
    public const decimal BeratMinimum = 0.01m;
    public const decimal BeratMaksimum = 1000m;

    private readonly TransaksiRepository _transaksiRepo;
    private readonly NasabahRepository _nasabahRepo;
    private readonly JenisSampahRepository _jenisRepo;

    public TransaksiService(
        TransaksiRepository transaksiRepo,
        NasabahRepository nasabahRepo,
        JenisSampahRepository jenisRepo,
        AktivitasRepository aktivitas,
        SessionService session)
        : base(session, aktivitas)
    {
        _transaksiRepo = transaksiRepo;
        _nasabahRepo = nasabahRepo;
        _jenisRepo = jenisRepo;
    }

    /// <summary>Total setoran = berat × harga per kg, dibulatkan ke rupiah terdekat (0,5 ke atas).</summary>
    public static long HitungTotal(decimal beratKg, long hargaPerKg) =>
        (long)Math.Round(beratKg * hargaPerKg, 0, MidpointRounding.AwayFromZero);

    public async Task<Setoran> SimpanSetoranAsync(int? nasabahId, int? jenisId, decimal? beratKg, string? catatan)
    {
        var user = Session.RequireLogin();
        var berat = beratKg is null ? (decimal?)null : Math.Round(beratKg.Value, 2, MidpointRounding.AwayFromZero);

        var errors = new ErrorBag();
        if (nasabahId is null or <= 0)
        {
            errors.Add("Nasabah", "Pilih nasabah terlebih dahulu.");
        }

        if (jenisId is null or <= 0)
        {
            errors.Add("Jenis", "Pilih jenis sampah.");
        }

        if (berat is null)
        {
            errors.Add("Berat", "Berat wajib diisi.");
        }
        else if (berat < BeratMinimum || berat > BeratMaksimum)
        {
            errors.Add("Berat", "Berat harus antara 0,01 dan 1.000 kg.");
        }

        if (catatan is { Length: > 200 })
        {
            errors.Add("Catatan", "Catatan maksimal 200 karakter.");
        }

        errors.ThrowIfAny();

        // Harga diambil langsung dari database saat disimpan, bukan dari tampilan.
        var jenis = await _jenisRepo.GetByIdAsync(jenisId!.Value);
        if (jenis is null || !jenis.IsActive)
        {
            throw new ValidationException("Jenis", "Jenis sampah tidak ditemukan atau sudah dinonaktifkan.");
        }

        var setoran = new Setoran
        {
            NasabahId = nasabahId!.Value,
            JenisId = jenis.JenisId,
            BeratKg = (double)berat!.Value,
            HargaPerKg = jenis.HargaPerKg,
            Total = HitungTotal(berat.Value, jenis.HargaPerKg),
            Tanggal = DateTime.Now,
            UserId = user.UserId,
            Catatan = Bersihkan(catatan)
        };

        return await _transaksiRepo.SimpanSetoranAsync(setoran);
    }

    public async Task<Penarikan> SimpanPenarikanAsync(int? nasabahId, decimal? jumlah, string? catatan)
    {
        var user = Session.RequireLogin();

        var errors = new ErrorBag();
        if (nasabahId is null or <= 0)
        {
            errors.Add("Nasabah", "Pilih nasabah terlebih dahulu.");
        }

        if (jumlah is null)
        {
            errors.Add("Jumlah", "Jumlah penarikan wajib diisi.");
        }
        else if (jumlah <= 0)
        {
            errors.Add("Jumlah", "Jumlah penarikan harus lebih dari Rp0.");
        }
        else if (jumlah != decimal.Truncate(jumlah.Value))
        {
            errors.Add("Jumlah", "Jumlah penarikan harus bilangan bulat rupiah.");
        }

        if (catatan is { Length: > 200 })
        {
            errors.Add("Catatan", "Catatan maksimal 200 karakter.");
        }

        errors.ThrowIfAny();

        // Pemeriksaan awal agar pesan muncul di kolom; pemeriksaan final tetap di dalam transaksi database.
        var nasabah = await _nasabahRepo.GetByIdAsync(nasabahId!.Value)
            ?? throw new ValidationException("Nasabah", "Nasabah tidak ditemukan.");
        if (jumlah > nasabah.Saldo)
        {
            throw new ValidationException("Jumlah", $"Saldo tidak cukup. Saldo tersedia {Format.Rupiah(nasabah.Saldo)}.");
        }

        var penarikan = new Penarikan
        {
            NasabahId = nasabah.NasabahId,
            Jumlah = (long)jumlah!.Value,
            Tanggal = DateTime.Now,
            UserId = user.UserId,
            Catatan = Bersihkan(catatan)
        };

        return await _transaksiRepo.SimpanPenarikanAsync(penarikan);
    }

    /// <summary>Membatalkan transaksi. Hanya Admin.</summary>
    public Task<string> BatalkanAsync(RiwayatTransaksi transaksi)
    {
        var user = Session.RequireRole(UserRole.Admin);
        if (transaksi.IsCancelled)
        {
            throw new AppException($"Transaksi {transaksi.NoTransaksi} sudah dibatalkan sebelumnya.");
        }

        return _transaksiRepo.BatalkanAsync(transaksi.Jenis, transaksi.Id, user.UserId);
    }

    public Task<List<RiwayatTransaksi>> GetRiwayatAsync(RiwayatFilter filter)
    {
        Session.RequireLogin();
        if (filter.Dari.Date > filter.Sampai.Date)
        {
            throw new ValidationException("Tanggal", "Tanggal awal tidak boleh setelah tanggal akhir.");
        }

        return _transaksiRepo.GetRiwayatAsync(filter);
    }

    /// <summary>Transaksi hari ini (termasuk yang dibatalkan) untuk panel di halaman Transaksi.</summary>
    public Task<List<RiwayatTransaksi>> GetHariIniAsync(int batas = 10)
    {
        Session.RequireLogin();
        var hariIni = DateTime.Today;
        return _transaksiRepo.GetRiwayatAsync(new RiwayatFilter(hariIni, hariIni, TampilkanBatal: true), batas);
    }
}
