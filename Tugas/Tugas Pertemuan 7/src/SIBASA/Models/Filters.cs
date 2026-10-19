namespace SIBASA.Models;

/// <summary>Kriteria pencarian nasabah. Nilai null berarti "semua".</summary>
public sealed record NasabahFilter(string? KataKunci = null, string? RW = null, bool? IsActive = null);

/// <summary>Kriteria pencarian jenis sampah. KategoriId null berarti semua kategori.</summary>
public sealed record JenisSampahFilter(string? KataKunci = null, int? KategoriId = null);

/// <summary>Kriteria riwayat transaksi. Rentang tanggal inklusif (Dari 00:00 sampai Sampai 23:59).</summary>
public sealed record RiwayatFilter(
    DateTime Dari,
    DateTime Sampai,
    JenisTransaksi? Jenis = null,
    string? KataKunci = null,
    bool TampilkanBatal = false);
