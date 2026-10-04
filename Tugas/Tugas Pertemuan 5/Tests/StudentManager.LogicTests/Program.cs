// =====================================================================
//  Program uji logika Student Management System (tanpa UI & tanpa SQL Server).
//  Kode yang diuji adalah kode ASLI project WPF (StudentViewModel, RelayCommand,
//  Student, Converter); hanya Repository dan DialogService yang diganti versi
//  palsu (Fakes.cs). Urutan skenario mengikuti tabel pengujian di LAPORAN.md.
//
//  Jalankan dari folder "Tugas Pertemuan 5":
//      dotnet run --project Tests/StudentManager.LogicTests
// =====================================================================

using System.Globalization;
using StudentManager.Converters;
using StudentManager.Data;
using StudentManager.Services;
using StudentManager.ViewModels;

int passed = 0, failed = 0;

void Check(bool condition, string name)
{
    if (condition) { passed++; Console.WriteLine($"  [PASS] {name}"); }
    else { failed++; Console.WriteLine($"  [FAIL] {name}"); }
}

string Stats(StudentViewModel v) =>
    $"Total={v.TotalStudents}, Inf={v.TotalInformatika}, SI={v.TotalSistemInformasi}, L={v.TotalLakiLaki}, P={v.TotalPerempuan}";

// Meniru pengguna mengetik di form (TwoWay binding menulis ke CurrentStudent)
void Fill(StudentViewModel v, string nim, string nama, string jurusan, string gender, string email)
{
    v.CurrentStudent.NIM = nim;
    v.CurrentStudent.Nama = nama;
    v.CurrentStudent.Jurusan = jurusan;
    v.CurrentStudent.Gender = gender;
    v.CurrentStudent.Email = email;
}

var repo = new StudentRepository();
var dialog = new DialogService();
var vm = new StudentViewModel(repo, dialog);

Console.WriteLine("T1  READ");
Check(vm.Students.Count == 4, "4 data contoh tampil di DataGrid");
Check(string.Join(",", vm.Students.Select(s => s.NIM)) == "23004,23003,23002,23001", "urutan ORDER BY Id DESC");
Check(Stats(vm) == "Total=4, Inf=2, SI=2, L=2, P=2", "statistik awal → " + Stats(vm));
Check(!vm.IsEditMode && vm.FormModeText == "Mode Tambah" && vm.SaveButtonText == "Simpan", "form dalam Mode Tambah");
Check(vm.StatusMessage == "Terhubung ke database StudentDB. 4 data mahasiswa dimuat.", "status → " + vm.StatusMessage);
Check(dialog.Log.Count == 0, "tidak ada dialog error");
Console.WriteLine("        TodayText = " + vm.TodayText);

Console.WriteLine("T2  VALIDASI");
vm.SaveCommand.Execute(null);
var last = dialog.Log.Last();
Check(last.Kind == "warning" && last.Message.Contains("NIM wajib diisi") && last.Message.Contains("Nama wajib diisi")
      && last.Message.Contains("Jurusan wajib dipilih") && last.Message.Contains("Gender wajib dipilih"),
      "form kosong → 4 pesan validasi sekaligus");
Console.WriteLine("        dialog: " + last.Message.Replace("\n", " | "));
Fill(vm, "23A01", "Dewi Lestari", "Sistem Informasi", "Perempuan", "dewi@gmail.com");
vm.SaveCommand.Execute(null);
Check(dialog.Log.Last().Message.Contains("NIM hanya boleh berisi angka"), "NIM berisi huruf ditolak");
Fill(vm, "23005", "Dewi Lestari", "Sistem Informasi", "Perempuan", "dewi@");
vm.SaveCommand.Execute(null);
Check(dialog.Log.Last().Message.Contains("Format email tidak valid"), "format email salah ditolak");
Fill(vm, "23001", "Dewi Lestari", "Sistem Informasi", "Perempuan", "dewi@gmail.com");
vm.SaveCommand.Execute(null);
Check(dialog.Log.Last().Message.Contains("NIM 23001 sudah terdaftar"), "NIM duplikat ditolak");
Check(repo.InsertCalls == 0 && vm.Students.Count == 4, "tidak ada INSERT, data tetap 4");

Console.WriteLine("T3  CREATE");
vm.ResetCommand.Execute(null);
Fill(vm, "23005", "Dewi Lestari", "Sistem Informasi", "Perempuan", "dewi@gmail.com");
vm.SaveCommand.Execute(null);
Check(repo.InsertCalls == 1, "Repository.Insert dipanggil");
Check(vm.Students.Count == 5 && vm.Students[0].Nama == "Dewi Lestari", "data baru muncul di baris teratas");
Check(Stats(vm) == "Total=5, Inf=2, SI=3, L=2, P=3", "statistik → " + Stats(vm));
Check(vm.CurrentStudent.Id == 0 && vm.CurrentStudent.NIM == "" && !vm.IsEditMode, "form kembali kosong");
Check(vm.StatusMessage == "Data Dewi Lestari berhasil disimpan.", "status → " + vm.StatusMessage);

Console.WriteLine("T4  UPDATE");
var andi = vm.Students.First(s => s.NIM == "23003");
vm.SelectedStudent = andi; // pengguna klik baris di DataGrid
Check(vm.IsEditMode && vm.SaveButtonText == "Update" && vm.FormModeText == "Mode Edit", "mode edit aktif (tombol jadi Update)");
Check(!ReferenceEquals(vm.CurrentStudent, andi) && vm.CurrentStudent.Nama == "Andi Wijaya", "form berisi SALINAN data terpilih");
vm.CurrentStudent.Jurusan = "Sistem Informasi";
Check(andi.Jurusan == "Informatika", "DataGrid belum berubah sebelum Update ditekan");
vm.SaveCommand.Execute(null);
Check(repo.UpdateCalls == 1 && repo.InsertCalls == 1, "Repository.Update dipanggil (bukan Insert)");
Check(vm.Students.First(s => s.NIM == "23003").Jurusan == "Sistem Informasi", "jurusan Andi tersimpan");
Check(Stats(vm) == "Total=5, Inf=1, SI=4, L=2, P=3", "statistik → " + Stats(vm));
Check(vm.StatusMessage == "Data Andi Wijaya berhasil diperbarui.", "status → " + vm.StatusMessage);
Check(!vm.IsEditMode && vm.SelectedStudent == null, "form & seleksi direset");

vm.SelectedStudent = vm.Students.First(s => s.NIM == "23001");
vm.CurrentStudent.Email = "budi.santoso@gmail.com";
vm.SaveCommand.Execute(null);
Check(repo.UpdateCalls == 2 && vm.Students.First(s => s.NIM == "23001").Email == "budi.santoso@gmail.com",
      "update data sendiri (NIM sama) tidak dianggap duplikat");
vm.SelectedStudent = vm.Students.First(s => s.NIM == "23002");
vm.CurrentStudent.NIM = "23001";
vm.SaveCommand.Execute(null);
Check(dialog.Log.Last().Message.Contains("NIM 23001 sudah terdaftar") && repo.UpdateCalls == 2,
      "update ke NIM milik mahasiswa lain ditolak");
vm.SelectedStudent = vm.Students.First(s => s.NIM == "23002");
vm.CurrentStudent.Nama = "Siti Aminah (edit)";
vm.SelectedStudent = null; // DataGrid kehilangan seleksi (mis. setelah refresh)
Check(vm.IsEditMode && vm.CurrentStudent.Nama == "Siti Aminah (edit)", "seleksi DataGrid hilang tidak menghapus isi form");
vm.ResetCommand.Execute(null);
Check(vm.Students.First(s => s.NIM == "23002").Nama == "Siti Aminah", "perubahan yang tidak disimpan tidak masuk ke data");

Console.WriteLine("T5  DELETE");
var dewi = vm.Students.First(s => s.NIM == "23005");
dialog.ConfirmAnswer = false;
vm.DeleteCommand.Execute(dewi); // tombol hapus pada kolom Aksi
Check(dialog.Log.Last().Kind == "confirm" && dialog.Log.Last().Message.Contains("23005"), "dialog konfirmasi menampilkan NIM");
Check(repo.DeleteCalls == 0 && vm.Students.Count == 5 && vm.StatusMessage == "Penghapusan data dibatalkan.", "pilih No → data tidak terhapus");
dialog.ConfirmAnswer = true;
vm.DeleteCommand.Execute(dewi);
Check(repo.DeleteCalls == 1 && vm.Students.Count == 4 && vm.Students.All(s => s.NIM != "23005"), "pilih Yes → data terhapus");
Check(Stats(vm) == "Total=4, Inf=1, SI=3, L=2, P=2", "statistik → " + Stats(vm));
Check(vm.StatusMessage == "Data Dewi Lestari berhasil dihapus.", "status → " + vm.StatusMessage);
int logCount = dialog.Log.Count;
vm.DeleteCommand.Execute(null);
Check(dialog.Log.Count == logCount + 1 && dialog.Log.Last().Kind == "warning" && repo.DeleteCalls == 1,
      "hapus tanpa memilih data → peringatan");

Console.WriteLine("T6  SEARCH");
vm.SearchText = "Budi";
vm.SearchCommand.Execute(null);
Check(vm.Students.Count == 1 && vm.Students[0].Nama == "Budi Santoso", "cari Nama 'Budi' → 1 data");
Check(vm.InfoCaption == "Hasil pencarian \"Budi\"" && Stats(vm) == "Total=1, Inf=1, SI=0, L=1, P=0", "statistik mengikuti hasil → " + Stats(vm));
vm.SearchText = "23002";
vm.SearchCommand.Execute(null);
Check(vm.Students.Count == 1 && vm.Students[0].Nama == "Siti Aminah", "cari NIM '23002' → Siti Aminah");
vm.SearchText = "Sistem Informasi";
vm.SearchCommand.Execute(null);
Check(string.Join(", ", vm.Students.Select(s => s.Nama)) == "Rina Sari, Andi Wijaya, Siti Aminah", "cari Jurusan 'Sistem Informasi' → 3 data");
Check(Stats(vm) == "Total=3, Inf=0, SI=3, L=1, P=2", "statistik → " + Stats(vm));
Check(vm.StatusMessage == "Ditemukan 3 data untuk kata kunci \"Sistem Informasi\".", "status → " + vm.StatusMessage);
vm.SearchText = "xyz";
vm.SearchCommand.Execute(null);
Check(vm.Students.Count == 0 && vm.TotalStudents == 0, "kata kunci tidak ditemukan → tabel kosong");
vm.SearchText = "budi";
vm.SearchCommand.Execute(null);
Check(vm.Students.Count == 1, "huruf kecil tetap ditemukan (meniru collation CI SQL Server)");
vm.SearchText = "   ";
vm.SearchCommand.Execute(null);
Check(vm.Students.Count == 4 && vm.InfoCaption == "Seluruh data mahasiswa", "kata kunci kosong → semua data");
vm.SearchText = "Budi";
vm.SearchCommand.Execute(null);
vm.ClearSearchCommand.Execute(null);
Check(vm.SearchText == "" && vm.Students.Count == 4 && vm.InfoCaption == "Seluruh data mahasiswa", "tombol Reset pencarian → semua data");

Console.WriteLine("T7  RESET");
vm.SelectedStudent = vm.Students[0];
Check(vm.IsEditMode, "baris dipilih → mode edit");
vm.ResetCommand.Execute(null);
Check(!vm.IsEditMode && vm.SelectedStudent == null && vm.CurrentStudent.NIM == "" && vm.CurrentStudent.Jurusan == ""
      && vm.CurrentStudent.Gender == "" && vm.FormModeText == "Mode Tambah", "form kembali ke kondisi awal");
Check(vm.StatusMessage == "Form dikosongkan. Silakan input data mahasiswa baru.", "status → " + vm.StatusMessage);

Console.WriteLine("T8  EDIT & HAPUS DARI FORM");
var rina = vm.Students.First(s => s.NIM == "23004");
vm.EditCommand.Execute(rina);
Check(vm.SelectedStudent == rina && vm.CurrentStudent.Nama == "Rina Sari" && !ReferenceEquals(vm.CurrentStudent, rina),
      "tombol Edit memuat salinan data ke form");
vm.DeleteCommand.Execute(null);
Check(repo.DeleteCalls == 2 && vm.Students.All(s => s.NIM != "23004"), "tombol Hapus di form menghapus data yang sedang diedit");
Check(Stats(vm) == "Total=3, Inf=1, SI=2, L=2, P=1", "statistik → " + Stats(vm));

Console.WriteLine("T9  TRIM & EMAIL OPSIONAL");
vm.ResetCommand.Execute(null);
Fill(vm, "  5025231266 ", "  Faizal Aldy Armiriawan ", "Informatika", "Laki-laki", "");
vm.SaveCommand.Execute(null);
var me = vm.Students.FirstOrDefault(s => s.NIM == "5025231266");
Check(me != null && me.Nama == "Faizal Aldy Armiriawan" && me.Email == "", "spasi dipangkas & email boleh kosong");

Console.WriteLine("T10 NOTIFIKASI PropertyChanged");
var raised = new HashSet<string>();
vm.PropertyChanged += (_, e) => raised.Add(e.PropertyName!);
vm.SelectedStudent = vm.Students[0];
vm.ResetCommand.Execute(null);
Fill(vm, "23010", "Uji Notifikasi", "Informatika", "Perempuan", "uji@gmail.com");
vm.SaveCommand.Execute(null);
foreach (var p in new[] { "TotalStudents", "TotalInformatika", "TotalSistemInformasi", "TotalLakiLaki", "TotalPerempuan",
                          "CurrentStudent", "IsEditMode", "FormModeText", "SaveButtonText", "StatusMessage",
                          "InfoCaption", "SearchText", "SelectedStudent" })
    Check(raised.Contains(p), $"PropertyChanged(\"{p}\") dikirim");

Console.WriteLine("T11 ERROR DATABASE");
var badRepo = new StudentRepository { FailOnInit = true };
var badDialog = new DialogService();
StudentViewModel? badVm = null;
Exception? ctorError = null;
try { badVm = new StudentViewModel(badRepo, badDialog); } catch (Exception ex) { ctorError = ex; }
Check(ctorError == null && badVm != null, "ViewModel tidak crash saat database tidak tersedia");
Check(badVm!.StatusMessage.StartsWith("Gagal terhubung") && badDialog.Log.Single().Kind == "error", "dialog error koneksi ditampilkan");
badRepo.Fail = true;
Fill(badVm, "23020", "Uji Error", "Informatika", "Laki-laki", "");
badVm.SaveCommand.Execute(null);
Check(badDialog.Log.Last().Kind == "error" && badVm.StatusMessage.StartsWith("Gagal menyimpan"), "error saat simpan ditangani");
badVm.SearchText = "a";
badVm.SearchCommand.Execute(null);
Check(badVm.StatusMessage.StartsWith("Gagal mencari"), "error saat cari ditangani");

Console.WriteLine("T12 RELAYCOMMAND & CONVERTER");
int counter = 0;
var command = new RelayCommand(() => counter++);
command.Execute(null);
Check(command.CanExecute(null) && counter == 1, "RelayCommand(Action) berjalan, CanExecute default true");
object? received = null;
var commandWithParam = new RelayCommand(p => received = p, p => p is string);
commandWithParam.Execute("abc");
Check((string?)received == "abc" && commandWithParam.CanExecute("x") && !commandWithParam.CanExecute(1), "CommandParameter & predicate CanExecute");
bool changed = false;
commandWithParam.CanExecuteChanged += (_, _) => changed = true;
commandWithParam.RaiseCanExecuteChanged();
Check(changed, "event CanExecuteChanged");
var rowNumber = new RowNumberConverter();
Check((string)rowNumber.Convert(0, typeof(string), null!, CultureInfo.InvariantCulture) == "1"
      && (string)rowNumber.Convert(9, typeof(string), null!, CultureInfo.InvariantCulture) == "10", "RowNumberConverter: indeks 0 → No 1");
var emptyToNull = new EmptyStringToNullConverter();
Check(emptyToNull.Convert("", typeof(object), null, CultureInfo.InvariantCulture) == null
      && (string?)emptyToNull.Convert("Informatika", typeof(object), null, CultureInfo.InvariantCulture) == "Informatika",
      "EmptyStringToNullConverter: \"\" → null (ComboBox kosong)");
Check((string)emptyToNull.ConvertBack(null, typeof(string), null, CultureInfo.InvariantCulture) == "", "EmptyStringToNullConverter: null → \"\"");

Console.WriteLine($"\nHASIL: {passed} lulus, {failed} gagal");
return failed == 0 ? 0 : 1;
