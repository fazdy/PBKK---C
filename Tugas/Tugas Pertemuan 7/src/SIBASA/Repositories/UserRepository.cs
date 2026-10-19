using Microsoft.Data.Sqlite;
using SIBASA.Data;
using SIBASA.Models;

namespace SIBASA.Repositories;

/// <summary>Akses data tabel Users.</summary>
public sealed class UserRepository : RepositoryBase
{
    private const string SelectColumns =
        "UserId, Username, PasswordHash, NamaLengkap, Role, IsActive, CreatedAt, LastLoginAt";

    public UserRepository(SqliteConnectionFactory factory) : base(factory)
    {
    }

    public Task<User?> GetByUsernameAsync(string username) =>
        RunAsync(c => QuerySingleAsync(c, null,
            $"SELECT {SelectColumns} FROM Users WHERE Username = $username;",
            Map, ("$username", username.Trim())));

    public Task<User?> GetByIdAsync(int userId) =>
        RunAsync(c => QuerySingleAsync(c, null,
            $"SELECT {SelectColumns} FROM Users WHERE UserId = $id;",
            Map, ("$id", userId)));

    public Task<List<User>> GetAllAsync() =>
        RunAsync(c => QueryAsync(c, null,
            $"SELECT {SelectColumns} FROM Users ORDER BY Role, Username COLLATE NOCASE;",
            Map));

    public Task<bool> UsernameExistsAsync(string username, int? excludeUserId) =>
        RunAsync(async c => await ScalarLongAsync(c, null,
            "SELECT COUNT(*) FROM Users WHERE Username = $username AND ($exclude IS NULL OR UserId <> $exclude);",
            ("$username", username.Trim()), ("$exclude", excludeUserId)) > 0);

    public Task<long> CountActiveAdminsAsync() =>
        RunAsync(c => ScalarLongAsync(c, null,
            "SELECT COUNT(*) FROM Users WHERE Role = 'Admin' AND IsActive = 1;"));

    public Task<int> InsertAsync(User user) =>
        RunAsync(async c => (int)await ScalarLongAsync(c, null,
            """
            INSERT INTO Users (Username, PasswordHash, NamaLengkap, Role, IsActive, CreatedAt)
            VALUES ($username, $hash, $nama, $role, $aktif, $dibuat)
            RETURNING UserId;
            """,
            ("$username", user.Username),
            ("$hash", user.PasswordHash),
            ("$nama", user.NamaLengkap),
            ("$role", user.Role.ToString()),
            ("$aktif", user.IsActive ? 1 : 0),
            ("$dibuat", DbDate.ToDb(DateTime.Now))));

    public Task<int> UpdateAsync(User user) =>
        RunAsync(c => ExecuteAsync(c, null,
            "UPDATE Users SET NamaLengkap = $nama, Role = $role, IsActive = $aktif WHERE UserId = $id;",
            ("$nama", user.NamaLengkap),
            ("$role", user.Role.ToString()),
            ("$aktif", user.IsActive ? 1 : 0),
            ("$id", user.UserId)));

    public Task<int> UpdatePasswordAsync(int userId, string passwordHash) =>
        RunAsync(c => ExecuteAsync(c, null,
            "UPDATE Users SET PasswordHash = $hash WHERE UserId = $id;",
            ("$hash", passwordHash), ("$id", userId)));

    public Task<int> UpdateLastLoginAsync(int userId, DateTime waktu) =>
        RunAsync(c => ExecuteAsync(c, null,
            "UPDATE Users SET LastLoginAt = $waktu WHERE UserId = $id;",
            ("$waktu", DbDate.ToDb(waktu)), ("$id", userId)));

    private static User Map(SqliteDataReader r) => new()
    {
        UserId = r.Int("UserId"),
        Username = r.Str("Username"),
        PasswordHash = r.Str("PasswordHash"),
        NamaLengkap = r.Str("NamaLengkap"),
        Role = Enum.Parse<UserRole>(r.Str("Role")),
        IsActive = r.Bool("IsActive"),
        CreatedAt = r.Date("CreatedAt"),
        LastLoginAt = r.DateOrNull("LastLoginAt")
    };
}
