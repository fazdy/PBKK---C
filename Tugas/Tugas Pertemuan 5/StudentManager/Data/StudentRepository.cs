using Microsoft.Data.SqlClient;
using StudentManager.Models;

namespace StudentManager.Data;

/// <summary>
/// Repository: satu-satunya kelas yang berkomunikasi dengan SQL Server
/// menggunakan ADO.NET (Microsoft.Data.SqlClient).
/// </summary>
public class StudentRepository
{
    private const string DefaultConnectionString =
        @"Server=(localdb)\MSSQLLocalDB;" +
        "Database=StudentDB;" +
        "Trusted_Connection=True;" +
        "TrustServerCertificate=True;";

    private readonly string connectionString;

    public StudentRepository() : this(DefaultConnectionString)
    {
    }

    public StudentRepository(string connectionString)
    {
        this.connectionString = connectionString;
    }

    // ===================== READ =====================
    public List<Student> GetAll()
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        string sql =
            "SELECT Id, NIM, Nama, Jurusan, Gender, Email " +
            "FROM Students ORDER BY Id DESC";

        using SqlCommand command =
            new SqlCommand(sql, connection);

        connection.Open();

        return ReadStudents(command);
    }

    // ===================== SEARCH =====================
    public List<Student> Search(string keyword)
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        string sql =
            "SELECT Id, NIM, Nama, Jurusan, Gender, Email " +
            "FROM Students WHERE NIM LIKE @Keyword " +
            "OR Nama LIKE @Keyword OR Jurusan LIKE @Keyword " +
            "ORDER BY Id DESC";

        using SqlCommand command =
            new SqlCommand(sql, connection);

        command.Parameters.AddWithValue("@Keyword", "%" + keyword + "%");

        connection.Open();

        return ReadStudents(command);
    }

    // ===================== CREATE =====================
    public void Insert(Student student)
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        string sql =
            "INSERT INTO Students " +
            "(NIM, Nama, Jurusan, Gender, Email) " +
            "VALUES (@NIM,@Nama,@Jurusan,@Gender,@Email)";

        using SqlCommand command =
            new SqlCommand(sql, connection);

        AddStudentParameters(command, student);

        connection.Open();
        command.ExecuteNonQuery();
    }

    // ===================== UPDATE =====================
    public void Update(Student student)
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        string sql =
            "UPDATE Students SET NIM=@NIM, Nama=@Nama, " +
            "Jurusan=@Jurusan, Gender=@Gender, Email=@Email " +
            "WHERE Id=@Id";

        using SqlCommand command =
            new SqlCommand(sql, connection);

        command.Parameters.AddWithValue("@Id", student.Id);
        AddStudentParameters(command, student);

        connection.Open();
        command.ExecuteNonQuery();
    }

    // ===================== DELETE =====================
    public void Delete(int id)
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        using SqlCommand command =
            new SqlCommand("DELETE FROM Students WHERE Id=@Id", connection);

        command.Parameters.AddWithValue("@Id", id);

        connection.Open();
        command.ExecuteNonQuery();
    }

    // ===================== VALIDASI NIM =====================
    /// <summary>
    /// Mengecek apakah NIM sudah dipakai mahasiswa lain.
    /// excludeId diisi Id data yang sedang di-update agar tidak dianggap duplikat.
    /// </summary>
    public bool IsNimExists(string nim, int excludeId = 0)
    {
        using SqlConnection connection =
            new SqlConnection(connectionString);

        string sql =
            "SELECT COUNT(1) FROM Students " +
            "WHERE NIM = @NIM AND Id <> @Id";

        using SqlCommand command =
            new SqlCommand(sql, connection);

        command.Parameters.AddWithValue("@NIM", nim);
        command.Parameters.AddWithValue("@Id", excludeId);

        connection.Open();

        return Convert.ToInt32(command.ExecuteScalar()) > 0;
    }

    // ===================== INISIALISASI DATABASE =====================
    /// <summary>
    /// Membuat database StudentDB, tabel Students, dan data contoh jika belum ada,
    /// sehingga aplikasi tetap bisa dijalankan di komputer lain tanpa menjalankan
    /// script SQL secara manual. Jika database sudah ada, method ini tidak mengubah apa pun.
    /// </summary>
    public void EnsureDatabaseCreated()
    {
        var builder = new SqlConnectionStringBuilder(connectionString);
        string databaseName = builder.InitialCatalog;

        // 1) Buat database melalui database "master".
        builder.InitialCatalog = "master";

        using (SqlConnection master = new SqlConnection(builder.ConnectionString))
        {
            string createDatabase =
                "IF DB_ID(@Name) IS NULL " +
                "BEGIN " +
                "    DECLARE @sql NVARCHAR(300) = N'CREATE DATABASE ' + QUOTENAME(@Name); " +
                "    EXEC (@sql); " +
                "END";

            using SqlCommand command = new SqlCommand(createDatabase, master);
            command.Parameters.AddWithValue("@Name", databaseName);

            master.Open();
            command.ExecuteNonQuery();
        }

        // 2) Buat tabel Students beserta data contoh.
        using SqlConnection connection = new SqlConnection(connectionString);

        string createTable = """
            IF OBJECT_ID(N'dbo.Students', N'U') IS NULL
            BEGIN
                CREATE TABLE dbo.Students
                (
                    Id INT IDENTITY(1,1) PRIMARY KEY,
                    NIM VARCHAR(20) NOT NULL,
                    Nama VARCHAR(100) NOT NULL,
                    Jurusan VARCHAR(100) NOT NULL,
                    Gender VARCHAR(20) NOT NULL,
                    Email VARCHAR(100)
                );

                INSERT INTO dbo.Students
                (NIM, Nama, Jurusan, Gender, Email)
                VALUES
                ('23001','Budi Santoso','Informatika','Laki-laki','budi@gmail.com'),
                ('23002','Siti Aminah','Sistem Informasi','Perempuan','siti@gmail.com'),
                ('23003','Andi Wijaya','Informatika','Laki-laki','andi@gmail.com'),
                ('23004','Rina Sari','Sistem Informasi','Perempuan','rina@gmail.com');
            END
            """;

        using SqlCommand tableCommand = new SqlCommand(createTable, connection);

        connection.Open();
        tableCommand.ExecuteNonQuery();
    }

    // ===================== HELPER =====================
    private static List<Student> ReadStudents(SqlCommand command)
    {
        var students = new List<Student>();

        using SqlDataReader reader = command.ExecuteReader();

        while (reader.Read())
        {
            students.Add(new Student
            {
                Id = Convert.ToInt32(reader["Id"]),
                NIM = reader["NIM"].ToString()!,
                Nama = reader["Nama"].ToString()!,
                Jurusan = reader["Jurusan"].ToString()!,
                Gender = reader["Gender"].ToString()!,
                Email = reader["Email"].ToString()!   // NULL di database → ""
            });
        }

        return students;
    }

    private static void AddStudentParameters(SqlCommand command, Student student)
    {
        command.Parameters.AddWithValue("@NIM", student.NIM);
        command.Parameters.AddWithValue("@Nama", student.Nama);
        command.Parameters.AddWithValue("@Jurusan", student.Jurusan);
        command.Parameters.AddWithValue("@Gender", student.Gender);

        // Email bersifat opsional: string kosong disimpan sebagai NULL.
        command.Parameters.AddWithValue("@Email",
            string.IsNullOrWhiteSpace(student.Email) ? DBNull.Value : (object)student.Email);
    }
}
