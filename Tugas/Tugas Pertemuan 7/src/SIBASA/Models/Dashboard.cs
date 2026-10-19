namespace SIBASA.Models;

/// <summary>Ringkasan angka dan data grafik untuk halaman Dashboard.</summary>
public sealed class DashboardRingkasan
{
    public int NasabahAktif { get; set; }
    public int NasabahTerdaftar { get; set; }
    public double KgBulanIni { get; set; }
    public long NilaiBulanIni { get; set; }
    public long TotalSaldo { get; set; }
    public int SetoranHariIni { get; set; }
    public int PenarikanHariIni { get; set; }
    public int TransaksiHariIni => SetoranHariIni + PenarikanHariIni;

    public IReadOnlyList<DataBulanan> SetoranPerBulan { get; set; } = [];
    public IReadOnlyList<DataKategori> KomposisiKategori { get; set; } = [];
    public IReadOnlyList<Aktivitas> AktivitasTerbaru { get; set; } = [];
}

/// <summary>Total setoran dalam satu bulan.</summary>
public sealed record DataBulanan(DateTime Bulan, double Kg, long Nilai);

/// <summary>Total berat setoran per kategori. Urutan (KategoriId) menentukan warna grafik.</summary>
public sealed record DataKategori(int KategoriId, string Kategori, double Kg);
