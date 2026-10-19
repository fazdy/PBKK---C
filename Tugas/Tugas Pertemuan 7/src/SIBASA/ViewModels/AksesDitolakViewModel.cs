using CommunityToolkit.Mvvm.Input;

namespace SIBASA.ViewModels;

/// <summary>Status UI "Akses ditolak" ketika pengguna membuka halaman yang tidak diizinkan untuk perannya.</summary>
public partial class AksesDitolakViewModel : PageViewModelBase
{
    private readonly Action _kembali;

    public AksesDitolakViewModel(AppServices services, string namaHalaman, Action kembali) : base(services)
    {
        _kembali = kembali;
        NamaHalaman = namaHalaman;
    }

    public string NamaHalaman { get; }

    public override string Title => "Akses Ditolak";

    public override string Subtitle => "Halaman ini tidak tersedia untuk peran Anda";

    public string Pesan =>
        $"Halaman {NamaHalaman} khusus Admin. Anda masuk sebagai {Services.Session.CurrentUser?.RoleText ?? "tamu"}. " +
        "Hubungi admin bank sampah bila Anda membutuhkan akses ini.";

    [RelayCommand]
    private void Kembali() => _kembali();
}
