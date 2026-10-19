using System.Text.RegularExpressions;
using SIBASA.Helpers;
using SIBASA.Models;
using SIBASA.Repositories;

namespace SIBASA.Services;

/// <summary>Aturan bisnis pengelolaan nasabah: validasi, simpan, nonaktifkan, dan hapus.</summary>
public sealed partial class NasabahService : ServiceBase
{
    private readonly NasabahRepository _repo;

    public NasabahService(NasabahRepository repo, AktivitasRepository aktivitas, SessionService session)
        : base(session, aktivitas)
    {
        _repo = repo;
    }

    public Task<List<Nasabah>> GetAllAsync(NasabahFilter filter)
    {
        Session.RequireLogin();
        return _repo.GetAllAsync(filter);
    }

    public Task<List<Nasabah>> GetAktifAsync(string? kataKunci)
    {
        Session.RequireLogin();
        return _repo.GetAktifAsync(kataKunci);
    }

    public Task<Nasabah?> GetByIdAsync(int nasabahId)
    {
        Session.RequireLogin();
        return _repo.GetByIdAsync(nasabahId);
    }

    public Task<List<string>> GetRwListAsync()
    {
        Session.RequireLogin();
        return _repo.GetRwListAsync();
    }

    /// <summary>Menyimpan nasabah baru (NasabahId = 0) atau perubahan data nasabah.</summary>
    public async Task<Nasabah> SaveAsync(Nasabah input)
    {
        Session.RequireLogin();
        var data = Normalisasi(input);
        Validasi(data);

        if (data.NasabahId == 0)
        {
            var (id, noRekening) = await _repo.InsertAsync(data);
            data.NasabahId = id;
            data.NoRekening = noRekening;
            await CatatAktivitasAsync("Tambah", "Nasabah", id, $"Menambah nasabah {noRekening} · {data.NamaLengkap}");
            return data;
        }

        var lama = await _repo.GetByIdAsync(data.NasabahId)
            ?? throw new AppException("Data nasabah tidak ditemukan. Mungkin sudah dihapus.");

        if (lama.IsActive && !data.IsActive && lama.Saldo > 0)
        {
            throw new ValidationException("IsActive",
                $"Saldo masih {Format.Rupiah(lama.Saldo)}, lakukan penarikan terlebih dahulu sebelum menonaktifkan.");
        }

        await _repo.UpdateAsync(data);
        data.NoRekening = lama.NoRekening;
        data.Saldo = lama.Saldo;
        await CatatAktivitasAsync("Ubah", "Nasabah", data.NasabahId, $"Mengubah data nasabah {lama.NoRekening} · {data.NamaLengkap}");
        return data;
    }

    /// <summary>Mengaktifkan atau menonaktifkan nasabah. Nasabah bersaldo tidak boleh dinonaktifkan.</summary>
    public async Task SetActiveAsync(int nasabahId, bool aktif)
    {
        Session.RequireLogin();
        var nasabah = await _repo.GetByIdAsync(nasabahId)
            ?? throw new AppException("Data nasabah tidak ditemukan. Mungkin sudah dihapus.");

        if (!aktif && nasabah.Saldo > 0)
        {
            throw new AppException(
                $"Saldo masih {Format.Rupiah(nasabah.Saldo)}, lakukan penarikan terlebih dahulu sebelum menonaktifkan.");
        }

        await _repo.SetActiveAsync(nasabahId, aktif);
        await CatatAktivitasAsync(aktif ? "Aktifkan" : "Nonaktifkan", "Nasabah", nasabahId,
            $"{(aktif ? "Mengaktifkan" : "Menonaktifkan")} nasabah {nasabah.NoRekening} · {nasabah.NamaLengkap}");
    }

    /// <summary>Menghapus nasabah. Nasabah yang sudah punya transaksi tidak boleh dihapus.</summary>
    public async Task DeleteAsync(int nasabahId)
    {
        Session.RequireLogin();
        var nasabah = await _repo.GetByIdAsync(nasabahId)
            ?? throw new AppException("Data nasabah tidak ditemukan. Mungkin sudah dihapus.");

        if (await _repo.HasTransactionsAsync(nasabahId))
        {
            throw new AppException("Nasabah sudah memiliki transaksi sehingga tidak dapat dihapus. Nonaktifkan saja.");
        }

        await _repo.DeleteAsync(nasabahId);
        await CatatAktivitasAsync("Hapus", "Nasabah", nasabahId, $"Menghapus nasabah {nasabah.NoRekening} · {nasabah.NamaLengkap}");
    }

    /// <summary>Merapikan input: spasi berlebih dihapus, RT/RW dua digit, nomor HP tanpa spasi/strip.</summary>
    public static Nasabah Normalisasi(Nasabah input) => new()
    {
        NasabahId = input.NasabahId,
        NoRekening = input.NoRekening,
        NamaLengkap = SpasiGanda().Replace(input.NamaLengkap?.Trim() ?? string.Empty, " "),
        NoHp = string.IsNullOrWhiteSpace(input.NoHp) ? null : PemisahNomor().Replace(input.NoHp.Trim(), string.Empty),
        Alamat = Bersihkan(input.Alamat),
        RT = DuaDigit(input.RT),
        RW = DuaDigit(input.RW),
        Saldo = input.Saldo,
        IsActive = input.IsActive,
        TanggalDaftar = input.TanggalDaftar
    };

    /// <summary>Memeriksa semua kolom sekaligus dan melempar ValidationException berisi pesan per kolom.</summary>
    public static void Validasi(Nasabah n)
    {
        var errors = new ErrorBag();

        if (string.IsNullOrWhiteSpace(n.NamaLengkap))
        {
            errors.Add(nameof(Nasabah.NamaLengkap), "Nama lengkap wajib diisi.");
        }
        else if (n.NamaLengkap.Length is < 3 or > 100)
        {
            errors.Add(nameof(Nasabah.NamaLengkap), "Nama lengkap harus 3–100 karakter.");
        }

        if (n.NoHp is not null && !FormatNoHp().IsMatch(n.NoHp))
        {
            errors.Add(nameof(Nasabah.NoHp), "No. HP harus 10–15 digit dan diawali 08 atau +62.");
        }

        if (n.Alamat is { Length: > 200 })
        {
            errors.Add(nameof(Nasabah.Alamat), "Alamat maksimal 200 karakter.");
        }

        if (!FormatRtRw().IsMatch(n.RT) || int.Parse(n.RT) < 1)
        {
            errors.Add(nameof(Nasabah.RT), "RT wajib berupa angka 1–3 digit.");
        }

        if (!FormatRtRw().IsMatch(n.RW) || int.Parse(n.RW) < 1)
        {
            errors.Add(nameof(Nasabah.RW), "RW wajib berupa angka 1–3 digit.");
        }

        errors.ThrowIfAny();
    }

    private static string DuaDigit(string? nilai)
    {
        var teks = nilai?.Trim() ?? string.Empty;
        return int.TryParse(teks, out var angka) && angka is >= 0 and <= 999 && FormatRtRw().IsMatch(teks)
            ? angka.ToString("00")
            : teks;
    }

    [GeneratedRegex(@"^(08\d{8,13}|\+628\d{7,12})$")]
    private static partial Regex FormatNoHp();

    [GeneratedRegex(@"^\d{1,3}$")]
    private static partial Regex FormatRtRw();

    [GeneratedRegex(@"[\s\-\.]")]
    private static partial Regex PemisahNomor();

    [GeneratedRegex(@"\s{2,}")]
    private static partial Regex SpasiGanda();
}
