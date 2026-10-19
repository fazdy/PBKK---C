using SIBASA.Helpers;

namespace SIBASA.Models;

/// <summary>Warga yang menabung sampah (tabel Nasabah).</summary>
public sealed class Nasabah
{
    public int NasabahId { get; set; }
    public string NoRekening { get; set; } = string.Empty;
    public string NamaLengkap { get; set; } = string.Empty;
    public string? NoHp { get; set; }
    public string? Alamat { get; set; }
    public string RT { get; set; } = string.Empty;
    public string RW { get; set; } = string.Empty;

    /// <summary>Saldo dalam rupiah. Hanya berubah lewat transaksi setoran/penarikan.</summary>
    public long Saldo { get; set; }

    public bool IsActive { get; set; } = true;
    public DateTime TanggalDaftar { get; set; }

    /// <summary>Jumlah transaksi (setoran + penarikan), diisi saat menampilkan daftar.</summary>
    public int JumlahTransaksi { get; set; }

    public string RtRw => $"{RT}/{RW}";
    public string StatusText => IsActive ? "Aktif" : "Nonaktif";
    public string SaldoText => Format.Rupiah(Saldo);
    public string NoHpText => string.IsNullOrWhiteSpace(NoHp) ? "-" : NoHp;
    public string AlamatText => string.IsNullOrWhiteSpace(Alamat) ? "-" : Alamat;
    public string Inisial => Format.Inisial(NamaLengkap);
    public string Ringkasan => $"{NoRekening} · RT {RT}/RW {RW}";
}
