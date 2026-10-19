using SIBASA.Data;
using SIBASA.Models;

namespace SIBASA.Repositories;

/// <summary>Query agregasi untuk kartu statistik, grafik, dan aktivitas di Dashboard.</summary>
public sealed class DashboardRepository : RepositoryBase
{
    public DashboardRepository(SqliteConnectionFactory factory) : base(factory)
    {
    }

    public Task<DashboardRingkasan> GetRingkasanAsync(DateTime sekarang, int jumlahBulan = 6, int jumlahAktivitas = 8) =>
        RunAsync(async c =>
        {
            var awalBulan = new DateTime(sekarang.Year, sekarang.Month, 1);
            var awalBulanDepan = awalBulan.AddMonths(1);
            var awalPeriode = awalBulan.AddMonths(-(jumlahBulan - 1));
            var hariIni = sekarang.Date;
            var besok = hariIni.AddDays(1);

            var ringkasan = new DashboardRingkasan
            {
                NasabahAktif = (int)await ScalarLongAsync(c, null,
                    "SELECT COUNT(*) FROM Nasabah WHERE IsActive = 1;"),
                NasabahTerdaftar = (int)await ScalarLongAsync(c, null,
                    "SELECT COUNT(*) FROM Nasabah;"),
                TotalSaldo = await ScalarLongAsync(c, null,
                    "SELECT IFNULL(SUM(Saldo), 0) FROM Nasabah;"),
                SetoranHariIni = (int)await ScalarLongAsync(c, null,
                    "SELECT COUNT(*) FROM Setoran WHERE IsCancelled = 0 AND Tanggal >= $a AND Tanggal < $b;",
                    ("$a", DbDate.ToDb(hariIni)), ("$b", DbDate.ToDb(besok))),
                PenarikanHariIni = (int)await ScalarLongAsync(c, null,
                    "SELECT COUNT(*) FROM Penarikan WHERE IsCancelled = 0 AND Tanggal >= $a AND Tanggal < $b;",
                    ("$a", DbDate.ToDb(hariIni)), ("$b", DbDate.ToDb(besok)))
            };

            // Total berat dan nilai setoran bulan berjalan.
            var bulanIni = await QueryAsync(c, null,
                """
                SELECT IFNULL(SUM(BeratKg), 0) AS Kg, IFNULL(SUM(Total), 0) AS Nilai
                FROM Setoran
                WHERE IsCancelled = 0 AND Tanggal >= $a AND Tanggal < $b;
                """,
                r => (Kg: r.Dbl("Kg"), Nilai: r.Long("Nilai")),
                ("$a", DbDate.ToDb(awalBulan)), ("$b", DbDate.ToDb(awalBulanDepan)));
            ringkasan.KgBulanIni = bulanIni[0].Kg;
            ringkasan.NilaiBulanIni = bulanIni[0].Nilai;

            // Grafik batang: total kg per bulan. Bulan tanpa setoran tetap ditampilkan dengan nilai 0.
            var perBulan = await QueryAsync(c, null,
                """
                SELECT strftime('%Y-%m', Tanggal) AS Bulan, SUM(BeratKg) AS Kg, SUM(Total) AS Nilai
                FROM Setoran
                WHERE IsCancelled = 0 AND Tanggal >= $a AND Tanggal < $b
                GROUP BY Bulan;
                """,
                r => (Bulan: r.Str("Bulan"), Kg: r.Dbl("Kg"), Nilai: r.Long("Nilai")),
                ("$a", DbDate.ToDb(awalPeriode)), ("$b", DbDate.ToDb(awalBulanDepan)));
            var lookup = perBulan.ToDictionary(x => x.Bulan);
            ringkasan.SetoranPerBulan = Enumerable.Range(0, jumlahBulan)
                .Select(i => awalPeriode.AddMonths(i))
                .Select(bulan => lookup.TryGetValue(bulan.ToString("yyyy-MM"), out var data)
                    ? new DataBulanan(bulan, data.Kg, data.Nilai)
                    : new DataBulanan(bulan, 0, 0))
                .ToList();

            // Grafik donat: komposisi berat per kategori pada periode yang sama.
            ringkasan.KomposisiKategori = await QueryAsync(c, null,
                """
                SELECT k.KategoriId, k.NamaKategori, SUM(s.BeratKg) AS Kg
                FROM Setoran s
                JOIN JenisSampah j ON j.JenisId = s.JenisId
                JOIN KategoriSampah k ON k.KategoriId = j.KategoriId
                WHERE s.IsCancelled = 0 AND s.Tanggal >= $a AND s.Tanggal < $b
                GROUP BY k.KategoriId
                ORDER BY k.KategoriId;
                """,
                r => new DataKategori(r.Int("KategoriId"), r.Str("NamaKategori"), r.Dbl("Kg")),
                ("$a", DbDate.ToDb(awalPeriode)), ("$b", DbDate.ToDb(awalBulanDepan)));

            var aktivitas = await AktivitasRepository.GetTerbaruAsync(c, jumlahAktivitas);
            aktivitas.ForEach(a => a.IsiWaktuRelatif(sekarang));
            ringkasan.AktivitasTerbaru = aktivitas;

            return ringkasan;
        });
}
