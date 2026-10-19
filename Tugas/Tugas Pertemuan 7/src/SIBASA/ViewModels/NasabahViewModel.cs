using System.Collections.ObjectModel;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Helpers;
using SIBASA.Models;

namespace SIBASA.ViewModels;

/// <summary>Halaman Data Nasabah: daftar, pencarian, filter, dan form tambah/ubah (CRUD).</summary>
public partial class NasabahViewModel : PageViewModelBase
{
    public const string SemuaRw = "Semua RW";
    public const string SemuaStatus = "Semua status";

    private CancellationTokenSource? _cariCts;
    private int _idSedangDiubah;

    public NasabahViewModel(AppServices services) : base(services)
    {
    }

    public override string Title => "Data Nasabah";

    public override string Subtitle => "Kelola warga penabung sampah: cari, tambah, ubah, dan nonaktifkan";

    public ObservableCollection<Nasabah> Items { get; } = new();

    public ObservableCollection<string> RwOptions { get; } = new() { SemuaRw };

    public IReadOnlyList<string> StatusOptions { get; } = [SemuaStatus, "Aktif", "Nonaktif"];

    // ---------------- Pencarian & filter ----------------
    [ObservableProperty] private string _kataKunci = string.Empty;
    [ObservableProperty] private string _selectedRw = SemuaRw;
    [ObservableProperty] private string _selectedStatus = SemuaStatus;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(HasSelection), nameof(ToggleStatusText), nameof(ToggleStatusIcon))]
    [NotifyCanExecuteChangedFor(nameof(UbahCommand), nameof(ToggleStatusCommand), nameof(HapusCommand))]
    private Nasabah? _selectedNasabah;

    [ObservableProperty] private string _resultText = string.Empty;
    [ObservableProperty] private bool _isEmpty;
    [ObservableProperty] private string _emptyText = string.Empty;

    public bool HasSelection => SelectedNasabah is not null;

    public string ToggleStatusText => SelectedNasabah is { IsActive: false } ? "Aktifkan" : "Nonaktifkan";

    public string ToggleStatusIcon => SelectedNasabah is { IsActive: false } ? "UserCheck" : "UserOff";

    // ---------------- Form tambah/ubah ----------------
    [ObservableProperty] private bool _isFormOpen;
    [ObservableProperty] private bool _isEditMode;
    [ObservableProperty] private string _formTitle = "Tambah Nasabah";
    [ObservableProperty] private string _formNoRekening = string.Empty;
    [ObservableProperty] private string _formSaldoText = string.Empty;
    [ObservableProperty] private string _formNamaLengkap = string.Empty;
    [ObservableProperty] private string? _formNoHp;
    [ObservableProperty] private string? _formAlamat;
    [ObservableProperty] private string _formRt = string.Empty;
    [ObservableProperty] private string _formRw = string.Empty;
    [ObservableProperty] private bool _formIsActive = true;

    [ObservableProperty] private string? _namaLengkapError;
    [ObservableProperty] private string? _noHpError;
    [ObservableProperty] private string? _alamatError;
    [ObservableProperty] private string? _rtError;
    [ObservableProperty] private string? _rwError;
    [ObservableProperty] private string? _isActiveError;

    // Pesan error kolom hilang begitu kolom tersebut diubah.
    partial void OnFormNamaLengkapChanged(string value) => NamaLengkapError = null;
    partial void OnFormNoHpChanged(string? value) => NoHpError = null;
    partial void OnFormAlamatChanged(string? value) => AlamatError = null;
    partial void OnFormRtChanged(string value) => RtError = null;
    partial void OnFormRwChanged(string value) => RwError = null;
    partial void OnFormIsActiveChanged(bool value) => IsActiveError = null;

    partial void OnKataKunciChanged(string value) => _ = CariUlangAsync();
    partial void OnSelectedRwChanged(string value) => _ = CariUlangAsync(0);
    partial void OnSelectedStatusChanged(string value) => _ = CariUlangAsync(0);

    public override Task LoadAsync() => RunAsync(async () =>
    {
        await MuatPilihanRwAsync();
        await MuatDaftarAsync();
    }, isLoad: true);

    [RelayCommand]
    private Task RefreshAsync() => LoadAsync();

    [RelayCommand]
    private void ResetFilter()
    {
        KataKunci = string.Empty;
        SelectedRw = SemuaRw;
        SelectedStatus = SemuaStatus;
    }

    [RelayCommand]
    private void Tambah()
    {
        _idSedangDiubah = 0;
        IsEditMode = false;
        FormTitle = "Tambah Nasabah";
        FormNoRekening = "Dibuat otomatis";
        FormSaldoText = Format.Rupiah(0);
        FormNamaLengkap = string.Empty;
        FormNoHp = null;
        FormAlamat = null;
        FormRt = string.Empty;
        FormRw = string.Empty;
        FormIsActive = true;
        BersihkanError();
        IsFormOpen = true;
    }

    [RelayCommand(CanExecute = nameof(HasSelection))]
    private void Ubah()
    {
        if (SelectedNasabah is not { } n)
        {
            return;
        }

        _idSedangDiubah = n.NasabahId;
        IsEditMode = true;
        FormTitle = "Ubah Nasabah";
        FormNoRekening = n.NoRekening;
        FormSaldoText = n.SaldoText;
        FormNamaLengkap = n.NamaLengkap;
        FormNoHp = n.NoHp;
        FormAlamat = n.Alamat;
        FormRt = n.RT;
        FormRw = n.RW;
        FormIsActive = n.IsActive;
        BersihkanError();
        IsFormOpen = true;
    }

    [RelayCommand]
    private void Batal()
    {
        IsFormOpen = false;
        BersihkanError();
    }

    [RelayCommand]
    private async Task SimpanAsync()
    {
        var input = new Nasabah
        {
            NasabahId = _idSedangDiubah,
            NamaLengkap = FormNamaLengkap,
            NoHp = FormNoHp,
            Alamat = FormAlamat,
            RT = FormRt,
            RW = FormRw,
            IsActive = FormIsActive
        };

        await RunAsync(async () =>
        {
            var tersimpan = await Services.Nasabah.SaveAsync(input);
            IsFormOpen = false;
            Notifications.Success(input.NasabahId == 0
                ? $"Nasabah {tersimpan.NamaLengkap} terdaftar dengan nomor rekening {tersimpan.NoRekening}."
                : $"Data {tersimpan.NamaLengkap} berhasil diperbarui.");
            await MuatPilihanRwAsync();
            await MuatDaftarAsync(tersimpan.NasabahId);
        }, TampilkanErrorKolom);
    }

    [RelayCommand(CanExecute = nameof(HasSelection))]
    private async Task ToggleStatusAsync()
    {
        if (SelectedNasabah is not { } n)
        {
            return;
        }

        var aktifkan = !n.IsActive;
        if (!aktifkan && n.Saldo > 0)
        {
            // Langsung beri tahu tanpa dialog; Service tetap memeriksa aturan yang sama.
            Notifications.Warning($"Saldo {n.NamaLengkap} masih {n.SaldoText}. Lakukan penarikan terlebih dahulu sebelum menonaktifkan.");
            return;
        }

        if (!aktifkan)
        {
            var setuju = await Dialog.ConfirmAsync(
                "Nonaktifkan nasabah?",
                $"{n.NamaLengkap} ({n.NoRekening}) tidak akan muncul lagi di pilihan transaksi. Data dan riwayatnya tetap tersimpan.",
                "Nonaktifkan");
            if (!setuju)
            {
                return;
            }
        }

        await RunAsync(async () =>
        {
            await Services.Nasabah.SetActiveAsync(n.NasabahId, aktifkan);
            Notifications.Success($"{n.NamaLengkap} sekarang {(aktifkan ? "aktif" : "nonaktif")}.");
            await MuatDaftarAsync(n.NasabahId);
        });
    }

    [RelayCommand(CanExecute = nameof(HasSelection))]
    private async Task HapusAsync()
    {
        if (SelectedNasabah is not { } n)
        {
            return;
        }

        var setuju = await Dialog.ConfirmAsync(
            "Hapus nasabah?",
            $"Data {n.NamaLengkap} ({n.NoRekening}) akan dihapus permanen dan tidak dapat dikembalikan.",
            "Hapus",
            isDanger: true);
        if (!setuju)
        {
            return;
        }

        await RunAsync(async () =>
        {
            await Services.Nasabah.DeleteAsync(n.NasabahId);
            Notifications.Success($"Nasabah {n.NamaLengkap} berhasil dihapus.");
            if (IsFormOpen && _idSedangDiubah == n.NasabahId)
            {
                IsFormOpen = false;
            }

            await MuatPilihanRwAsync();
            await MuatDaftarAsync();
        });
    }

    private async Task CariUlangAsync(int jeda = 300)
    {
        _cariCts?.Cancel();
        var cts = _cariCts = new CancellationTokenSource();
        if (jeda > 0 && !await DebounceAsync(cts.Token, jeda))
        {
            return;
        }

        await RunAsync(() => MuatDaftarAsync());
    }

    private async Task MuatDaftarAsync(int? pilihId = null)
    {
        var idTerpilih = pilihId ?? SelectedNasabah?.NasabahId;
        var filter = new NasabahFilter(
            KataKunci,
            SelectedRw == SemuaRw ? null : SelectedRw,
            SelectedStatus switch
            {
                "Aktif" => true,
                "Nonaktif" => false,
                _ => null
            });

        var daftar = await Services.Nasabah.GetAllAsync(filter);

        Items.Clear();
        foreach (var nasabah in daftar)
        {
            Items.Add(nasabah);
        }

        SelectedNasabah = Items.FirstOrDefault(n => n.NasabahId == idTerpilih);

        var adaFilter = !string.IsNullOrWhiteSpace(KataKunci) || SelectedRw != SemuaRw || SelectedStatus != SemuaStatus;
        IsEmpty = Items.Count == 0;
        EmptyText = adaFilter
            ? "Tidak ada nasabah yang cocok dengan pencarian"
            : "Belum ada data nasabah. Klik Tambah untuk mendaftarkan nasabah pertama.";
        ResultText = $"Menampilkan {Items.Count:N0} nasabah · total saldo {Format.Rupiah(Items.Sum(n => n.Saldo))}";
    }

    private async Task MuatPilihanRwAsync()
    {
        var daftarRw = await Services.Nasabah.GetRwListAsync();
        var terpilih = SelectedRw;

        // Perbarui pilihan tanpa memicu pencarian ulang berkali-kali.
        for (var i = RwOptions.Count - 1; i >= 1; i--)
        {
            if (!daftarRw.Contains(RwOptions[i]))
            {
                RwOptions.RemoveAt(i);
            }
        }

        foreach (var rw in daftarRw.Where(rw => !RwOptions.Contains(rw)))
        {
            // Sisipkan sesuai urutan agar daftar RW tetap terurut (indeks 0 = "Semua RW").
            var posisi = 1 + RwOptions.Skip(1).Count(ada => string.CompareOrdinal(ada, rw) < 0);
            RwOptions.Insert(posisi, rw);
        }

        if (!RwOptions.Contains(terpilih))
        {
            SelectedRw = SemuaRw;
        }
    }

    private void TampilkanErrorKolom(ValidationException ex)
    {
        NamaLengkapError = ex[nameof(Nasabah.NamaLengkap)];
        NoHpError = ex[nameof(Nasabah.NoHp)];
        AlamatError = ex[nameof(Nasabah.Alamat)];
        RtError = ex[nameof(Nasabah.RT)];
        RwError = ex[nameof(Nasabah.RW)];
        IsActiveError = ex[nameof(Nasabah.IsActive)];
    }

    private void BersihkanError()
    {
        NamaLengkapError = null;
        NoHpError = null;
        AlamatError = null;
        RtError = null;
        RwError = null;
        IsActiveError = null;
    }
}
