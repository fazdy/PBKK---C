using System.Collections.ObjectModel;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Helpers;
using SIBASA.Models;

namespace SIBASA.ViewModels;

/// <summary>
/// Halaman Jenis Sampah: tab Jenis Sampah (harga per kg) dan tab Kategori.
/// Petugas hanya dapat melihat; tombol ubah hanya tampil untuk Admin dan Service juga menolak Petugas.
/// </summary>
public partial class JenisSampahViewModel : PageViewModelBase
{
    private static readonly KategoriSampah SemuaKategori = new() { KategoriId = 0, NamaKategori = "Semua kategori" };

    private CancellationTokenSource? _cariCts;
    private int _jenisSedangDiubah;
    private int _kategoriSedangDiubah;
    private bool _sedangMenyusunFilter;

    public JenisSampahViewModel(AppServices services) : base(services)
    {
        _sedangMenyusunFilter = true;
        KategoriFilterOptions.Add(SemuaKategori);
        SelectedKategoriFilter = SemuaKategori;
        _sedangMenyusunFilter = false;
    }

    public override string Title => "Jenis Sampah";

    public override string Subtitle => IsAdmin
        ? "Atur kategori, jenis sampah, dan harga per kilogram"
        : "Daftar jenis sampah dan harga per kilogram (hanya baca)";

    public bool IsReadOnly => !IsAdmin;

    [ObservableProperty] private int _selectedTabIndex;

    // ================= Tab Jenis Sampah =================
    public ObservableCollection<JenisSampah> JenisItems { get; } = new();

    public ObservableCollection<KategoriSampah> KategoriFilterOptions { get; } = new();

    public ObservableCollection<KategoriSampah> KategoriOptions { get; } = new();

    [ObservableProperty] private string _kataKunciJenis = string.Empty;
    [ObservableProperty] private KategoriSampah? _selectedKategoriFilter;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(HasJenisSelection))]
    [NotifyCanExecuteChangedFor(nameof(UbahJenisCommand), nameof(HapusJenisCommand))]
    private JenisSampah? _selectedJenis;

    [ObservableProperty] private bool _isJenisEmpty;
    [ObservableProperty] private string _jenisEmptyText = string.Empty;
    [ObservableProperty] private string _jenisResultText = string.Empty;

    public bool HasJenisSelection => SelectedJenis is not null;

    [ObservableProperty] private bool _isJenisFormOpen;
    [ObservableProperty] private string _jenisFormTitle = "Tambah Jenis Sampah";
    [ObservableProperty] private string _formNamaJenis = string.Empty;
    [ObservableProperty] private KategoriSampah? _formKategori;
    [ObservableProperty] private decimal? _formHarga;
    [ObservableProperty] private bool _formJenisAktif = true;
    [ObservableProperty] private string? _namaJenisError;
    [ObservableProperty] private string? _kategoriError;
    [ObservableProperty] private string? _hargaError;

    partial void OnFormNamaJenisChanged(string value) => NamaJenisError = null;
    partial void OnFormKategoriChanged(KategoriSampah? value) => KategoriError = null;
    partial void OnFormHargaChanged(decimal? value) => HargaError = null;

    partial void OnKataKunciJenisChanged(string value) => _ = CariJenisUlangAsync(300);
    partial void OnSelectedKategoriFilterChanged(KategoriSampah? value)
    {
        if (!_sedangMenyusunFilter)
        {
            _ = CariJenisUlangAsync(0);
        }
    }

    // ================= Tab Kategori =================
    public ObservableCollection<KategoriSampah> KategoriItems { get; } = new();

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(HasKategoriSelection))]
    [NotifyCanExecuteChangedFor(nameof(UbahKategoriCommand), nameof(HapusKategoriCommand))]
    private KategoriSampah? _selectedKategori;

    [ObservableProperty] private bool _isKategoriEmpty;

    public bool HasKategoriSelection => SelectedKategori is not null;

    [ObservableProperty] private bool _isKategoriFormOpen;
    [ObservableProperty] private string _kategoriFormTitle = "Tambah Kategori";
    [ObservableProperty] private string _formNamaKategori = string.Empty;
    [ObservableProperty] private string? _formKeterangan;
    [ObservableProperty] private string? _namaKategoriError;
    [ObservableProperty] private string? _keteranganError;

    partial void OnFormNamaKategoriChanged(string value) => NamaKategoriError = null;
    partial void OnFormKeteranganChanged(string? value) => KeteranganError = null;

    public override Task LoadAsync() => RunAsync(async () =>
    {
        await MuatKategoriAsync();
        await MuatJenisAsync();
    }, isLoad: true);

    [RelayCommand]
    private Task RefreshAsync() => LoadAsync();

    // ---------------- Aksi jenis sampah ----------------
    [RelayCommand]
    private void TambahJenis()
    {
        _jenisSedangDiubah = 0;
        JenisFormTitle = "Tambah Jenis Sampah";
        FormNamaJenis = string.Empty;
        FormKategori = SelectedKategoriFilter is { KategoriId: > 0 } filter
            ? KategoriOptions.FirstOrDefault(k => k.KategoriId == filter.KategoriId)
            : null;
        FormHarga = null;
        FormJenisAktif = true;
        BersihkanErrorJenis();
        IsJenisFormOpen = true;
    }

    [RelayCommand(CanExecute = nameof(HasJenisSelection))]
    private void UbahJenis()
    {
        if (SelectedJenis is not { } jenis)
        {
            return;
        }

        _jenisSedangDiubah = jenis.JenisId;
        JenisFormTitle = "Ubah Jenis Sampah";
        FormNamaJenis = jenis.NamaJenis;
        FormKategori = KategoriOptions.FirstOrDefault(k => k.KategoriId == jenis.KategoriId);
        FormHarga = jenis.HargaPerKg;
        FormJenisAktif = jenis.IsActive;
        BersihkanErrorJenis();
        IsJenisFormOpen = true;
    }

    [RelayCommand]
    private void BatalJenis()
    {
        IsJenisFormOpen = false;
        BersihkanErrorJenis();
    }

    [RelayCommand]
    private async Task SimpanJenisAsync()
    {
        var input = new JenisSampah
        {
            JenisId = _jenisSedangDiubah,
            NamaJenis = FormNamaJenis,
            KategoriId = FormKategori?.KategoriId ?? 0,
            HargaPerKg = FormHarga is { } harga && harga == decimal.Truncate(harga) && harga is > 0 and <= long.MaxValue
                ? (long)harga
                : 0,
            IsActive = FormJenisAktif
        };

        await RunAsync(async () =>
        {
            var tersimpan = await Services.JenisSampah.SaveJenisAsync(input);
            IsJenisFormOpen = false;
            Notifications.Success(input.JenisId == 0
                ? $"Jenis {tersimpan.NamaJenis} ditambahkan dengan harga {Format.Rupiah(tersimpan.HargaPerKg)}/kg."
                : $"Jenis {tersimpan.NamaJenis} berhasil diperbarui.");
            await MuatKategoriAsync();
            await MuatJenisAsync(tersimpan.JenisId);
        }, ex =>
        {
            NamaJenisError = ex[nameof(JenisSampah.NamaJenis)];
            KategoriError = ex[nameof(JenisSampah.KategoriId)];
            HargaError = ex[nameof(JenisSampah.HargaPerKg)];
        });
    }

    [RelayCommand(CanExecute = nameof(HasJenisSelection))]
    private async Task HapusJenisAsync()
    {
        if (SelectedJenis is not { } jenis)
        {
            return;
        }

        var setuju = await Dialog.ConfirmAsync(
            "Hapus jenis sampah?",
            $"{jenis.NamaJenis} akan dihapus permanen. Jenis yang sudah dipakai setoran tidak dapat dihapus.",
            "Hapus",
            isDanger: true);
        if (!setuju)
        {
            return;
        }

        await RunAsync(async () =>
        {
            await Services.JenisSampah.DeleteJenisAsync(jenis.JenisId);
            Notifications.Success($"Jenis {jenis.NamaJenis} berhasil dihapus.");
            await MuatKategoriAsync();
            await MuatJenisAsync();
        });
    }

    // ---------------- Aksi kategori ----------------
    [RelayCommand]
    private void TambahKategori()
    {
        _kategoriSedangDiubah = 0;
        KategoriFormTitle = "Tambah Kategori";
        FormNamaKategori = string.Empty;
        FormKeterangan = null;
        BersihkanErrorKategori();
        IsKategoriFormOpen = true;
    }

    [RelayCommand(CanExecute = nameof(HasKategoriSelection))]
    private void UbahKategori()
    {
        if (SelectedKategori is not { } kategori)
        {
            return;
        }

        _kategoriSedangDiubah = kategori.KategoriId;
        KategoriFormTitle = "Ubah Kategori";
        FormNamaKategori = kategori.NamaKategori;
        FormKeterangan = kategori.Keterangan;
        BersihkanErrorKategori();
        IsKategoriFormOpen = true;
    }

    [RelayCommand]
    private void BatalKategori()
    {
        IsKategoriFormOpen = false;
        BersihkanErrorKategori();
    }

    [RelayCommand]
    private async Task SimpanKategoriAsync()
    {
        var input = new KategoriSampah
        {
            KategoriId = _kategoriSedangDiubah,
            NamaKategori = FormNamaKategori,
            Keterangan = FormKeterangan
        };

        await RunAsync(async () =>
        {
            var tersimpan = await Services.JenisSampah.SaveKategoriAsync(input);
            IsKategoriFormOpen = false;
            Notifications.Success(input.KategoriId == 0
                ? $"Kategori {tersimpan.NamaKategori} ditambahkan."
                : $"Kategori {tersimpan.NamaKategori} berhasil diperbarui.");
            await MuatKategoriAsync(tersimpan.KategoriId);
            await MuatJenisAsync();
        }, ex =>
        {
            NamaKategoriError = ex[nameof(KategoriSampah.NamaKategori)];
            KeteranganError = ex[nameof(KategoriSampah.Keterangan)];
        });
    }

    [RelayCommand(CanExecute = nameof(HasKategoriSelection))]
    private async Task HapusKategoriAsync()
    {
        if (SelectedKategori is not { } kategori)
        {
            return;
        }

        var setuju = await Dialog.ConfirmAsync(
            "Hapus kategori?",
            $"Kategori {kategori.NamaKategori} akan dihapus permanen. Kategori yang masih berisi jenis sampah tidak dapat dihapus.",
            "Hapus",
            isDanger: true);
        if (!setuju)
        {
            return;
        }

        await RunAsync(async () =>
        {
            await Services.JenisSampah.DeleteKategoriAsync(kategori);
            Notifications.Success($"Kategori {kategori.NamaKategori} berhasil dihapus.");
            await MuatKategoriAsync();
            await MuatJenisAsync();
        });
    }

    // ---------------- Pemuatan data ----------------
    private async Task CariJenisUlangAsync(int jeda)
    {
        _cariCts?.Cancel();
        var cts = _cariCts = new CancellationTokenSource();
        if (jeda > 0 && !await DebounceAsync(cts.Token, jeda))
        {
            return;
        }

        await RunAsync(() => MuatJenisAsync());
    }

    private async Task MuatJenisAsync(int? pilihId = null)
    {
        var idTerpilih = pilihId ?? SelectedJenis?.JenisId;
        var kategoriId = SelectedKategoriFilter is { KategoriId: > 0 } filter ? filter.KategoriId : (int?)null;
        var daftar = await Services.JenisSampah.GetJenisAsync(new JenisSampahFilter(KataKunciJenis, kategoriId));

        JenisItems.Clear();
        foreach (var jenis in daftar)
        {
            JenisItems.Add(jenis);
        }

        SelectedJenis = JenisItems.FirstOrDefault(j => j.JenisId == idTerpilih);
        IsJenisEmpty = JenisItems.Count == 0;
        JenisEmptyText = string.IsNullOrWhiteSpace(KataKunciJenis) && kategoriId is null
            ? "Belum ada jenis sampah. Admin dapat menambahkannya lewat tombol Tambah."
            : "Tidak ada jenis sampah yang cocok dengan pencarian";
        JenisResultText = $"{JenisItems.Count:N0} jenis · {JenisItems.Count(j => j.IsActive):N0} aktif";
    }

    private async Task MuatKategoriAsync(int? pilihId = null)
    {
        var idTerpilih = pilihId ?? SelectedKategori?.KategoriId;
        var filterTerpilih = SelectedKategoriFilter?.KategoriId ?? 0;
        var daftar = await Services.JenisSampah.GetKategoriAsync();

        KategoriItems.Clear();
        KategoriOptions.Clear();
        foreach (var kategori in daftar)
        {
            KategoriItems.Add(kategori);
            KategoriOptions.Add(kategori);
        }

        SelectedKategori = KategoriItems.FirstOrDefault(k => k.KategoriId == idTerpilih);
        IsKategoriEmpty = KategoriItems.Count == 0;

        // Pilihan filter disusun ulang tanpa memicu pencarian berulang.
        _sedangMenyusunFilter = true;
        try
        {
            KategoriFilterOptions.Clear();
            KategoriFilterOptions.Add(SemuaKategori);
            foreach (var kategori in daftar)
            {
                KategoriFilterOptions.Add(kategori);
            }

            SelectedKategoriFilter = KategoriFilterOptions.FirstOrDefault(k => k.KategoriId == filterTerpilih) ?? SemuaKategori;
        }
        finally
        {
            _sedangMenyusunFilter = false;
        }
    }

    private void BersihkanErrorJenis()
    {
        NamaJenisError = null;
        KategoriError = null;
        HargaError = null;
    }

    private void BersihkanErrorKategori()
    {
        NamaKategoriError = null;
        KeteranganError = null;
    }
}
