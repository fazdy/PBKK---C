using SIBASA.Helpers;

namespace SIBASA.Models;

/// <summary>Catatan aktivitas pengguna (tabel Aktivitas) untuk panel "Aktivitas terbaru".</summary>
public sealed class Aktivitas
{
    public int AktivitasId { get; set; }
    public int? UserId { get; set; }
    public string NamaUser { get; set; } = "Sistem";
    public string JenisAksi { get; set; } = string.Empty;
    public string Entitas { get; set; } = string.Empty;
    public int? EntitasId { get; set; }
    public string Keterangan { get; set; } = string.Empty;
    public DateTime Waktu { get; set; }

    /// <summary>Teks waktu relatif ("5 menit lalu"), dihitung saat data dimuat.</summary>
    public string WaktuText { get; set; } = string.Empty;

    public string Pelaku => $"oleh {NamaUser} · {WaktuText}";

    // Dipakai tampilan untuk memberi warna ikon sesuai jenis aksi (warna selalu disertai teks).
    public bool IsSetoran => JenisAksi == "Setoran";
    public bool IsPenarikan => JenisAksi == "Penarikan";
    public bool IsBahaya => JenisAksi is "Hapus" or "Batal" or "Nonaktifkan";
    public bool IsUmum => !IsSetoran && !IsPenarikan && !IsBahaya;

    public static Aktivitas Buat(int? userId, string jenisAksi, string entitas, int? entitasId, string keterangan) => new()
    {
        UserId = userId,
        JenisAksi = jenisAksi,
        Entitas = entitas,
        EntitasId = entitasId,
        Keterangan = keterangan,
        Waktu = DateTime.Now
    };

    public void IsiWaktuRelatif(DateTime sekarang) => WaktuText = Format.Relatif(Waktu, sekarang);
}
