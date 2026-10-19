using SIBASA.Models;
using SIBASA.Repositories;

namespace SIBASA.Services;

/// <summary>Menyediakan ringkasan statistik dashboard. Semua angka dihitung dari database.</summary>
public sealed class DashboardService
{
    private readonly DashboardRepository _repo;
    private readonly SessionService _session;

    public DashboardService(DashboardRepository repo, SessionService session)
    {
        _repo = repo;
        _session = session;
    }

    public Task<DashboardRingkasan> GetRingkasanAsync()
    {
        _session.RequireLogin();
        return _repo.GetRingkasanAsync(DateTime.Now);
    }
}
