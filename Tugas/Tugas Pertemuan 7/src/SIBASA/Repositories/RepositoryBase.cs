using Microsoft.Data.Sqlite;
using SIBASA.Data;

namespace SIBASA.Repositories;

/// <summary>
/// Dasar semua repository: membuka koneksi, menjalankan query berparameter,
/// dan mengelola transaksi database.
/// </summary>
public abstract class RepositoryBase
{
    protected RepositoryBase(SqliteConnectionFactory factory)
    {
        Factory = factory;
    }

    protected SqliteConnectionFactory Factory { get; }

    /// <summary>
    /// Menjalankan pekerjaan database di thread latar.
    /// Metode *Async milik Microsoft.Data.Sqlite sebenarnya berjalan sinkron, jadi dibungkus
    /// Task.Run agar UI tetap responsif (tidak membeku) selama query berjalan.
    /// </summary>
    protected Task<T> RunAsync<T>(Func<SqliteConnection, Task<T>> work) =>
        Task.Run(async () =>
        {
            await using var connection = Factory.Create();
            await connection.OpenAsync();
            return await work(connection);
        });

    protected Task RunAsync(Func<SqliteConnection, Task> work) =>
        RunAsync<bool>(async connection =>
        {
            await work(connection);
            return true;
        });

    /// <summary>
    /// Menjalankan beberapa perintah sebagai satu transaksi: semuanya tersimpan (COMMIT)
    /// atau semuanya dibatalkan (ROLLBACK) bila ada yang gagal.
    /// </summary>
    protected Task<T> InTransactionAsync<T>(Func<SqliteConnection, SqliteTransaction, Task<T>> work) =>
        Task.Run(async () =>
        {
            await using var connection = Factory.Create();
            await connection.OpenAsync();

            // deferred: false = BEGIN IMMEDIATE. Kunci tulis diambil sejak awal sehingga pola
            // "baca saldo lalu ubah saldo" aman dari perubahan oleh proses lain di tengah jalan.
            await using var transaction = connection.BeginTransaction(deferred: false);
            try
            {
                var result = await work(connection, transaction);
                await transaction.CommitAsync();
                return result;
            }
            catch
            {
                try
                {
                    await transaction.RollbackAsync();
                }
                catch
                {
                    // Abaikan kegagalan rollback agar error aslinya yang dilaporkan.
                }

                throw;
            }
        });

    /// <summary>
    /// Membuat perintah SQL dengan parameter. Nilai dari pengguna SELALU dikirim sebagai parameter
    /// ($nama), tidak pernah digabung ke teks SQL, sehingga aman dari SQL injection.
    /// </summary>
    protected static SqliteCommand Command(
        SqliteConnection connection,
        SqliteTransaction? transaction,
        string sql,
        params (string Name, object? Value)[] parameters)
    {
        var command = connection.CreateCommand();
        command.Transaction = transaction;
        command.CommandText = sql;
        foreach (var (name, value) in parameters)
        {
            command.Parameters.AddWithValue(name, value ?? DBNull.Value);
        }

        return command;
    }

    protected static async Task<long> ScalarLongAsync(
        SqliteConnection connection,
        SqliteTransaction? transaction,
        string sql,
        params (string Name, object? Value)[] parameters)
    {
        await using var command = Command(connection, transaction, sql, parameters);
        var result = await command.ExecuteScalarAsync();
        return result is null or DBNull ? 0 : Convert.ToInt64(result);
    }

    protected static async Task<int> ExecuteAsync(
        SqliteConnection connection,
        SqliteTransaction? transaction,
        string sql,
        params (string Name, object? Value)[] parameters)
    {
        await using var command = Command(connection, transaction, sql, parameters);
        return await command.ExecuteNonQueryAsync();
    }

    protected static async Task<List<T>> QueryAsync<T>(
        SqliteConnection connection,
        SqliteTransaction? transaction,
        string sql,
        Func<SqliteDataReader, T> map,
        params (string Name, object? Value)[] parameters)
    {
        await using var command = Command(connection, transaction, sql, parameters);
        await using var reader = await command.ExecuteReaderAsync();
        var list = new List<T>();
        while (await reader.ReadAsync())
        {
            list.Add(map(reader));
        }

        return list;
    }

    protected static async Task<T?> QuerySingleAsync<T>(
        SqliteConnection connection,
        SqliteTransaction? transaction,
        string sql,
        Func<SqliteDataReader, T> map,
        params (string Name, object? Value)[] parameters) where T : class
    {
        var list = await QueryAsync(connection, transaction, sql, map, parameters);
        return list.Count > 0 ? list[0] : null;
    }

    /// <summary>
    /// Mengubah kata kunci menjadi pola LIKE yang aman. Karakter khusus % dan _ di-escape
    /// agar dicari sebagai huruf biasa (dipakai bersama klausa ESCAPE '\').
    /// </summary>
    protected static string? LikePattern(string? keyword)
    {
        if (string.IsNullOrWhiteSpace(keyword))
        {
            return null;
        }

        var escaped = keyword.Trim()
            .Replace("\\", "\\\\")
            .Replace("%", "\\%")
            .Replace("_", "\\_");
        return "%" + escaped + "%";
    }
}
