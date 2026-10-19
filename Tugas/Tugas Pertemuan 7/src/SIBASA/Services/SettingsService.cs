using System.Text.Json;
using SIBASA.Helpers;

namespace SIBASA.Services;

/// <summary>Preferensi pengguna yang disimpan di settings.json (folder data aplikasi).</summary>
public sealed class AppSettings
{
    public string Theme { get; set; } = "Light";
}

/// <summary>Membaca dan menyimpan preferensi. File rusak atau tidak ada tidak membuat aplikasi gagal dibuka.</summary>
public sealed class SettingsService
{
    private static readonly JsonSerializerOptions JsonOptions = new() { WriteIndented = true };
    private readonly string _filePath;

    /// <param name="filePath">Lokasi file pengaturan; bawaan: folder data aplikasi (bisa diganti saat pengujian).</param>
    public SettingsService(string? filePath = null)
    {
        _filePath = filePath ?? AppPaths.SettingsFile;
    }

    public AppSettings Current { get; private set; } = new();

    public void Load()
    {
        try
        {
            if (File.Exists(_filePath))
            {
                Current = JsonSerializer.Deserialize<AppSettings>(File.ReadAllText(_filePath)) ?? new AppSettings();
            }
        }
        catch (Exception ex)
        {
            Log.Error("Gagal membaca settings.json, memakai pengaturan bawaan", ex);
            Current = new AppSettings();
        }
    }

    public void Save()
    {
        try
        {
            var folder = Path.GetDirectoryName(_filePath);
            if (!string.IsNullOrEmpty(folder))
            {
                Directory.CreateDirectory(folder);
            }

            File.WriteAllText(_filePath, JsonSerializer.Serialize(Current, JsonOptions));
        }
        catch (Exception ex)
        {
            Log.Error("Gagal menyimpan settings.json", ex);
        }
    }
}
