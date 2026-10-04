namespace StudentManager.Models;

/// <summary>
/// Model: representasi satu baris data pada tabel Students.
/// </summary>
public class Student
{
    public int Id { get; set; }
    public string NIM { get; set; } = "";
    public string Nama { get; set; } = "";
    public string Jurusan { get; set; } = "";
    public string Gender { get; set; } = "";
    public string Email { get; set; } = "";

    /// <summary>
    /// Membuat salinan objek. Form mengedit salinan ini sehingga data di
    /// DataGrid tidak ikut berubah sebelum tombol Simpan/Update ditekan.
    /// </summary>
    public Student Clone() => (Student)MemberwiseClone();
}
