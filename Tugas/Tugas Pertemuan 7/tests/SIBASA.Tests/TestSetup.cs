using System.Runtime.CompilerServices;

namespace SIBASA.Tests;

internal static class TestSetup
{
    /// <summary>
    /// Dijalankan sekali sebelum tes apa pun: log dan pengaturan saat pengujian diarahkan ke folder
    /// sementara, sehingga folder data aplikasi milik pengguna tidak tersentuh.
    /// </summary>
    [ModuleInitializer]
    internal static void Initialize() =>
        Environment.SetEnvironmentVariable("SIBASA_DATA_DIR", Path.Combine(Path.GetTempPath(), "sibasa-test-data"));
}
