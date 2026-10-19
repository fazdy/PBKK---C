using SIBASA.Helpers;
using SIBASA.Models;
using SIBASA.Repositories;

namespace SIBASA.Services;

/// <summary>Dasar service: akses sesi dan pencatatan aktivitas.</summary>
public abstract class ServiceBase
{
    protected ServiceBase(SessionService session, AktivitasRepository aktivitas)
    {
        Session = session;
        AktivitasRepo = aktivitas;
    }

    protected SessionService Session { get; }

    protected AktivitasRepository AktivitasRepo { get; }

    /// <summary>
    /// Mencatat aktivitas untuk panel "Aktivitas terbaru". Kegagalan mencatat tidak membatalkan
    /// aksi utama yang sudah berhasil; error cukup ditulis ke log.
    /// </summary>
    protected async Task CatatAktivitasAsync(string jenisAksi, string entitas, int? entitasId, string keterangan)
    {
        try
        {
            await AktivitasRepo.CatatAsync(Aktivitas.Buat(Session.CurrentUser?.UserId, jenisAksi, entitas, entitasId, keterangan));
        }
        catch (Exception ex)
        {
            Log.Error($"Gagal mencatat aktivitas '{jenisAksi}'", ex);
        }
    }

    /// <summary>Mengosongkan teks yang hanya berisi spasi menjadi null.</summary>
    protected static string? Bersihkan(string? teks) => string.IsNullOrWhiteSpace(teks) ? null : teks.Trim();
}
