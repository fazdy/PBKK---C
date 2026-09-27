# Student Registration App (WPF)

Tugas Pertemuan 4 PBKK (C): Latihan Student Registration App dengan WPF.

- Nama : Faizal Aldy Armiriawan
- NRP  : 5025231266
- Kelas: PBKK (C)

## Fitur
- Form pendaftaran: NIM, Nama, Program Studi (ComboBox), Jenis Kelamin (RadioButton), Tanggal Lahir (DatePicker), Alamat, No. Telepon
- Validasi input (field wajib, NIM angka dan unik, tanggal lahir, no. telepon 10-13 digit)
- CRUD: Simpan, tampilkan (DataGrid), Edit/Update, Hapus dengan konfirmasi, Reset form
- Pencarian berdasarkan NIM, Nama, atau Prodi
- Counter jumlah mahasiswa
- Penyimpanan permanen ke database SQLite (`mahasiswa.db`, dibuat otomatis)

## Menjalankan
Butuh Windows + .NET 8 SDK (atau Visual Studio 2022 dengan workload ".NET desktop development").

```
dotnet run --project StudentRegistrationApp
```

Atau buka `StudentRegistrationApp.sln` di Visual Studio lalu tekan F5.
