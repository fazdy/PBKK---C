using System.Collections.ObjectModel;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Helpers;
using SIBASA.Models;

namespace SIBASA.ViewModels;

/// <summary>Halaman Riwayat Transaksi: filter periode/jenis/kata kunci, ringkasan total, dan pembatalan (Admin).</summary>
public partial class RiwayatViewModel : PageViewModelBase
{
    public const string SemuaJenis = "Semua jenis";
    private const int BatasBaris = 2000;

    private CancellationTokenSource? _cariCts;

    public RiwayatViewModel(AppServices services) : base(services)
    {
    }

    private static DateTime AwalBulanIni => new(DateTime.Today.Year, DateTime.Today.Month, 1);

    public override string Title => "Riwayat Transaksi";

    public override string Subtitle => IsAdmin
        ? "Telusuri setoran dan penarikan, lalu batalkan transaksi yang salah input"
        : "Telusuri setoran dan penarikan per periode";

    public ObservableCollection<RiwayatTransaksi> Items { get; } = new();

    public IReadOnlyList<string> JenisOptions { get; } = [SemuaJenis, "Setoran", "Penarikan"];

    // Default: awal bulan berjalan sampai hari ini.
    [ObservableProperty] private DateTime? _dari = AwalBulanIni;
    [ObservableProperty] private DateTime? _sampai = DateTime.Today;
    [ObservableProperty] private string _selectedJenis = SemuaJenis;
    [ObservableProperty] private string _kataKunci = string.Empty;
    [ObservableProperty] private bool _tampilkanBatal;

    [ObservableProperty]
    [NotifyCanExecuteChangedFor(nameof(BatalkanCommand))]
    private RiwayatTransaksi? _selected;

    [ObservableProperty] private bool _isEmpty;
    [ObservableProperty] private string _emptyText = string.Empty;
    [ObservableProperty] private string _jumlahText = "0";
    [ObservableProperty] private string _totalKgText = "0 kg";
    [ObservableProperty] private string _totalSetoranText = "Rp0";
    [ObservableProperty] private string _totalPenarikanText = "Rp0";
    [ObservableProperty] private string _resultText = string.Empty;

    partial void OnDariChanged(DateTime? value) => _ = CariUlangAsync(0);
    partial void OnSampaiChanged(DateTime? value) => _ = CariUlangAsync(0);
    partial void OnSelectedJenisChanged(string value) => _ = CariUlangAsync(0);
    partial void OnTampilkanBatalChanged(bool value) => _ = CariUlangAsync(0);
    partial void OnKataKunciChanged(string value) => _ = CariUlangAsync(300);

    public override Task LoadAsync() => RunAsync(MuatAsync, isLoad: true);

    [RelayCommand]
    private Task RefreshAsync() => LoadAsync();

    [RelayCommand]
    private void ResetFilter()
    {
        Dari = AwalBulanIni;
        Sampai = DateTime.Today;
        SelectedJenis = SemuaJenis;
        KataKunci = string.Empty;
        TampilkanBatal = false;
    }

    [RelayCommand]
    private void PilihPeriode(string? periode)
    {
        var hariIni = DateTime.Today;
        (Dari, Sampai) = periode switch
        {
            "hari" => (hariIni, hariIni),
            "7hari" => (hariIni.AddDays(-6), hariIni),
            "30hari" => (hariIni.AddDays(-29), hariIni),
            "tahun" => (new DateTime(hariIni.Year, 1, 1), hariIni),
            _ => (new DateTime(hariIni.Year, hariIni.Month, 1), hariIni)
        };
    }

    private bool BisaDibatalkan() => IsAdmin && Selected is { IsCancelled: false };

    [RelayCommand(CanExecute = nameof(BisaDibatalkan))]
    private async Task BatalkanAsync()
    {
        if (Selected is not { } transaksi)
        {
            return;
        }

        var efek = transaksi.IsSetoran
            ? $"Saldo {transaksi.NamaNasabah} akan berkurang {Format.Rupiah(transaksi.Nominal)}."
            : $"Saldo {transaksi.NamaNasabah} akan bertambah kembali {Format.Rupiah(transaksi.Nominal)}.";
        var setuju = await Dialog.ConfirmAsync(
            $"Batalkan {transaksi.JenisText.ToLowerInvariant()} {transaksi.NoTransaksi}?",
            $"{efek} Transaksi tetap tersimpan dengan status \"Dibatalkan\".",
            "Batalkan transaksi",
            "Kembali",
            isDanger: true);
        if (!setuju)
        {
            return;
        }

        await RunAsync(async () =>
        {
            var noTransaksi = await Services.Transaksi.BatalkanAsync(transaksi);
            Notifications.Success($"Transaksi {noTransaksi} dibatalkan dan saldo nasabah sudah dikoreksi.");
            await MuatAsync();
        });
    }

    private async Task CariUlangAsync(int jeda)
    {
        _cariCts?.Cancel();
        var cts = _cariCts = new CancellationTokenSource();
        if (jeda > 0 && !await DebounceAsync(cts.Token, jeda))
        {
            return;
        }

        await RunAsync(MuatAsync);
    }

    private async Task MuatAsync()
    {
        var dari = Dari ?? DateTime.Today;
        var sampai = Sampai ?? DateTime.Today;
        JenisTransaksi? jenis = SelectedJenis switch
        {
            "Setoran" => JenisTransaksi.Setoran,
            "Penarikan" => JenisTransaksi.Penarikan,
            _ => null
        };

        var idTerpilih = Selected is { } s ? (s.Jenis, s.Id) : default((JenisTransaksi, int)?);
        var daftar = await Services.Transaksi.GetRiwayatAsync(new RiwayatFilter(dari, sampai, jenis, KataKunci, TampilkanBatal));

        Items.Clear();
        foreach (var transaksi in daftar)
        {
            Items.Add(transaksi);
        }

        Selected = idTerpilih is { } id ? Items.FirstOrDefault(t => t.Jenis == id.Item1 && t.Id == id.Item2) : null;

        // Ringkasan hanya menghitung transaksi yang tidak dibatalkan.
        var berhasil = Items.Where(t => !t.IsCancelled).ToList();
        JumlahText = berhasil.Count.ToString("N0", Format.Indonesia);
        TotalKgText = Format.Kg(berhasil.Where(t => t.IsSetoran).Sum(t => t.BeratKg ?? 0));
        TotalSetoranText = Format.Rupiah(berhasil.Where(t => t.IsSetoran).Sum(t => t.Nominal));
        TotalPenarikanText = Format.Rupiah(berhasil.Where(t => !t.IsSetoran).Sum(t => t.Nominal));

        IsEmpty = Items.Count == 0;
        EmptyText = string.IsNullOrWhiteSpace(KataKunci)
            ? "Tidak ada transaksi pada periode ini"
            : "Tidak ada transaksi yang cocok dengan pencarian";
        ResultText = Items.Count >= BatasBaris
            ? $"Menampilkan {BatasBaris:N0} transaksi terbaru. Persempit periode untuk melihat sisanya."
            : $"Menampilkan {Items.Count:N0} transaksi ({Format.TanggalSaja(dari)} – {Format.TanggalSaja(sampai)})";
    }
}
