using System.Collections.ObjectModel;
using System.ComponentModel;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Data;
using StudentRegistrationApp.Data;
using StudentRegistrationApp.Models;

namespace StudentRegistrationApp
{
    public partial class MainWindow : Window
    {
        private readonly StudentRepository _repository;
        private readonly ObservableCollection<Student> _students = new();
        private readonly ICollectionView _studentView;

        // Mahasiswa yang sedang dipilih untuk diedit (null = mode tambah)
        private Student? _selectedStudent;

        public MainWindow()
        {
            InitializeComponent();

            _repository = new StudentRepository();

            // Hubungkan koleksi data ke DataGrid, lengkap dengan filter pencarian
            _studentView = CollectionViewSource.GetDefaultView(_students);
            _studentView.Filter = FilterStudent;
            dgMahasiswa.ItemsSource = _studentView;

            LoadData();
            txtNim.Focus();
        }

        // ================= READ =================
        private void LoadData()
        {
            _students.Clear();
            foreach (var student in _repository.GetAll())
            {
                _students.Add(student);
            }
            UpdateCounter();
        }

        // ================= CREATE =================
        private void BtnSimpan_Click(object sender, RoutedEventArgs e)
        {
            if (!ValidateInput(excludeId: 0))
                return;

            var student = ReadForm();
            _repository.Insert(student);
            _students.Add(student);
            UpdateCounter();

            MessageBox.Show(
                "Data mahasiswa berhasil disimpan!",
                "Informasi",
                MessageBoxButton.OK,
                MessageBoxImage.Information);

            ResetForm();
        }

        // ================= UPDATE =================
        private void BtnUpdate_Click(object sender, RoutedEventArgs e)
        {
            if (_selectedStudent == null)
            {
                MessageBox.Show("Pilih data yang ingin diedit!", "Peringatan",
                    MessageBoxButton.OK, MessageBoxImage.Warning);
                return;
            }

            if (!ValidateInput(excludeId: _selectedStudent.Id))
                return;

            var updated = ReadForm();
            updated.Id = _selectedStudent.Id;
            _repository.Update(updated);

            // Ganti item lama di koleksi agar DataGrid ikut ter-refresh
            int index = _students.IndexOf(_selectedStudent);
            _students[index] = updated;

            MessageBox.Show(
                "Data mahasiswa berhasil diperbarui!",
                "Informasi",
                MessageBoxButton.OK,
                MessageBoxImage.Information);

            ResetForm();
        }

        // ================= DELETE =================
        private void BtnHapus_Click(object sender, RoutedEventArgs e)
        {
            if (dgMahasiswa.SelectedItem is not Student student)
            {
                MessageBox.Show("Pilih data yang ingin dihapus!", "Peringatan",
                    MessageBoxButton.OK, MessageBoxImage.Warning);
                return;
            }

            // Level 2: konfirmasi sebelum hapus
            var result = MessageBox.Show(
                $"Yakin ingin menghapus data berikut?\n\n{student}",
                "Konfirmasi Hapus",
                MessageBoxButton.YesNo,
                MessageBoxImage.Question);

            if (result != MessageBoxResult.Yes)
                return;

            _repository.Delete(student.Id);
            _students.Remove(student);
            UpdateCounter();
            ResetForm();
        }

        // ================= RESET =================
        private void BtnReset_Click(object sender, RoutedEventArgs e)
        {
            ResetForm();
        }

        private void ResetForm()
        {
            txtNim.Clear();
            txtNama.Clear();
            cmbProdi.SelectedIndex = -1;
            rbLaki.IsChecked = false;
            rbPerempuan.IsChecked = false;
            dpTanggalLahir.SelectedDate = null;
            txtAlamat.Clear();
            txtTelepon.Clear();

            _selectedStudent = null;
            dgMahasiswa.SelectedItem = null;
            btnUpdate.IsEnabled = false;
            btnSimpan.IsEnabled = true;
            txtModeForm.Text = "Mode: Tambah Data Baru";

            txtNim.Focus();
        }

        // ================= EDIT (pilih baris) =================
        private void DgMahasiswa_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if (dgMahasiswa.SelectedItem is not Student student)
                return;

            _selectedStudent = student;

            txtNim.Text = student.Nim;
            txtNama.Text = student.Nama;
            cmbProdi.SelectedItem = cmbProdi.Items
                .OfType<ComboBoxItem>()
                .FirstOrDefault(i => i.Content?.ToString() == student.Prodi);
            rbLaki.IsChecked = student.JenisKelamin == "Laki-laki";
            rbPerempuan.IsChecked = student.JenisKelamin == "Perempuan";
            dpTanggalLahir.SelectedDate = student.TanggalLahir;
            txtAlamat.Text = student.Alamat;
            txtTelepon.Text = student.NoTelepon;

            btnUpdate.IsEnabled = true;
            btnSimpan.IsEnabled = false;
            txtModeForm.Text = $"Mode: Edit Data ({student.Nim})";
        }

        // ================= SEARCH =================
        private void TxtCari_TextChanged(object sender, TextChangedEventArgs e)
        {
            _studentView.Refresh();
            UpdateCounter();
        }

        private bool FilterStudent(object obj)
        {
            if (obj is not Student s)
                return false;

            string keyword = txtCari.Text.Trim();
            if (keyword.Length == 0)
                return true;

            return s.Nim.Contains(keyword, StringComparison.OrdinalIgnoreCase)
                || s.Nama.Contains(keyword, StringComparison.OrdinalIgnoreCase)
                || s.Prodi.Contains(keyword, StringComparison.OrdinalIgnoreCase);
        }

        // ================= COUNTER =================
        private void UpdateCounter()
        {
            int total = _students.Count;
            int shown = _studentView.Cast<object>().Count();

            txtJumlah.Text = total == shown
                ? $"Jumlah Mahasiswa: {total}"
                : $"Jumlah Mahasiswa: {total} (ditampilkan: {shown})";
        }

        // ================= VALIDASI =================
        private bool ValidateInput(int excludeId)
        {
            string nim = txtNim.Text.Trim();
            string nama = txtNama.Text.Trim();
            string telepon = txtTelepon.Text.Trim();

            if (string.IsNullOrWhiteSpace(nim))
                return Warn("NIM harus diisi!", txtNim);

            if (!nim.All(char.IsDigit))
                return Warn("NIM hanya boleh berisi angka!", txtNim);

            if (_repository.NimExists(nim, excludeId))
                return Warn($"NIM {nim} sudah terdaftar!", txtNim);

            if (string.IsNullOrWhiteSpace(nama))
                return Warn("Nama harus diisi!", txtNama);

            if (cmbProdi.SelectedItem == null)
                return Warn("Pilih program studi!", cmbProdi);

            if (rbLaki.IsChecked != true && rbPerempuan.IsChecked != true)
                return Warn("Pilih jenis kelamin!", rbLaki);

            if (dpTanggalLahir.SelectedDate == null)
                return Warn("Tanggal lahir harus diisi!", dpTanggalLahir);

            if (dpTanggalLahir.SelectedDate.Value.Date >= DateTime.Today)
                return Warn("Tanggal lahir tidak valid!", dpTanggalLahir);

            if (string.IsNullOrWhiteSpace(txtAlamat.Text))
                return Warn("Alamat harus diisi!", txtAlamat);

            if (string.IsNullOrWhiteSpace(telepon))
                return Warn("No. telepon harus diisi!", txtTelepon);

            if (!telepon.All(char.IsDigit) || telepon.Length < 10 || telepon.Length > 13)
                return Warn("No. telepon harus berupa angka 10-13 digit!", txtTelepon);

            return true;
        }

        private static bool Warn(string message, Control focusTarget)
        {
            MessageBox.Show(message, "Validasi", MessageBoxButton.OK, MessageBoxImage.Warning);
            focusTarget.Focus();
            return false;
        }

        // Membaca isi form menjadi objek Student
        private Student ReadForm()
        {
            return new Student
            {
                Nim = txtNim.Text.Trim(),
                Nama = txtNama.Text.Trim(),
                Prodi = (cmbProdi.SelectedItem as ComboBoxItem)?.Content?.ToString() ?? "",
                JenisKelamin = rbLaki.IsChecked == true ? "Laki-laki" : "Perempuan",
                TanggalLahir = dpTanggalLahir.SelectedDate!.Value.Date,
                Alamat = txtAlamat.Text.Trim(),
                NoTelepon = txtTelepon.Text.Trim()
            };
        }
    }
}
