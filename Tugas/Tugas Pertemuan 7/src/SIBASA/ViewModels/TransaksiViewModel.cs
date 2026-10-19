using System.Collections.ObjectModel;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Helpers;
using SIBASA.Models;
using SIBASA.Services;

namespace SIBASA.ViewModels;

/// <summary>
/// Halaman Transaksi: pilih nasabah di panel kiri, lalu catat setoran atau penarikan di panel kanan.
/// Total setoran (berat × harga) dihitung otomatis sebelum disimpan.
/// </summary>
public partial class TransaksiViewModel : PageViewModelBase
{
    private CancellationTokenSource? _cariCts;

    public TransaksiViewModel(AppServices services) : base(services)
    {
    }

    public override string Title => "Transaksi";

    public override string Subtitle => "Catat setoran sampah dan penarikan saldo nasabah";

    public ObservableCollection<Nasabah> NasabahList { get; } = new();

    public ObservableCollection<JenisSampah> JenisList { get; } = new();

    public ObservableCollection<RiwayatTransaksi> HariIni { get; } = new();

    // ---------------- Pemilihan nasabah ----------------
    [ObservableProperty] private string _kataKunciNasabah = string.Empty;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(HasNasabah), nameof(NamaNasabahText), nameof(InfoNasabahText),
        nameof(SaldoText), nameof(SisaSaldoText), nameof(IsSisaMinus))]
    private Nasabah? _selectedNasabah;

    [ObservableProperty] private bool _isNasabahListEmpty;
    [ObservableProperty] private string _nasabahEmptyText = string.Empty;

    public bool HasNasabah => SelectedNasabah is not null;
    public string NamaNasabahText => SelectedNasabah?.NamaLengkap ?? "Belum ada nasabah dipilih";
    public string InfoNasabahText => SelectedNasabah?.Ringkasan ?? "Pilih nasabah dari daftar di sebelah kiri.";
    public string SaldoText => Format.Rupiah(SelectedNasabah?.Saldo ?? 0);

    partial void OnSelectedNasabahChanged(Nasabah? value)
    {
        NasabahError = null;
        JumlahError = null;
    }

    partial void OnKataKunciNasabahChanged(string value) => _ = CariNasabahUlangAsync();

    // ---------------- Form setoran ----------------
    [ObservableProperty] private int _selectedTabIndex;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(HargaText), nameof(TotalText))]
    private JenisSampah? _selectedJenis;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(TotalText), nameof(BeratText))]
    private decimal? _berat;

    [ObservableProperty] private string? _catatanSetoran;
    [ObservableProperty] private bool _isJenisEmpty;

    public string HargaText => SelectedJenis is null ? "-" : SelectedJenis.HargaText;
    public string BeratText => Berat is { } b ? Format.Kg(b) : "-";

    /// <summary>Pratinjau total; perhitungan final tetap dilakukan Service dengan harga terbaru dari database.</summary>
    public string TotalText => SelectedJenis is not null && Berat is > 0
        ? Format.Rupiah(TransaksiService.HitungTotal(Math.Round(Berat.Value, 2, MidpointRounding.AwayFromZero), SelectedJenis.HargaPerKg))
        : Format.Rupiah(0);

    partial void OnSelectedJenisChanged(JenisSampah? value) => JenisError = null;
    partial void OnBeratChanged(decimal? value) => BeratError = null;
    partial void OnCatatanSetoranChanged(string? value) => CatatanError = null;

    // ---------------- Form penarikan ----------------
    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(SisaSaldoText), nameof(IsSisaMinus))]
    private decimal? _jumlahPenarikan;

    [ObservableProperty] private string? _catatanPenarikan;

    public string SisaSaldoText => Format.Rupiah(SisaSaldo);

    /// <summary>True bila jumlah penarikan melebihi saldo (pratinjau ditampilkan merah).</summary>
    public bool IsSisaMinus => SisaSaldo < 0;

    private long SisaSaldo
    {
        get
        {
            var saldo = SelectedNasabah?.Saldo ?? 0;
            var jumlah = JumlahPenarikan is { } j && j > 0 ? (long)decimal.Truncate(Math.Min(j, long.MaxValue / 2)) : 0;
            return saldo - jumlah;
        }
    }

    partial void OnJumlahPenarikanChanged(decimal? value) => JumlahError = null;
    partial void OnCatatanPenarikanChanged(string? value) => CatatanError = null;

    // ---------------- Pesan error kolom ----------------
    [ObservableProperty] private string? _nasabahError;
    [ObservableProperty] private string? _jenisError;
    [ObservableProperty] private string? _beratError;
    [ObservableProperty] private string? _jumlahError;
    [ObservableProperty] private string? _catatanError;

    [ObservableProperty] private bool _hasHariIni;

    public override Task LoadAsync() => RunAsync(async () =>
    {
        await MuatNasabahAsync();
        await MuatJenisAsync();
        await MuatHariIniAsync();
    }, isLoad: true);

    [RelayCommand]
    private Task RefreshAsync() => LoadAsync();

    [RelayCommand]
    private async Task SimpanSetoranAsync()
    {
        var nasabahId = SelectedNasabah?.NasabahId;
        await RunAsync(async () =>
        {
            var setoran = await Services.Transaksi.SimpanSetoranAsync(nasabahId, SelectedJenis?.JenisId, Berat, CatatanSetoran);
            Notifications.Success(
                $"Setoran {setoran.NoTransaksi} tersimpan: {setoran.NamaJenis} {Format.Kg(setoran.BeratKg)}. " +
                $"Saldo {setoran.NamaNasabah} bertambah {Format.Rupiah(setoran.Total)}.");
            SelectedJenis = null;
            Berat = null;
            CatatanSetoran = null;
            await MuatNasabahAsync(setoran.NasabahId);
            await MuatHariIniAsync();
        }, TampilkanErrorKolom);
    }

    [RelayCommand]
    private async Task SimpanPenarikanAsync()
    {
        var nasabah = SelectedNasabah;
        var jumlah = JumlahPenarikan;

        // Konfirmasi hanya ditanyakan bila isian sudah masuk akal; validasi lengkap tetap di Service.
        if (nasabah is not null && jumlah is > 0 && jumlah <= nasabah.Saldo && jumlah == decimal.Truncate(jumlah.Value))
        {
            var setuju = await Dialog.ConfirmAsync(
                "Konfirmasi penarikan",
                $"Tarik {Format.Rupiah((long)jumlah.Value)} dari saldo {nasabah.NamaLengkap} ({nasabah.NoRekening})?\n" +
                $"Saldo setelah penarikan: {Format.Rupiah(nasabah.Saldo - (long)jumlah.Value)}.",
                "Ya, tarik");
            if (!setuju)
            {
                return;
            }
        }

        await RunAsync(async () =>
        {
            var penarikan = await Services.Transaksi.SimpanPenarikanAsync(nasabah?.NasabahId, jumlah, CatatanPenarikan);
            Notifications.Success(
                $"Penarikan {penarikan.NoTransaksi} tersimpan: {Format.Rupiah(penarikan.Jumlah)} untuk {penarikan.NamaNasabah}.");
            JumlahPenarikan = null;
            CatatanPenarikan = null;
            await MuatNasabahAsync(penarikan.NasabahId);
            await MuatHariIniAsync();
        }, TampilkanErrorKolom);
    }

    [RelayCommand]
    private void TarikSemua()
    {
        if (SelectedNasabah is { Saldo: > 0 } nasabah)
        {
            JumlahPenarikan = nasabah.Saldo;
        }
    }

    [RelayCommand]
    private void ResetForm()
    {
        SelectedJenis = null;
        Berat = null;
        CatatanSetoran = null;
        JumlahPenarikan = null;
        CatatanPenarikan = null;
        NasabahError = JenisError = BeratError = JumlahError = CatatanError = null;
    }

    private async Task CariNasabahUlangAsync()
    {
        _cariCts?.Cancel();
        var cts = _cariCts = new CancellationTokenSource();
        if (!await DebounceAsync(cts.Token))
        {
            return;
        }

        await RunAsync(() => MuatNasabahAsync());
    }

    private async Task MuatNasabahAsync(int? pilihId = null)
    {
        var idTerpilih = pilihId ?? SelectedNasabah?.NasabahId;
        var daftar = await Services.Nasabah.GetAktifAsync(KataKunciNasabah);

        NasabahList.Clear();
        foreach (var nasabah in daftar)
        {
            NasabahList.Add(nasabah);
        }

        SelectedNasabah = NasabahList.FirstOrDefault(n => n.NasabahId == idTerpilih);
        IsNasabahListEmpty = NasabahList.Count == 0;
        NasabahEmptyText = string.IsNullOrWhiteSpace(KataKunciNasabah)
            ? "Belum ada nasabah aktif. Tambahkan nasabah di menu Nasabah."
            : "Tidak ada nasabah aktif yang cocok";
    }

    private async Task MuatJenisAsync()
    {
        var idTerpilih = SelectedJenis?.JenisId;
        var daftar = await Services.JenisSampah.GetJenisAktifAsync();

        JenisList.Clear();
        foreach (var jenis in daftar)
        {
            JenisList.Add(jenis);
        }

        SelectedJenis = JenisList.FirstOrDefault(j => j.JenisId == idTerpilih);
        IsJenisEmpty = JenisList.Count == 0;
    }

    private async Task MuatHariIniAsync()
    {
        var daftar = await Services.Transaksi.GetHariIniAsync();
        HariIni.Clear();
        foreach (var transaksi in daftar)
        {
            HariIni.Add(transaksi);
        }

        HasHariIni = HariIni.Count > 0;
    }

    private void TampilkanErrorKolom(ValidationException ex)
    {
        NasabahError = ex["Nasabah"];
        JenisError = ex["Jenis"];
        BeratError = ex["Berat"];
        JumlahError = ex["Jumlah"];
        CatatanError = ex["Catatan"];
    }
}
