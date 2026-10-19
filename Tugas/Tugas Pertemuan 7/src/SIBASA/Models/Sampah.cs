using SIBASA.Helpers;

namespace SIBASA.Models;

/// <summary>Kelompok jenis sampah (tabel KategoriSampah).</summary>
public sealed class KategoriSampah
{
    public int KategoriId { get; set; }
    public string NamaKategori { get; set; } = string.Empty;
    public string? Keterangan { get; set; }

    /// <summary>Jumlah jenis sampah dalam kategori ini, diisi saat menampilkan daftar.</summary>
    public int JumlahJenis { get; set; }

    public string KeteranganText => string.IsNullOrWhiteSpace(Keterangan) ? "-" : Keterangan;

    public override string ToString() => NamaKategori;
}

/// <summary>Jenis sampah beserta harga per kg (tabel JenisSampah).</summary>
public sealed class JenisSampah
{
    public int JenisId { get; set; }
    public int KategoriId { get; set; }
    public string NamaKategori { get; set; } = string.Empty;
    public string NamaJenis { get; set; } = string.Empty;
    public long HargaPerKg { get; set; }
    public bool IsActive { get; set; } = true;

    /// <summary>Berapa kali jenis ini dipakai pada setoran (untuk aturan hapus).</summary>
    public int JumlahPemakaian { get; set; }

    public string HargaText => Format.Rupiah(HargaPerKg) + "/kg";
    public string StatusText => IsActive ? "Aktif" : "Nonaktif";

    public override string ToString() => NamaJenis;
}
