using SIBASA.Helpers;

namespace SIBASA.Models;

public enum JenisTransaksi
{
    Setoran,
    Penarikan
}

/// <summary>Transaksi setor sampah (tabel Setoran).</summary>
public sealed class Setoran
{
    public int SetoranId { get; set; }
    public string NoTransaksi { get; set; } = string.Empty;
    public int NasabahId { get; set; }
    public int JenisId { get; set; }
    public double BeratKg { get; set; }

    /// <summary>Salinan harga saat transaksi, agar perubahan harga tidak mengubah transaksi lama.</summary>
    public long HargaPerKg { get; set; }

    public long Total { get; set; }
    public DateTime Tanggal { get; set; }
    public int UserId { get; set; }
    public string? Catatan { get; set; }
    public bool IsCancelled { get; set; }
    public DateTime? CancelledAt { get; set; }

    // Data tampilan (hasil join).
    public string NamaNasabah { get; set; } = string.Empty;
    public string NamaJenis { get; set; } = string.Empty;
}

/// <summary>Transaksi tarik saldo (tabel Penarikan).</summary>
public sealed class Penarikan
{
    public int PenarikanId { get; set; }
    public string NoTransaksi { get; set; } = string.Empty;
    public int NasabahId { get; set; }
    public long Jumlah { get; set; }
    public DateTime Tanggal { get; set; }
    public int UserId { get; set; }
    public string? Catatan { get; set; }
    public bool IsCancelled { get; set; }
    public DateTime? CancelledAt { get; set; }

    // Data tampilan (hasil join).
    public string NamaNasabah { get; set; } = string.Empty;
}

/// <summary>Baris gabungan setoran dan penarikan untuk halaman Riwayat dan Transaksi.</summary>
public sealed class RiwayatTransaksi
{
    public JenisTransaksi Jenis { get; set; }
    public int Id { get; set; }
    public string NoTransaksi { get; set; } = string.Empty;
    public DateTime Tanggal { get; set; }
    public int NasabahId { get; set; }
    public string NoRekening { get; set; } = string.Empty;
    public string NamaNasabah { get; set; } = string.Empty;
    public string? NamaJenisSampah { get; set; }
    public double? BeratKg { get; set; }
    public long? HargaPerKg { get; set; }
    public long Nominal { get; set; }
    public string Petugas { get; set; } = string.Empty;
    public string? Catatan { get; set; }
    public bool IsCancelled { get; set; }

    public bool IsSetoran => Jenis == JenisTransaksi.Setoran;
    public string JenisText => IsSetoran ? "Setoran" : "Penarikan";
    public string StatusText => IsCancelled ? "Dibatalkan" : "Berhasil";

    /// <summary>Nominal bertanda: setoran positif, penarikan negatif (dipakai untuk pengurutan).</summary>
    public long NominalBertanda => IsSetoran ? Nominal : -Nominal;

    public string NominalText => (IsSetoran ? "+" : "−") + Format.Rupiah(Nominal);

    public string Rincian => IsSetoran
        ? $"{NamaJenisSampah} · {Format.Kg(BeratKg ?? 0)} × {Format.Rupiah(HargaPerKg ?? 0)}"
        : string.IsNullOrWhiteSpace(Catatan) ? "Penarikan saldo" : Catatan!;

    public string TanggalText => Format.Tanggal(Tanggal);
    public string JamText => Format.Jam(Tanggal);
    public string NasabahText => $"{NamaNasabah} ({NoRekening})";
}
