namespace StudentRegistration.Models;

/// <summary>
/// Model program studi (tabel Programs).
/// Catatan: ini BUKAN Program.cs entry point aplikasi. Entry point Avalonia
/// berada di file Program.cs pada folder utama (namespace StudentRegistration).
/// </summary>
public class Program
{
    public int ProgramId { get; set; }

    public string ProgramCode { get; set; } = string.Empty;

    public string ProgramName { get; set; } = string.Empty;

    public bool IsActive { get; set; }
}
