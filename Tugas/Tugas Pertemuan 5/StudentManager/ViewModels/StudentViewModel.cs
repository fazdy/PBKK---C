using System.Collections.ObjectModel;
using System.ComponentModel;
using System.Globalization;
using System.Runtime.CompilerServices;
using System.Text.RegularExpressions;
using System.Windows.Input;
using StudentManager.Data;
using StudentManager.Models;
using StudentManager.Services;

namespace StudentManager.ViewModels;

/// <summary>
/// ViewModel: menyimpan state UI, command, pencarian, statistik,
/// dan logika aplikasi. View (XAML) hanya melakukan binding ke kelas ini.
/// </summary>
public class StudentViewModel : INotifyPropertyChanged
{
    private const string AllDataCaption = "Seluruh data mahasiswa";

    private static readonly Regex EmailPattern =
        new(@"^[^@\s]+@[^@\s]+\.[^@\s]+$");

    private readonly StudentRepository _repository;
    private readonly IDialogService _dialogService;

    // ===================== DATA UNTUK DATAGRID =====================
    public ObservableCollection<Student> Students { get; } = new();

    // ===================== PILIHAN COMBOBOX =====================
    public IReadOnlyList<string> JurusanOptions { get; } =
        new[] { "Informatika", "Sistem Informasi" };

    public IReadOnlyList<string> GenderOptions { get; } =
        new[] { "Laki-laki", "Perempuan" };

    // ===================== HEADER =====================
    public string TodayText { get; } =
        DateTime.Now.ToString("dddd, dd MMMM yyyy", new CultureInfo("id-ID"));

    // ===================== BARIS TERPILIH DI DATAGRID =====================
    private Student? _selectedStudent;
    public Student? SelectedStudent
    {
        get => _selectedStudent;
        set
        {
            _selectedStudent = value;
            OnPropertyChanged();

            // Selection: DataGrid.SelectedItem → SelectedStudent → Form
            if (value != null)
            {
                CurrentStudent = value.Clone();
                StatusMessage = $"Data {value.Nama} dipilih. Ubah data lalu klik Update, atau klik Hapus.";
            }
        }
    }

    // ===================== DATA PADA FORM INPUT =====================
    private Student _currentStudent = new();
    public Student CurrentStudent
    {
        get => _currentStudent;
        set
        {
            _currentStudent = value;
            OnPropertyChanged();
            OnPropertyChanged(nameof(IsEditMode));
            OnPropertyChanged(nameof(FormModeText));
            OnPropertyChanged(nameof(SaveButtonText));
        }
    }

    public bool IsEditMode => CurrentStudent.Id != 0;

    public string FormModeText => IsEditMode ? "Mode Edit" : "Mode Tambah";

    public string SaveButtonText => IsEditMode ? "Update" : "Simpan";

    // ===================== PENCARIAN =====================
    private string _searchText = "";
    public string SearchText
    {
        get => _searchText;
        set
        {
            _searchText = value;
            OnPropertyChanged();
        }
    }

    // ===================== PESAN STATUS =====================
    private string _statusMessage = "";
    public string StatusMessage
    {
        get => _statusMessage;
        set
        {
            _statusMessage = value;
            OnPropertyChanged();
        }
    }

    private string _infoCaption = AllDataCaption;
    public string InfoCaption
    {
        get => _infoCaption;
        set
        {
            _infoCaption = value;
            OnPropertyChanged();
        }
    }

    // ===================== STATISTIK (PANEL INFORMASI) =====================
    public int TotalStudents => Students.Count;
    public int TotalInformatika =>
        Students.Count(x => x.Jurusan == "Informatika");
    public int TotalSistemInformasi =>
        Students.Count(x => x.Jurusan == "Sistem Informasi");
    public int TotalLakiLaki =>
        Students.Count(x => x.Gender == "Laki-laki");
    public int TotalPerempuan =>
        Students.Count(x => x.Gender == "Perempuan");

    // ===================== COMMAND =====================
    public ICommand SaveCommand { get; }
    public ICommand DeleteCommand { get; }
    public ICommand ResetCommand { get; }
    public ICommand SearchCommand { get; }
    public ICommand ClearSearchCommand { get; }
    public ICommand EditCommand { get; }

    public StudentViewModel()
        : this(new StudentRepository(), new DialogService())
    {
    }

    public StudentViewModel(StudentRepository repository, IDialogService dialogService)
    {
        _repository = repository;
        _dialogService = dialogService;

        SaveCommand = new RelayCommand(Save);
        DeleteCommand = new RelayCommand(Delete);
        ResetCommand = new RelayCommand(Reset);
        SearchCommand = new RelayCommand(Search);
        ClearSearchCommand = new RelayCommand(ClearSearch);
        EditCommand = new RelayCommand(Edit);

        InitializeData();
    }

    // ===================== INISIALISASI =====================
    private void InitializeData()
    {
        try
        {
            _repository.EnsureDatabaseCreated();
            LoadData();
            StatusMessage = $"Terhubung ke database StudentDB. {Students.Count} data mahasiswa dimuat.";
        }
        catch (Exception ex)
        {
            StatusMessage = "Gagal terhubung ke database. Periksa SQL Server LocalDB.";
            _dialogService.ShowError(
                "Aplikasi tidak dapat terhubung ke SQL Server LocalDB.\n\n" +
                "Pastikan komponen \"SQL Server Express LocalDB\" sudah terpasang " +
                "(Visual Studio Installer → Modify → Individual components), " +
                "lalu jalankan ulang aplikasi.\n\n" +
                "Detail: " + ex.Message,
                "Koneksi Database Gagal");
        }
    }

    // ===================== READ =====================
    private void LoadData()
    {
        FillStudents(_repository.GetAll());
        InfoCaption = AllDataCaption;
    }

    // ===================== CREATE / UPDATE =====================
    private void Save()
    {
        Student student = ReadForm();

        List<string> errors = Validate(student);
        if (errors.Count > 0)
        {
            ShowValidationErrors(errors);
            return;
        }

        try
        {
            if (_repository.IsNimExists(student.NIM, student.Id))
            {
                ShowValidationErrors(new List<string>
                {
                    $"NIM {student.NIM} sudah terdaftar. Gunakan NIM lain."
                });
                return;
            }

            bool isNew = student.Id == 0;

            if (isNew)
                _repository.Insert(student);
            else
                _repository.Update(student);

            SearchText = "";
            LoadData();
            Reset();

            StatusMessage = isNew
                ? $"Data {student.Nama} berhasil disimpan."
                : $"Data {student.Nama} berhasil diperbarui.";
        }
        catch (Exception ex)
        {
            ShowDatabaseError("menyimpan", ex);
        }
    }

    // ===================== DELETE =====================
    private void Delete(object? parameter)
    {
        // Tombol hapus pada kolom Aksi mengirim Student sebagai CommandParameter,
        // sedangkan tombol Hapus di form memakai data yang sedang diedit.
        Student? target = parameter as Student ?? (IsEditMode ? CurrentStudent : null);

        if (target == null)
        {
            _dialogService.ShowWarning(
                "Pilih data mahasiswa yang akan dihapus terlebih dahulu.",
                "Hapus Data");
            return;
        }

        bool confirmed = _dialogService.Confirm(
            "Yakin ingin menghapus data mahasiswa berikut?\n\n" +
            $"NIM   : {target.NIM}\n" +
            $"Nama : {target.Nama}",
            "Konfirmasi Hapus");

        if (!confirmed)
        {
            StatusMessage = "Penghapusan data dibatalkan.";
            return;
        }

        try
        {
            _repository.Delete(target.Id);

            SearchText = "";
            LoadData();
            Reset();

            StatusMessage = $"Data {target.Nama} berhasil dihapus.";
        }
        catch (Exception ex)
        {
            ShowDatabaseError("menghapus", ex);
        }
    }

    // ===================== EDIT (TOMBOL AKSI) =====================
    private void Edit(object? parameter)
    {
        if (parameter is not Student student) return;

        SelectedStudent = student;
        CurrentStudent = student.Clone();
        StatusMessage = $"Mode edit: {student.Nama}. Ubah data lalu klik Update.";
    }

    // ===================== SEARCH =====================
    private void Search()
    {
        string keyword = SearchText.Trim();

        try
        {
            var result = string.IsNullOrEmpty(keyword)
                ? _repository.GetAll()
                : _repository.Search(keyword);

            FillStudents(result);

            if (string.IsNullOrEmpty(keyword))
            {
                InfoCaption = AllDataCaption;
                StatusMessage = $"Menampilkan seluruh data ({Students.Count} mahasiswa).";
            }
            else
            {
                InfoCaption = $"Hasil pencarian \"{keyword}\"";
                StatusMessage = $"Ditemukan {Students.Count} data untuk kata kunci \"{keyword}\".";
            }
        }
        catch (Exception ex)
        {
            ShowDatabaseError("mencari", ex);
        }
    }

    private void ClearSearch()
    {
        SearchText = "";

        try
        {
            LoadData();
            StatusMessage = $"Pencarian direset. Menampilkan seluruh data ({Students.Count} mahasiswa).";
        }
        catch (Exception ex)
        {
            ShowDatabaseError("memuat", ex);
        }
    }

    // ===================== RESET FORM =====================
    private void Reset()
    {
        SelectedStudent = null;
        CurrentStudent = new Student();
        StatusMessage = "Form dikosongkan. Silakan input data mahasiswa baru.";
    }

    // ===================== HELPER =====================
    private void FillStudents(IEnumerable<Student> students)
    {
        Students.Clear();

        foreach (var student in students)
            Students.Add(student);

        RefreshStatistics();
    }

    private void RefreshStatistics()
    {
        OnPropertyChanged(nameof(TotalStudents));
        OnPropertyChanged(nameof(TotalInformatika));
        OnPropertyChanged(nameof(TotalSistemInformasi));
        OnPropertyChanged(nameof(TotalLakiLaki));
        OnPropertyChanged(nameof(TotalPerempuan));
    }

    private Student ReadForm() => new()
    {
        Id = CurrentStudent.Id,
        NIM = CurrentStudent.NIM.Trim(),
        Nama = CurrentStudent.Nama.Trim(),
        Jurusan = CurrentStudent.Jurusan,
        Gender = CurrentStudent.Gender,
        Email = CurrentStudent.Email.Trim()
    };

    private static List<string> Validate(Student student)
    {
        var errors = new List<string>();

        if (string.IsNullOrWhiteSpace(student.NIM))
            errors.Add("NIM wajib diisi.");
        else if (!student.NIM.All(char.IsAsciiDigit))
            errors.Add("NIM hanya boleh berisi angka.");

        if (string.IsNullOrWhiteSpace(student.Nama))
            errors.Add("Nama wajib diisi.");

        if (string.IsNullOrWhiteSpace(student.Jurusan))
            errors.Add("Jurusan wajib dipilih.");

        if (string.IsNullOrWhiteSpace(student.Gender))
            errors.Add("Gender wajib dipilih.");

        if (student.Email.Length > 0 && !EmailPattern.IsMatch(student.Email))
            errors.Add("Format email tidak valid (contoh: nama@gmail.com).");

        return errors;
    }

    private void ShowValidationErrors(List<string> errors)
    {
        StatusMessage = "Data belum valid: " + errors[0];
        _dialogService.ShowWarning(
            "Periksa kembali input berikut:\n\n• " + string.Join("\n• ", errors),
            "Validasi Data");
    }

    private void ShowDatabaseError(string action, Exception ex)
    {
        StatusMessage = $"Gagal {action} data: {ex.Message}";
        _dialogService.ShowError(
            $"Terjadi kesalahan saat {action} data.\n\nDetail: {ex.Message}",
            "Kesalahan Database");
    }

    // ===================== INotifyPropertyChanged =====================
    public event PropertyChangedEventHandler? PropertyChanged;

    protected void OnPropertyChanged(
        [CallerMemberName] string? propertyName = null)
    {
        PropertyChanged?.Invoke(
            this,
            new PropertyChangedEventArgs(propertyName));
    }
}
