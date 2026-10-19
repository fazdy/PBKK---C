using SIBASA.Helpers;
using SIBASA.Models;
using SIBASA.Repositories;

namespace SIBASA.Services;

/// <summary>
/// Aturan bisnis kategori dan jenis sampah. Semua orang yang login boleh melihat,
/// tetapi hanya Admin yang boleh menambah, mengubah, atau menghapus (dicek di sini, bukan hanya di UI).
/// </summary>
public sealed class JenisSampahService : ServiceBase
{
    public const long HargaMinimum = 1;
    public const long HargaMaksimum = 1_000_000;

    private readonly JenisSampahRepository _jenisRepo;
    private readonly KategoriSampahRepository _kategoriRepo;

    public JenisSampahService(
        JenisSampahRepository jenisRepo,
        KategoriSampahRepository kategoriRepo,
        AktivitasRepository aktivitas,
        SessionService session)
        : base(session, aktivitas)
    {
        _jenisRepo = jenisRepo;
        _kategoriRepo = kategoriRepo;
    }

    public Task<List<JenisSampah>> GetJenisAsync(JenisSampahFilter filter)
    {
        Session.RequireLogin();
        return _jenisRepo.GetAllAsync(filter);
    }

    public Task<List<JenisSampah>> GetJenisAktifAsync()
    {
        Session.RequireLogin();
        return _jenisRepo.GetAktifAsync();
    }

    public Task<List<KategoriSampah>> GetKategoriAsync()
    {
        Session.RequireLogin();
        return _kategoriRepo.GetAllAsync();
    }

    public async Task<JenisSampah> SaveJenisAsync(JenisSampah input)
    {
        Session.RequireRole(UserRole.Admin);

        var data = new JenisSampah
        {
            JenisId = input.JenisId,
            KategoriId = input.KategoriId,
            NamaJenis = input.NamaJenis?.Trim() ?? string.Empty,
            HargaPerKg = input.HargaPerKg,
            IsActive = input.IsActive
        };

        var errors = new ErrorBag();
        if (data.NamaJenis.Length is < 2 or > 60)
        {
            errors.Add(nameof(JenisSampah.NamaJenis), "Nama jenis wajib diisi (2–60 karakter).");
        }

        if (data.KategoriId <= 0)
        {
            errors.Add(nameof(JenisSampah.KategoriId), "Pilih kategori sampah.");
        }

        if (data.HargaPerKg is < HargaMinimum or > HargaMaksimum)
        {
            errors.Add(nameof(JenisSampah.HargaPerKg), "Harga harus bilangan bulat Rp1–Rp1.000.000 per kg.");
        }

        if (!errors.HasErrors && await _jenisRepo.NameExistsAsync(data.NamaJenis, data.JenisId == 0 ? null : data.JenisId))
        {
            errors.Add(nameof(JenisSampah.NamaJenis), "Nama jenis sudah dipakai.");
        }

        errors.ThrowIfAny();

        if (data.JenisId == 0)
        {
            data.JenisId = await _jenisRepo.InsertAsync(data);
            await CatatAktivitasAsync("Tambah", "JenisSampah", data.JenisId,
                $"Menambah jenis sampah {data.NamaJenis} · {Format.Rupiah(data.HargaPerKg)}/kg");
        }
        else
        {
            var lama = await _jenisRepo.GetByIdAsync(data.JenisId)
                ?? throw new AppException("Jenis sampah tidak ditemukan. Mungkin sudah dihapus.");
            await _jenisRepo.UpdateAsync(data);
            var keterangan = lama.HargaPerKg != data.HargaPerKg
                ? $"Mengubah harga {data.NamaJenis}: {Format.Rupiah(lama.HargaPerKg)} → {Format.Rupiah(data.HargaPerKg)}/kg"
                : $"Mengubah jenis sampah {data.NamaJenis}";
            await CatatAktivitasAsync("Ubah", "JenisSampah", data.JenisId, keterangan);
        }

        return data;
    }

    public async Task DeleteJenisAsync(int jenisId)
    {
        Session.RequireRole(UserRole.Admin);
        var jenis = await _jenisRepo.GetByIdAsync(jenisId)
            ?? throw new AppException("Jenis sampah tidak ditemukan. Mungkin sudah dihapus.");

        if (await _jenisRepo.IsUsedAsync(jenisId))
        {
            throw new AppException($"{jenis.NamaJenis} sudah dipakai pada setoran sehingga tidak dapat dihapus. Nonaktifkan saja.");
        }

        await _jenisRepo.DeleteAsync(jenisId);
        await CatatAktivitasAsync("Hapus", "JenisSampah", jenisId, $"Menghapus jenis sampah {jenis.NamaJenis}");
    }

    public async Task<KategoriSampah> SaveKategoriAsync(KategoriSampah input)
    {
        Session.RequireRole(UserRole.Admin);

        var data = new KategoriSampah
        {
            KategoriId = input.KategoriId,
            NamaKategori = input.NamaKategori?.Trim() ?? string.Empty,
            Keterangan = Bersihkan(input.Keterangan)
        };

        var errors = new ErrorBag();
        if (data.NamaKategori.Length is < 2 or > 40)
        {
            errors.Add(nameof(KategoriSampah.NamaKategori), "Nama kategori wajib diisi (2–40 karakter).");
        }

        if (data.Keterangan is { Length: > 150 })
        {
            errors.Add(nameof(KategoriSampah.Keterangan), "Keterangan maksimal 150 karakter.");
        }

        if (!errors.HasErrors && await _kategoriRepo.NameExistsAsync(data.NamaKategori, data.KategoriId == 0 ? null : data.KategoriId))
        {
            errors.Add(nameof(KategoriSampah.NamaKategori), "Nama kategori sudah dipakai.");
        }

        errors.ThrowIfAny();

        if (data.KategoriId == 0)
        {
            data.KategoriId = await _kategoriRepo.InsertAsync(data);
            await CatatAktivitasAsync("Tambah", "Kategori", data.KategoriId, $"Menambah kategori {data.NamaKategori}");
        }
        else
        {
            await _kategoriRepo.UpdateAsync(data);
            await CatatAktivitasAsync("Ubah", "Kategori", data.KategoriId, $"Mengubah kategori {data.NamaKategori}");
        }

        return data;
    }

    public async Task DeleteKategoriAsync(KategoriSampah kategori)
    {
        Session.RequireRole(UserRole.Admin);
        if (await _kategoriRepo.HasJenisAsync(kategori.KategoriId))
        {
            throw new AppException($"Kategori {kategori.NamaKategori} masih memiliki jenis sampah. Pindahkan atau hapus jenisnya terlebih dahulu.");
        }

        await _kategoriRepo.DeleteAsync(kategori.KategoriId);
        await CatatAktivitasAsync("Hapus", "Kategori", kategori.KategoriId, $"Menghapus kategori {kategori.NamaKategori}");
    }
}
