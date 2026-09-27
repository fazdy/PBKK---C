using System.Globalization;
using System.IO;
using Microsoft.Data.Sqlite;
using StudentRegistrationApp.Models;

namespace StudentRegistrationApp.Data
{
    /// <summary>
    /// Akses data mahasiswa ke database SQLite (file mahasiswa.db).
    /// Menyediakan operasi CRUD: Create, Read, Update, Delete.
    /// </summary>
    public class StudentRepository
    {
        private const string DateFormat = "yyyy-MM-dd";
        private readonly string _connectionString;

        public StudentRepository()
        {
            string dbPath = Path.Combine(AppContext.BaseDirectory, "mahasiswa.db");
            _connectionString = $"Data Source={dbPath}";
            InitializeDatabase();
        }

        private SqliteConnection OpenConnection()
        {
            var connection = new SqliteConnection(_connectionString);
            connection.Open();
            return connection;
        }

        /// <summary>
        /// Membuat tabel Mahasiswa jika belum ada.
        /// </summary>
        private void InitializeDatabase()
        {
            using var connection = OpenConnection();
            using var command = connection.CreateCommand();
            command.CommandText = @"
                CREATE TABLE IF NOT EXISTS Mahasiswa (
                    Id            INTEGER PRIMARY KEY AUTOINCREMENT,
                    Nim           TEXT NOT NULL UNIQUE,
                    Nama          TEXT NOT NULL,
                    Prodi         TEXT NOT NULL,
                    JenisKelamin  TEXT NOT NULL,
                    TanggalLahir  TEXT NOT NULL,
                    Alamat        TEXT NOT NULL,
                    NoTelepon     TEXT NOT NULL
                );";
            command.ExecuteNonQuery();
        }

        // READ
        public List<Student> GetAll()
        {
            var students = new List<Student>();

            using var connection = OpenConnection();
            using var command = connection.CreateCommand();
            command.CommandText = @"
                SELECT Id, Nim, Nama, Prodi, JenisKelamin, TanggalLahir, Alamat, NoTelepon
                FROM Mahasiswa
                ORDER BY Nim;";

            using var reader = command.ExecuteReader();
            while (reader.Read())
            {
                students.Add(new Student
                {
                    Id = reader.GetInt32(0),
                    Nim = reader.GetString(1),
                    Nama = reader.GetString(2),
                    Prodi = reader.GetString(3),
                    JenisKelamin = reader.GetString(4),
                    TanggalLahir = DateTime.ParseExact(
                        reader.GetString(5), DateFormat, CultureInfo.InvariantCulture),
                    Alamat = reader.GetString(6),
                    NoTelepon = reader.GetString(7)
                });
            }

            return students;
        }

        // CREATE
        public void Insert(Student student)
        {
            using var connection = OpenConnection();
            using var command = connection.CreateCommand();
            command.CommandText = @"
                INSERT INTO Mahasiswa
                    (Nim, Nama, Prodi, JenisKelamin, TanggalLahir, Alamat, NoTelepon)
                VALUES
                    ($nim, $nama, $prodi, $jk, $tgl, $alamat, $telp);
                SELECT last_insert_rowid();";
            AddParameters(command, student);

            student.Id = Convert.ToInt32(command.ExecuteScalar());
        }

        // UPDATE
        public void Update(Student student)
        {
            using var connection = OpenConnection();
            using var command = connection.CreateCommand();
            command.CommandText = @"
                UPDATE Mahasiswa SET
                    Nim = $nim,
                    Nama = $nama,
                    Prodi = $prodi,
                    JenisKelamin = $jk,
                    TanggalLahir = $tgl,
                    Alamat = $alamat,
                    NoTelepon = $telp
                WHERE Id = $id;";
            AddParameters(command, student);
            command.Parameters.AddWithValue("$id", student.Id);

            command.ExecuteNonQuery();
        }

        // DELETE
        public void Delete(int id)
        {
            using var connection = OpenConnection();
            using var command = connection.CreateCommand();
            command.CommandText = "DELETE FROM Mahasiswa WHERE Id = $id;";
            command.Parameters.AddWithValue("$id", id);

            command.ExecuteNonQuery();
        }

        /// <summary>
        /// Mengecek apakah NIM sudah dipakai mahasiswa lain.
        /// excludeId dipakai saat edit agar data itu sendiri tidak dihitung.
        /// </summary>
        public bool NimExists(string nim, int excludeId = 0)
        {
            using var connection = OpenConnection();
            using var command = connection.CreateCommand();
            command.CommandText =
                "SELECT COUNT(*) FROM Mahasiswa WHERE Nim = $nim AND Id <> $id;";
            command.Parameters.AddWithValue("$nim", nim);
            command.Parameters.AddWithValue("$id", excludeId);

            return Convert.ToInt32(command.ExecuteScalar()) > 0;
        }

        private static void AddParameters(SqliteCommand command, Student s)
        {
            command.Parameters.AddWithValue("$nim", s.Nim);
            command.Parameters.AddWithValue("$nama", s.Nama);
            command.Parameters.AddWithValue("$prodi", s.Prodi);
            command.Parameters.AddWithValue("$jk", s.JenisKelamin);
            command.Parameters.AddWithValue("$tgl",
                s.TanggalLahir.ToString(DateFormat, CultureInfo.InvariantCulture));
            command.Parameters.AddWithValue("$alamat", s.Alamat);
            command.Parameters.AddWithValue("$telp", s.NoTelepon);
        }
    }
}
