// Pengganti (fake) untuk bagian yang bergantung pada Windows/SQL Server,
// agar kode ViewModel ASLI bisa diuji di Windows, macOS, maupun Linux.

#nullable disable
namespace System.Windows.Data
{
    // Stub minimal dari WPF (WPF tidak beranotasi nullable → oblivious)
    public interface IValueConverter
    {
        object Convert(object value, Type targetType, object parameter, System.Globalization.CultureInfo culture);
        object ConvertBack(object value, Type targetType, object parameter, System.Globalization.CultureInfo culture);
    }

    public static class Binding
    {
        public static readonly object DoNothing = new object();
    }
}
#nullable restore

namespace StudentManager.Data
{
    using StudentManager.Models;

    /// <summary>Repository in-memory yang meniru perilaku SQL Server (IDENTITY, ORDER BY Id DESC, LIKE case-insensitive).</summary>
    public class StudentRepository
    {
        private readonly List<Student> _table = new();
        private int _identity;

        public bool Fail { get; set; }
        public bool FailOnInit { get; set; }
        public int InsertCalls { get; private set; }
        public int UpdateCalls { get; private set; }
        public int DeleteCalls { get; private set; }

        private void ThrowIfFail()
        {
            if (Fail) throw new InvalidOperationException("Simulasi: koneksi database terputus");
        }

        public void EnsureDatabaseCreated()
        {
            if (FailOnInit || Fail) throw new InvalidOperationException("Simulasi: LocalDB tidak tersedia");
            if (_identity > 0) return;
            Seed("23001", "Budi Santoso", "Informatika", "Laki-laki", "budi@gmail.com");
            Seed("23002", "Siti Aminah", "Sistem Informasi", "Perempuan", "siti@gmail.com");
            Seed("23003", "Andi Wijaya", "Informatika", "Laki-laki", "andi@gmail.com");
            Seed("23004", "Rina Sari", "Sistem Informasi", "Perempuan", "rina@gmail.com");
        }

        private void Seed(string nim, string nama, string jurusan, string gender, string email) =>
            _table.Add(new Student { Id = ++_identity, NIM = nim, Nama = nama, Jurusan = jurusan, Gender = gender, Email = email });

        public List<Student> GetAll()
        {
            ThrowIfFail();
            return _table.OrderByDescending(s => s.Id).Select(s => s.Clone()).ToList();
        }

        public List<Student> Search(string keyword)
        {
            ThrowIfFail();
            bool Like(string value) => value.Contains(keyword, StringComparison.OrdinalIgnoreCase);
            return _table.Where(s => Like(s.NIM) || Like(s.Nama) || Like(s.Jurusan))
                         .OrderByDescending(s => s.Id).Select(s => s.Clone()).ToList();
        }

        public void Insert(Student student)
        {
            ThrowIfFail();
            InsertCalls++;
            var row = student.Clone();
            row.Id = ++_identity;
            _table.Add(row);
        }

        public void Update(Student student)
        {
            ThrowIfFail();
            UpdateCalls++;
            int i = _table.FindIndex(x => x.Id == student.Id);
            if (i >= 0) _table[i] = student.Clone();
        }

        public void Delete(int id)
        {
            ThrowIfFail();
            DeleteCalls++;
            _table.RemoveAll(x => x.Id == id);
        }

        public bool IsNimExists(string nim, int excludeId = 0)
        {
            ThrowIfFail();
            return _table.Any(x => string.Equals(x.NIM, nim, StringComparison.OrdinalIgnoreCase) && x.Id != excludeId);
        }
    }
}

namespace StudentManager.Services
{
    /// <summary>Dialog palsu: mencatat setiap dialog dan menjawab konfirmasi sesuai ConfirmAnswer.</summary>
    public class DialogService : IDialogService
    {
        public List<(string Kind, string Title, string Message)> Log { get; } = new();
        public bool ConfirmAnswer { get; set; } = true;

        public void ShowWarning(string message, string title) => Log.Add(("warning", title, message));
        public void ShowError(string message, string title) => Log.Add(("error", title, message));

        public bool Confirm(string message, string title)
        {
            Log.Add(("confirm", title, message));
            return ConfirmAnswer;
        }
    }
}
