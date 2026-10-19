using System.Collections.ObjectModel;
using Avalonia.Media;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using LiveChartsCore;
using LiveChartsCore.SkiaSharpView;
using LiveChartsCore.SkiaSharpView.Painting;
using SIBASA.Helpers;
using SIBASA.Models;
using SkiaSharp;

namespace SIBASA.ViewModels;

/// <summary>Satu baris legenda grafik donat (warna + nama + berat + persentase).</summary>
public sealed record LegendItem(string Nama, string KgText, string PersenText, IBrush Warna);

/// <summary>
/// Dashboard: kartu statistik, grafik batang setoran per bulan, grafik donat komposisi kategori,
/// dan aktivitas terbaru. Semua angka berasal dari query database (bukan angka statis).
/// </summary>
public partial class DashboardViewModel : PageViewModelBase
{
    // Palet kategorikal tervalidasi aman buta warna (urutan tetap, warna mengikuti kategori, bukan peringkat).
    private static readonly string[] PaletTerang = ["#2A78D6", "#EB6834", "#1BAF7A", "#EDA100", "#E87BA4", "#008300", "#6250D6", "#E34948"];
    private static readonly string[] PaletGelap = ["#3987E5", "#D95926", "#199E70", "#C98500", "#D55181", "#008300", "#9085E9", "#E66767"];

    private DashboardRingkasan? _data;

    public DashboardViewModel(AppServices services) : base(services)
    {
        services.Ui.Theme.ThemeChanged += OnThemeChanged;
    }

    public override string Title => "Dashboard";

    public override string Subtitle => "Ringkasan kondisi bank sampah dari data terbaru";

    // Kartu statistik.
    [ObservableProperty] private string _nasabahAktifText = "0";
    [ObservableProperty] private string _nasabahAktifSub = string.Empty;
    [ObservableProperty] private string _kgBulanIniText = "0 kg";
    [ObservableProperty] private string _kgBulanIniSub = string.Empty;
    [ObservableProperty] private string _totalSaldoText = "Rp0";
    [ObservableProperty] private string _totalSaldoSub = "Tabungan seluruh nasabah";
    [ObservableProperty] private string _transaksiHariIniText = "0";
    [ObservableProperty] private string _transaksiHariIniSub = string.Empty;

    // Grafik.
    [ObservableProperty] private ISeries[] _barSeries = [];
    [ObservableProperty] private Axis[] _xAxes = [new Axis()];
    [ObservableProperty] private Axis[] _yAxes = [new Axis()];
    [ObservableProperty] private ISeries[] _pieSeries = [];
    [ObservableProperty] private SolidColorPaint _tooltipTextPaint = new(SKColors.Black);
    [ObservableProperty] private SolidColorPaint _tooltipBackgroundPaint = new(SKColors.White);
    [ObservableProperty] private bool _hasBarData;
    [ObservableProperty] private bool _hasPieData;
    [ObservableProperty] private string _periodeText = string.Empty;
    [ObservableProperty] private string _totalPeriodeText = string.Empty;

    [ObservableProperty] private bool _hasAktivitas;
    [ObservableProperty] private string _updatedText = string.Empty;

    public ObservableCollection<LegendItem> KomposisiLegend { get; } = new();

    public ObservableCollection<Aktivitas> AktivitasTerbaru { get; } = new();

    public override Task LoadAsync() => RunAsync(async () =>
    {
        _data = await Services.Dashboard.GetRingkasanAsync();
        TampilkanData(_data);
    }, isLoad: true);

    [RelayCommand]
    private Task RefreshAsync() => LoadAsync();

    public override void OnNavigatedFrom() => Services.Ui.Theme.ThemeChanged -= OnThemeChanged;

    private void OnThemeChanged(object? sender, EventArgs e) => BangunGrafik();

    private void TampilkanData(DashboardRingkasan data)
    {
        NasabahAktifText = data.NasabahAktif.ToString("N0", Format.Indonesia);
        NasabahAktifSub = $"dari {data.NasabahTerdaftar:N0} nasabah terdaftar";
        KgBulanIniText = Format.Kg(data.KgBulanIni);
        KgBulanIniSub = $"senilai {Format.Rupiah(data.NilaiBulanIni)}";
        TotalSaldoText = Format.Rupiah(data.TotalSaldo);
        TransaksiHariIniText = data.TransaksiHariIni.ToString("N0", Format.Indonesia);
        TransaksiHariIniSub = $"{data.SetoranHariIni} setoran · {data.PenarikanHariIni} penarikan";

        AktivitasTerbaru.Clear();
        foreach (var aktivitas in data.AktivitasTerbaru)
        {
            AktivitasTerbaru.Add(aktivitas);
        }

        HasAktivitas = AktivitasTerbaru.Count > 0;
        UpdatedText = $"Diperbarui {Format.Jam(DateTime.Now)}";
        BangunGrafik();
    }

    /// <summary>Menyusun ulang seri dan sumbu grafik. Dipanggil saat data dimuat dan saat tema berganti.</summary>
    private void BangunGrafik()
    {
        var gelap = Services.Ui.Theme.IsDark;
        var teksSekunder = SKColor.Parse(gelap ? "#94A3B8" : "#64748B");
        var teksUtama = SKColor.Parse(gelap ? "#E2E8F0" : "#0F172A");
        var garis = SKColor.Parse(gelap ? "#1E293B" : "#E2E8F0");
        var permukaan = SKColor.Parse(gelap ? "#111A2E" : "#FFFFFF");
        var palet = gelap ? PaletGelap : PaletTerang;

        TooltipTextPaint = new SolidColorPaint(teksUtama);
        TooltipBackgroundPaint = new SolidColorPaint(permukaan);

        // --- Grafik batang: satu seri (berat per bulan), satu warna, tanpa legenda. ---
        var bulanan = _data?.SetoranPerBulan ?? [];
        if (bulanan.Count > 0)
        {
            PeriodeText = $"{Format.BulanSingkat(bulanan[0].Bulan)} – {Format.BulanSingkat(bulanan[^1].Bulan)}";
        }

        HasBarData = bulanan.Any(b => b.Kg > 0);
        BarSeries =
        [
            new ColumnSeries<double>
            {
                Name = "Berat setoran",
                Values = bulanan.Select(b => Math.Round(b.Kg, 1)).ToArray(),
                Fill = new SolidColorPaint(SKColor.Parse(palet[0])),
                Rx = 4,
                Ry = 4,
                MaxBarWidth = 40,
                Padding = 8,
                YToolTipLabelFormatter = point => $"{Format.Angka(point.Coordinate.PrimaryValue, 1)} kg"
            }
        ];
        XAxes =
        [
            new Axis
            {
                Labels = bulanan.Select(b => Format.BulanSingkat(b.Bulan)).ToArray(),
                LabelsPaint = new SolidColorPaint(teksSekunder),
                TextSize = 12,
                ShowSeparatorLines = false
            }
        ];
        YAxes =
        [
            new Axis
            {
                MinLimit = 0,
                Labeler = value => $"{Format.Angka(value)} kg",
                LabelsPaint = new SolidColorPaint(teksSekunder),
                TextSize = 12,
                SeparatorsPaint = new SolidColorPaint(garis) { StrokeThickness = 1 }
            }
        ];

        // --- Grafik donat: komposisi kategori. Warna mengikuti kategori (KategoriId), bukan urutan besar-kecil. ---
        var komposisi = SusunKomposisi(_data?.KomposisiKategori ?? [], palet, gelap);
        var total = komposisi.Sum(k => k.Kg);
        HasPieData = total > 0;
        TotalPeriodeText = $"Total {Format.Kg(total)}";

        PieSeries = komposisi
            .Select(k => (ISeries)new PieSeries<double>
            {
                Name = k.Nama,
                Values = [Math.Round(k.Kg, 1)],
                Fill = new SolidColorPaint(SKColor.Parse(k.Warna)),
                Stroke = new SolidColorPaint(permukaan) { StrokeThickness = 2 }, // celah 2px antar-segmen
                InnerRadius = 56,
                HoverPushout = 6,
                ToolTipLabelFormatter = point =>
                    $"{Format.Angka(point.Coordinate.PrimaryValue, 1)} kg ({(point.StackedValue?.Share ?? 0).ToString("P0", Format.Indonesia)})"
            })
            .ToArray();

        KomposisiLegend.Clear();
        foreach (var k in komposisi)
        {
            var persen = total > 0 ? k.Kg / total : 0;
            KomposisiLegend.Add(new LegendItem(
                k.Nama,
                Format.Kg(k.Kg),
                persen.ToString("P0", Format.Indonesia),
                new SolidColorBrush(Color.Parse(k.Warna))));
        }
    }

    /// <summary>
    /// Memberi warna tetap per kategori: slot = (KategoriId - 1) mod 8.
    /// Bila dua kategori jatuh di slot yang sama (lebih dari 8 kategori), yang lebih kecil dilebur ke "Lainnya".
    /// </summary>
    private static List<(string Nama, double Kg, string Warna)> SusunKomposisi(
        IReadOnlyList<DataKategori> data, string[] palet, bool gelap)
    {
        var slotTerpakai = new HashSet<int>();
        var hasil = new List<(int Id, string Nama, double Kg, string Warna)>();
        double lainnya = 0;

        foreach (var kategori in data.Where(d => d.Kg > 0).OrderByDescending(d => d.Kg))
        {
            var slot = (kategori.KategoriId - 1) % palet.Length;
            if (slot < 0 || !slotTerpakai.Add(slot))
            {
                lainnya += kategori.Kg;
                continue;
            }

            hasil.Add((kategori.KategoriId, kategori.Kategori, kategori.Kg, palet[slot]));
        }

        var urut = hasil.OrderBy(h => h.Id).Select(h => (h.Nama, h.Kg, h.Warna)).ToList();
        if (lainnya > 0)
        {
            urut.Add(("Lainnya", lainnya, gelap ? "#64748B" : "#94A3B8"));
        }

        return urut;
    }
}
