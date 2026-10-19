using System.Globalization;
using System.Text;

namespace SIBASA.Helpers;

/// <summary>
/// Pencatat log sederhana ke file harian (logs/sibasa-yyyyMMdd.log).
/// Hanya detail teknis yang dicatat; password dan data rahasia tidak pernah ditulis ke log.
/// </summary>
public static class Log
{
    private static readonly object Gate = new();
    private static readonly Encoding Utf8TanpaBom = new UTF8Encoding(encoderShouldEmitUTF8Identifier: false);

    public static string CurrentLogFile =>
        Path.Combine(AppPaths.LogDirectory, $"sibasa-{DateTime.Now.ToString("yyyyMMdd", CultureInfo.InvariantCulture)}.log");

    public static void Info(string message) => Write("INFO", message, null);

    public static void Warning(string message) => Write("WARN", message, null);

    public static void Error(string context, Exception exception) => Write("ERROR", context, exception);

    private static void Write(string level, string message, Exception? exception)
    {
        try
        {
            var builder = new StringBuilder()
                .Append(DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss.fff", CultureInfo.InvariantCulture))
                .Append(" [").Append(level).Append("] ")
                .AppendLine(message);

            if (exception is not null)
            {
                builder.AppendLine(exception.ToString());
            }

            lock (Gate)
            {
                Directory.CreateDirectory(AppPaths.LogDirectory);
                File.AppendAllText(CurrentLogFile, builder.ToString(), Utf8TanpaBom);
            }
        }
        catch
        {
            // Gagal menulis log tidak boleh membuat aplikasi berhenti.
        }
    }
}
