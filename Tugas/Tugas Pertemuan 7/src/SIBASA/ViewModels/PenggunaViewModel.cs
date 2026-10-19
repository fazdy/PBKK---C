using System.Collections.ObjectModel;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using SIBASA.Helpers;
using SIBASA.Models;

namespace SIBASA.ViewModels;

/// <summary>Halaman Kelola Pengguna (khusus Admin): tambah, ubah peran/status, dan reset password.</summary>
public partial class PenggunaViewModel : PageViewModelBase
{
    private int _idSedangDiubah;

    public PenggunaViewModel(AppServices services) : base(services)
    {
    }

    public override string Title => "Kelola Pengguna";

    public override string Subtitle => "Atur akun admin dan petugas yang dapat masuk ke aplikasi";

    public ObservableCollection<User> Items { get; } = new();

    public IReadOnlyList<UserRole> RoleOptions { get; } = [UserRole.Admin, UserRole.Petugas];

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(HasSelection))]
    [NotifyCanExecuteChangedFor(nameof(UbahCommand))]
    private User? _selected;

    [ObservableProperty] private string _resultText = string.Empty;

    public bool HasSelection => Selected is not null;

    // ---------------- Form ----------------
    [ObservableProperty] private bool _isFormOpen;

    [ObservableProperty]
    [NotifyPropertyChangedFor(nameof(PasswordLabel), nameof(PasswordHint), nameof(PasswordWatermark))]
    private bool _isEditMode;

    /// <summary>True bila Admin sedang mengubah akunnya sendiri (peran dan status tidak boleh diturunkan).</summary>
    [ObservableProperty]
    private bool _isEditingSelf;

    [ObservableProperty] private string _formTitle = "Tambah Pengguna";
    [ObservableProperty] private string _formUsername = string.Empty;
    [ObservableProperty] private string _formNamaLengkap = string.Empty;
    [ObservableProperty] private UserRole _formRole = UserRole.Petugas;
    [ObservableProperty] private bool _formIsActive = true;
    [ObservableProperty] private string _formPassword = string.Empty;
    [ObservableProperty] private string _formKonfirmasi = string.Empty;

    [ObservableProperty] private string? _usernameError;
    [ObservableProperty] private string? _namaLengkapError;
    [ObservableProperty] private string? _roleError;
    [ObservableProperty] private string? _isActiveError;
    [ObservableProperty] private string? _passwordError;
    [ObservableProperty] private string? _konfirmasiError;

    public string PasswordLabel => IsEditMode ? "Password baru (opsional)" : "Password";
    public string PasswordWatermark => IsEditMode ? "Kosongkan jika tidak diubah" : "Minimal 8 karakter";

    public string PasswordHint => IsEditMode
        ? "Isi hanya bila ingin me-reset password pengguna ini."
        : "Minimal 8 karakter, mengandung huruf dan angka.";

    partial void OnFormUsernameChanged(string value) => UsernameError = null;
    partial void OnFormNamaLengkapChanged(string value) => NamaLengkapError = null;
    partial void OnFormRoleChanged(UserRole value) => RoleError = null;
    partial void OnFormIsActiveChanged(bool value) => IsActiveError = null;
    partial void OnFormPasswordChanged(string value) => PasswordError = null;
    partial void OnFormKonfirmasiChanged(string value) => KonfirmasiError = null;

    public override Task LoadAsync() => RunAsync(() => MuatAsync(), isLoad: true);

    [RelayCommand]
    private Task RefreshAsync() => LoadAsync();

    [RelayCommand]
    private void Tambah()
    {
        _idSedangDiubah = 0;
        IsEditMode = false;
        IsEditingSelf = false;
        FormTitle = "Tambah Pengguna";
        FormUsername = string.Empty;
        FormNamaLengkap = string.Empty;
        FormRole = UserRole.Petugas;
        FormIsActive = true;
        FormPassword = string.Empty;
        FormKonfirmasi = string.Empty;
        BersihkanError();
        IsFormOpen = true;
    }

    [RelayCommand(CanExecute = nameof(HasSelection))]
    private void Ubah()
    {
        if (Selected is not { } user)
        {
            return;
        }

        _idSedangDiubah = user.UserId;
        IsEditMode = true;
        IsEditingSelf = user.UserId == Services.Session.CurrentUser?.UserId;
        FormTitle = IsEditingSelf ? "Ubah Akun Saya" : "Ubah Pengguna";
        FormUsername = user.Username;
        FormNamaLengkap = user.NamaLengkap;
        FormRole = user.Role;
        FormIsActive = user.IsActive;
        FormPassword = string.Empty;
        FormKonfirmasi = string.Empty;
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
        var input = new User
        {
            UserId = _idSedangDiubah,
            Username = FormUsername,
            NamaLengkap = FormNamaLengkap,
            Role = FormRole,
            IsActive = FormIsActive
        };

        await RunAsync(async () =>
        {
            var tersimpan = await Services.Users.SaveAsync(input, FormPassword, FormKonfirmasi);
            IsFormOpen = false;
            FormPassword = string.Empty;
            FormKonfirmasi = string.Empty;
            Notifications.Success(input.UserId == 0
                ? $"Pengguna {tersimpan.Username} ({tersimpan.RoleText}) berhasil ditambahkan."
                : $"Pengguna {tersimpan.Username} berhasil diperbarui.");
            await MuatAsync(tersimpan.UserId);
        }, ex =>
        {
            UsernameError = ex[nameof(User.Username)];
            NamaLengkapError = ex[nameof(User.NamaLengkap)];
            RoleError = ex[nameof(User.Role)];
            IsActiveError = ex[nameof(User.IsActive)];
            PasswordError = ex["Password"];
            KonfirmasiError = ex["Konfirmasi"];
        });
    }

    private async Task MuatAsync(int? pilihId = null)
    {
        var idTerpilih = pilihId ?? Selected?.UserId;
        var daftar = await Services.Users.GetAllAsync();

        Items.Clear();
        foreach (var user in daftar)
        {
            Items.Add(user);
        }

        Selected = Items.FirstOrDefault(u => u.UserId == idTerpilih);
        ResultText = $"{Items.Count} pengguna · {Items.Count(u => u.IsActive)} aktif · " +
                     $"{Items.Count(u => u.IsAdmin)} admin";
    }

    private void BersihkanError()
    {
        UsernameError = null;
        NamaLengkapError = null;
        RoleError = null;
        IsActiveError = null;
        PasswordError = null;
        KonfirmasiError = null;
    }
}
