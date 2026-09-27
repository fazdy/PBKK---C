namespace StudentRegistrationApp.Models
{
    /// <summary>
    /// Model data mahasiswa. Sesuai dengan kolom tabel Mahasiswa di database.
    /// </summary>
    public class Student
    {
        public int Id { get; set; }
        public string Nim { get; set; } = string.Empty;
        public string Nama { get; set; } = string.Empty;
        public string Prodi { get; set; } = string.Empty;
        public string JenisKelamin { get; set; } = string.Empty;
        public DateTime TanggalLahir { get; set; }
        public string Alamat { get; set; } = string.Empty;
        public string NoTelepon { get; set; } = string.Empty;

        /// <summary>
        /// Format ringkas seperti pada latihan awal (ListBox).
        /// </summary>
        public override string ToString() =>
            $"{Nim} | {Nama} | {Prodi} | {JenisKelamin}";
    }
}
