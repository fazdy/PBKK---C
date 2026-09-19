# Laporan Tugas Pertemuan 3 Pemrograman Berorientasi Objek / PBKK
## Pembuatan Aplikasi Kalkulator Desktop dengan C# dan .NET (Avalonia UI) di macOS

---

### 1. Pendahuluan
.NET adalah platform pengembang sumber terbuka yang gratis dan lintas platform untuk membangun berbagai jenis aplikasi: web, seluler, desktop, permainan, dan IoT. Meskipun aplikasi web dan seluler berkembang pesat, aplikasi desktop masih menjadi pilihan utama untuk tugas yang menuntut efisiensi dan performa.

**Alasan memilih aplikasi desktop:**
- **Interaksi lebih baik dengan PC pengguna**: Akses file system, printer, kamera, Bluetooth, dan perangkat USB lebih leluasa dan berkinerja tinggi.
- **Performa tinggi untuk perhitungan kompleks**: Diproses langsung di CPU dan RAM komputer pengguna tanpa latensi jaringan.
- **Logika kustom di sisi klien**: Lebih mudah dijalankan secara independen daripada di aplikasi web.
- **Multithreading lebih mudah dan efisien**: Aplikasi tetap responsif saat menjalankan proses berat di background thread.
- **Pembuatan UI relatif mudah**: Tersedia pendekatan visual maupun deklaratif (XAML).
- **Cepat untuk memulai**: Tanpa server, domain, hosting, atau konfigurasi browser.
- **Debugging sangat kuat**: Breakpoint, step into/over, watch, call stack, dan exception debugging terintegrasi.
- **Akses ke perangkat keras sangat mudah**.

Pada latihan ini dibuat sebuah **Kalkulator Desktop dengan C# dan .NET** sebagai studi kasus. Latihan mencakup pembuatan project, desain antarmuka, penanganan event, operator aritmatika, percabangan `switch`, validasi input, dan penanganan error dengan `try-catch`.

---

### 2. Tujuan
1. Membuat project aplikasi desktop dengan C# dan .NET.
2. Mendesain antarmuka dengan kontrol Label (`TextBlock`), `TextBox`, dan `Button`.
3. Menangani event `Click` menggunakan event handler.
4. Menerapkan variabel, method, operator aritmatika, dan percabangan `if`/`switch`.
5. Melakukan validasi input dan menangani error dengan `try-catch`.

---

### 3. Alat dan Bahan

| No | Kebutuhan | Keterangan |
|---|---|---|
| 1 | Perangkat | MacBook Pro 2020, prosesor Intel Core i5 (Intel(R) Core(TM) i5-8257U CPU @ 1.40GHz, x86_64) |
| 2 | Sistem Operasi | macOS Sequoia 15.7.9 |
| 3 | SDK | .NET SDK 8.0.425, installer macOS x64 |
| 4 | Editor | Visual Studio Code + ekstensi C# Dev Kit dan Avalonia for VSCode |
| 5 | Bahasa | C# |
| 6 | Framework UI | Avalonia UI |
| 7 | Template Project | `avalonia.app` (Avalonia .NET App) |

---

### 4. Penyesuaian Lingkungan Pengembangan
Modul praktikum asli memakai **Windows Forms App** dan Visual Studio di sistem operasi Windows. Namun, Windows Forms dan WPF adalah teknologi khusus Windows sehingga tidak dapat berjalan langsung di macOS. Selain itu, Visual Studio for Mac telah dihentikan oleh Microsoft pada 31 Agustus 2024.

Terdapat tiga pilihan untuk pengguna macOS:

| Pilihan | Keterangan | Kekurangan |
|---|---|---|
| **Windows di Boot Camp / VM + Visual Studio** | WinForms asli | Memerlukan lisensi/instalasi Windows, konsumsi penyimpanan besar, dan performa berat |
| **.NET MAUI** | Lintas platform resmi Microsoft, mendukung macOS | Memerlukan instalasi Xcode besar dan setup tooling lebih berat |
| **Avalonia UI (Dipilih)** | Lintas platform modern, berjalan langsung di macOS | Bukan WinForms, tetapi konsep komponen dan arsitekturnya sama |

**Avalonia UI** dipilih karena merupakan framework UI open source lintas platform untuk .NET yang menggambar sendiri kontrolnya (skia-based canvas), sehingga tampilan dan perilakunya konsisten di macOS, Linux, dan Windows. Avalonia versi 12 mendukung .NET 8 ke atas. Bahasa yang dipakai tetap C#, dan seluruh logika program identik dengan modul lab asli.

#### Padanan Konsep Windows Forms dengan Avalonia UI:

| Windows Forms | Avalonia UI | Keterangan |
|---|---|---|
| `Form1` | `MainWindow` | Jendela utama aplikasi |
| `Form1.Designer.cs` | `MainWindow.axaml` (XAML) | Desain UI dideklarasikan dalam format XAML |
| `Form1.cs` | `MainWindow.axaml.cs` | Kode C# untuk event handling dan logika bisnis |
| `Label` | `TextBlock` | Komponen untuk menampilkan teks label |
| `TextBox (.Text)` | `TextBox (.Text)` | Komponen input/output teks layar |
| `Button (.Text)` | `Button (.Content)` | Konten tombol (teks atau elemen grafis) |
| `EventArgs` | `RoutedEventArgs` | Parameter event klik tombol |
| `MessageBox.Show()` | Dialog Modal kustom (`ShowDialog`) | Avalonia tidak memiliki MessageBox bawaan, digantikan dialog async |

---

### 5. Alur Program
1. Program dimulai dan layar (`txtDisplay`) menampilkan `"0"`.
2. Pengguna menekan tombol angka, lalu angka muncul di layar kalkulator.
3. Pengguna memilih operator (`+`, `−`, `×`, `÷`).
4. Angka pertama disimpan ke variabel `firstNumber`, operator disimpan ke `operation`, dan layar dikosongkan.
5. Pengguna memasukkan angka kedua.
6. Pengguna menekan tombol `=`, lalu angka kedua disimpan ke `secondNumber`.
7. Program memproses operasi aritmatika melalui percabangan `switch`, lalu hasilnya disimpan di `result`.
8. Hasil perhitungan ditampilkan di layar.

#### Ilustrasi Contoh Kasus: `10 + 20 = 30`

| Tahap | Tampilan Layar | Keterangan |
|---|---|---|
| 1 | `0` | Program dimulai |
| 2 | `10` | Pengguna menekan tombol `1` lalu `0` |
| 3 | `(kosong)` | Pengguna memilih `+`, nilai `firstNumber = 10` tersimpan |
| 4 | `20` | Pengguna menekan tombol `2` lalu `0` |
| 5 | `30` | Pengguna menekan tombol `=`, hasil `10 + 20 = 30` ditampilkan |

---

### 6. Langkah Pengerjaan

#### Langkah 1: Menyiapkan Lingkungan di macOS
1. **Cek Arsitektur Prosesor**: Buka menu Apple → *About This Mac*. Pada perangkat MacBook Pro 2020 ini, prosesor yang digunakan adalah Intel Core i5 (`x86_64`).
2. **Pasang .NET SDK**: Menggunakan .NET SDK versi LTS (8.0.425). Periksa ketersediaan SDK di Terminal:
   ```bash
   dotnet --version
   ```
3. **Pasang Visual Studio Code & Ekstensi**:
   - Pasang *C# Dev Kit* untuk integrasi bahasa C#, IntelliSense, dan debugging.
   - Pasang *Avalonia for VSCode* untuk autocomplete XAML dan pratinjau.
4. **Pasang Template Avalonia**:
   ```bash
   dotnet new install Avalonia.Templates
   ```

#### Langkah 2: Membuat Project
Di Terminal, inisialisasi project desktop kosong menggunakan template `avalonia.app`:
```bash
dotnet new avalonia.app -o CalculatorApp
cd CalculatorApp
```

Struktur file utama dalam project:
```
CalculatorApp
├── CalculatorApp.csproj
├── Program.cs
├── App.axaml
├── App.axaml.cs
├── MainWindow.axaml       ← Desain UI (setara Form1.Designer.cs)
└── MainWindow.axaml.cs    ← Kode C# logika kalkulator (setara Form1.cs)
```

Pada file `CalculatorApp.csproj`, sesuaikan target framework dengan .NET SDK yang terpasang:
```xml
<TargetFramework>net8.0</TargetFramework>
```

#### Langkah 3: Mendesain Form UI (`MainWindow.axaml`)
Buka file `MainWindow.axaml` dan susun tata letak antarmuka kalkulator menggunakan XAML:
```xml
<Window xmlns="https://github.com/avaloniaui"
        xmlns:x="http://schemas.microsoft.com/winfx/2006/xaml"
        x:Class="CalculatorApp.MainWindow"
        Title="Calculator"
        Width="340"
        SizeToContent="Height"
        CanResize="False"
        WindowStartupLocation="CenterScreen">

    <!-- Gaya (style) tombol -->
    <Window.Styles>
        <Style Selector="Button">
            <Setter Property="Margin" Value="3"/>
            <Setter Property="Height" Value="56"/>
            <Setter Property="FontSize" Value="20"/>
            <Setter Property="HorizontalAlignment" Value="Stretch"/>
            <Setter Property="HorizontalContentAlignment" Value="Center"/>
            <Setter Property="VerticalContentAlignment" Value="Center"/>
        </Style>

        <!-- Tombol operator: oranye -->
        <Style Selector="Button.op">
            <Setter Property="Background" Value="#FF9500"/>
            <Setter Property="Foreground" Value="White"/>
        </Style>
        <Style Selector="Button.op:pointerover /template/ ContentPresenter#PART_ContentPresenter">
            <Setter Property="Background" Value="#FFAD33"/>
            <Setter Property="Foreground" Value="White"/>
        </Style>

        <!-- Tombol Clear: merah -->
        <Style Selector="Button.clear">
            <Setter Property="Background" Value="#FF3B30"/>
            <Setter Property="Foreground" Value="White"/>
        </Style>
        <Style Selector="Button.clear:pointerover /template/ ContentPresenter#PART_ContentPresenter">
            <Setter Property="Background" Value="#FF6B60"/>
            <Setter Property="Foreground" Value="White"/>
        </Style>

        <!-- Tombol Equals: hijau -->
        <Style Selector="Button.equals">
            <Setter Property="Background" Value="#34C759"/>
            <Setter Property="Foreground" Value="White"/>
        </Style>
        <Style Selector="Button.equals:pointerover /template/ ContentPresenter#PART_ContentPresenter">
            <Setter Property="Background" Value="#5AD67D"/>
            <Setter Property="Foreground" Value="White"/>
        </Style>
    </Window.Styles>

    <StackPanel Margin="16" Spacing="8">
        <TextBlock x:Name="lblTitle"
                   Text="Calculator"
                   FontSize="26"
                   FontWeight="Bold"
                   HorizontalAlignment="Center"/>

        <TextBox x:Name="txtDisplay"
                 Text="0"
                 IsReadOnly="True"
                 FontSize="30"
                 Height="64"
                 Margin="3"
                 TextAlignment="Right"
                 VerticalContentAlignment="Center"/>

        <UniformGrid Columns="4">
            <Button x:Name="btn7" Content="7" Click="NumberButton_Click"/>
            <Button x:Name="btn8" Content="8" Click="NumberButton_Click"/>
            <Button x:Name="btn9" Content="9" Click="NumberButton_Click"/>
            <Button x:Name="btnDivide" Content="÷" Classes="op" Click="OperatorButton_Click"/>

            <Button x:Name="btn4" Content="4" Click="NumberButton_Click"/>
            <Button x:Name="btn5" Content="5" Click="NumberButton_Click"/>
            <Button x:Name="btn6" Content="6" Click="NumberButton_Click"/>
            <Button x:Name="btnMultiply" Content="×" Classes="op" Click="OperatorButton_Click"/>

            <Button x:Name="btn1" Content="1" Click="NumberButton_Click"/>
            <Button x:Name="btn2" Content="2" Click="NumberButton_Click"/>
            <Button x:Name="btn3" Content="3" Click="NumberButton_Click"/>
            <Button x:Name="btnMinus" Content="−" Classes="op" Click="OperatorButton_Click"/>

            <Button x:Name="btn0" Content="0" Click="NumberButton_Click"/>
            <Button x:Name="btnDecimal" Content="." Click="btnDecimal_Click"/>
            <Button x:Name="btnClear" Content="C" Classes="clear" Click="btnClear_Click"/>
            <Button x:Name="btnPlus" Content="+" Classes="op" Click="OperatorButton_Click"/>
        </UniformGrid>

        <Button x:Name="btnEquals" Content="=" Classes="equals" Click="btnEquals_Click"/>
    </StackPanel>
</Window>
```

#### Komponen UI yang Dibuat:

| Kontrol | Name | Text / Content | Fungsi |
|---|---|---|---|
| `TextBlock` | `lblTitle` | Calculator | Menampilkan judul aplikasi |
| `TextBox` | `txtDisplay` | 0 | Layar output angka & hasil (ReadOnly, TextAlignment Right) |
| `Button` | `btn0` s.d. `btn9` | 0 – 9 | Input angka |
| `Button` | `btnDecimal` | . | Input tanda titik desimal |
| `Button` | `btnDivide` | ÷ | Operator pembagian |
| `Button` | `btnMultiply` | × | Operator perkalian |
| `Button` | `btnMinus` | − | Operator pengurangan |
| `Button` | `btnPlus` | + | Operator penjumlahan |
| `Button` | `btnClear` | C | Reset perhitungan dan layar |
| `Button` | `btnEquals` | = | Mengeksekusi kalkulasi hasil |

---

### 7. Kode Lengkap C# (`MainWindow.axaml.cs`)

```csharp
using System;
using System.Globalization;
using System.Threading.Tasks;
using Avalonia;
using Avalonia.Controls;
using Avalonia.Interactivity;
using Avalonia.Layout;
using Avalonia.Media;

namespace CalculatorApp;

public partial class MainWindow : Window
{
    // Variabel untuk perhitungan
    double firstNumber = 0;
    double secondNumber = 0;
    double result = 0;
    string operation = "";

    public MainWindow()
    {
        InitializeComponent();
    }

    // Event untuk semua tombol angka (0-9)
    private void NumberButton_Click(object? sender, RoutedEventArgs e)
    {
        if (sender is not Button button) return;
        string angka = button.Content?.ToString() ?? "";
        string display = txtDisplay.Text ?? "";

        if (display == "0")
            txtDisplay.Text = angka;
        else
            txtDisplay.Text = display + angka;
    }

    // Event untuk semua tombol operator (+, −, ×, ÷)
    private async void OperatorButton_Click(object? sender, RoutedEventArgs e)
    {
        if (sender is not Button button) return;

        if (!double.TryParse(txtDisplay.Text, NumberStyles.Float, CultureInfo.InvariantCulture, out double angka))
        {
            await ShowMessage("Masukkan angka terlebih dahulu.", "Peringatan");
            return;
        }

        firstNumber = angka;
        operation = button.Content?.ToString() ?? "";
        txtDisplay.Text = "";
    }

    // Event tombol "=" untuk menghitung hasil
    private async void btnEquals_Click(object? sender, RoutedEventArgs e)
    {
        if (operation == "") return;

        try
        {
            secondNumber = double.Parse(txtDisplay.Text ?? "", CultureInfo.InvariantCulture);

            switch (operation)
            {
                case "+":
                    result = firstNumber + secondNumber;
                    break;
                case "−":
                    result = firstNumber - secondNumber;
                    break;
                case "×":
                    result = firstNumber * secondNumber;
                    break;
                case "÷":
                    if (secondNumber == 0)
                        throw new DivideByZeroException("Tidak dapat membagi dengan nol.");
                    result = firstNumber / secondNumber;
                    break;
            }

            txtDisplay.Text = result.ToString(CultureInfo.InvariantCulture);
            operation = "";
        }
        catch (DivideByZeroException ex)
        {
            txtDisplay.Text = "0";
            await ShowMessage(ex.Message, "Error");
        }
        catch (FormatException)
        {
            await ShowMessage("Input tidak valid. Masukkan angka yang benar.", "Error");
        }
        catch (Exception ex)
        {
            await ShowMessage(ex.Message, "Error");
        }
    }

    // Event tombol Clear
    private void btnClear_Click(object? sender, RoutedEventArgs e)
    {
        firstNumber = 0;
        secondNumber = 0;
        result = 0;
        operation = "";
        txtDisplay.Text = "0";
    }

    // Event tombol Decimal
    private void btnDecimal_Click(object? sender, RoutedEventArgs e)
    {
        string display = txtDisplay.Text ?? "";
        if (display == "")
            txtDisplay.Text = "0.";
        else if (!display.Contains("."))
            txtDisplay.Text = display + ".";
    }

    // Pengganti MessageBox.Show() pada Windows Forms
    private async Task ShowMessage(string message, string title)
    {
        var okButton = new Button
        {
            Content = "OK",
            Width = 90,
            HorizontalAlignment = HorizontalAlignment.Center
        };

        var dialog = new Window
        {
            Title = title,
            Width = 320,
            SizeToContent = SizeToContent.Height,
            CanResize = false,
            WindowStartupLocation = WindowStartupLocation.CenterOwner,
            Content = new StackPanel
            {
                Margin = new Thickness(20),
                Spacing = 16,
                Children =
                {
                    new TextBlock
                    {
                        Text = message,
                        TextWrapping = TextWrapping.Wrap
                    },
                    okButton
                }
            }
        };

        okButton.Click += (_, _) => dialog.Close();
        await dialog.ShowDialog(this);
    }
}
```

---

### 8. Pengujian Aplikasi

Pengujian fungsionalitas dan penanganan error aplikasi kalkulator:

#### A. Skenario Operasi Utama

| No | Skenario | Input | Hasil yang Diharapkan | Hasil Pengujian | Status |
|---|---|---|---|---|:---:|
| 1 | Penjumlahan | `10 + 20 =` | `30` | Layar menampilkan `30` | ☑ Lolos |
| 2 | Pengurangan | `30 − 12 =` | `18` | Layar menampilkan `18` | ☑ Lolos |
| 3 | Perkalian | `6 × 7 =` | `42` | Layar menampilkan `42` | ☑ Lolos |
| 4 | Pembagian | `100 ÷ 4 =` | `25` | Layar menampilkan `25` | ☑ Lolos |
| 5 | Desimal | `2.5 × 4 =` | `10` | Layar menampilkan `10` | ☑ Lolos |
| 6 | Pembagian Nol | `10 ÷ 0 =` | Dialog error *"Tidak dapat membagi dengan nol."* | Muncul dialog modal error pembagian nol, layar kembali `0` | ☑ Lolos |
| 7 | Clear | Tekan tombol `C` | Layar kembali `"0"` dan semua nilai reset | Variabel dan layar tereset ke `0` | ☑ Lolos |

#### B. Skenario Kasus Uji Tepi (Edge Cases) & Validasi

| No | Skenario | Input | Hasil yang Diharapkan | Hasil Pengujian | Status |
|---|---|---|---|---|:---:|
| 8 | Tanpa angka kedua | `5 + =` | Dialog *"Input tidak valid. Masukkan angka yang benar."* | Muncul dialog error FormatException, aplikasi stabil | ☑ Lolos |
| 9 | Dua operator berurutan | `5 + +` | Dialog peringatan *"Masukkan angka terlebih dahulu."* | Muncul dialog peringatan, operasi pertama dipertahankan | ☑ Lolos |
| 10 | Titik desimal ganda | `2 . . 5` | Layar tetap `2.5` (titik kedua diabaikan) | Titik hanya muncul 1 kali (`2.5`) | ☑ Lolos |
| 11 | Angka nol di depan | `0 5` | Layar menampilkan `5` (tidak ada `05`) | Angka nol di depan digantikan angka 5 | ☑ Lolos |
| 12 | Hasil negatif | `3 − 8 =` | `-5` | Layar menampilkan `-5` | ☑ Lolos |

---

### 9. Pembahasan

#### a. Fungsi `object sender` pada Event Handler
`sender` adalah referensi objek sumber yang memicu terjadinya event (dalam kasus ini, tombol `Button` yang diklik). Melalui pola *pattern matching* C# `sender is not Button button`, kita dapat memvalidasi dan meng-cast objek `sender` menjadi tipe `Button` secara aman tanpa resiko `InvalidCastException`. Melalui variabel `button`, properti seperti `button.Content` dapat dibaca secara dinamis.

#### b. Penggunaan Satu `NumberButton_Click` untuk Semua Tombol Angka
Alih-alih membuat 10 fungsi terpisah untuk tombol 0 hingga 9, satu fungsi umum `NumberButton_Click` digunakan bersama dengan memanfaatkan `sender`. Pendekatan ini menerapkan prinsip **DRY (Don't Repeat Yourself)**, menyederhanakan pemeliharaan kode, dan mengurangi kompleksitas program.

#### c. Perbedaan `firstNumber`, `secondNumber`, dan `result`
- `firstNumber`: Menyimpan operand pertama yang diinput sebelum operator ditekan.
- `secondNumber`: Menyimpan operand kedua yang diinput setelah operator dan sebelum tombol `=` ditekan.
- `result`: Menyimpan nilai keluaran hasil kalkulasi matematika yang kemudian dikonversi menjadi teks untuk ditampilkan di layar.

#### d. Validasi Pembagian dengan Nol
Secara matematis, pembagian dengan nol tidak terdefinisi. Dalam C#, operasi tipe data `double` pada pembagian dengan nol tidak menghasilkan runtime exception melainkan nilai khusus IEEE `Infinity` atau `NaN`. Hal ini dapat membingungkan pengguna jika tampil di layar. Oleh karena itu, pemeriksaan eksplisit `if (secondNumber == 0)` dilakukan untuk melempar `DivideByZeroException` sehingga program dapat memberikan pesan error yang jelas dan ramah pengguna.

#### e. Peran `try-catch` dalam Menjaga Stabilitas Aplikasi
Blok `try-catch` bertindak sebagai pengaman runtime. Kesalahan seperti `FormatException` (misalnya saat pengguna menekan tombol `=` ketika layar masih kosong setelah memilih operator) atau `DivideByZeroException` ditangkap secara tertib. Alih-alih membuat aplikasi desktop crash mendadak (force close), program menampilkan kotak pesan dialog yang menjelaskan kesalahan kepada pengguna.

#### f. Format Angka Desimal (`CultureInfo.InvariantCulture`)
Pada komputer atau sistem operasi yang menggunakan locale Indonesia (`id-ID`), pemisah bilangan desimal adalah tanda koma (`,`), sedangkan titik (`.`) dianggap sebagai pemisah ribuan. Hal ini menyebabkan `double.Parse("2.5")` dapat salah diterjemahkan menjadi `25`. Dengan menggunakan `CultureInfo.InvariantCulture`, tanda titik (`.`) dijamin selalu dibaca sebagai pemisah desimal secara konsisten di semua konfigurasi perangkat dan bahasa OS.

#### g. Penyesuaian Paradigma dari Windows Forms ke Avalonia UI
Logika pemrograman C# (variabel, operasi aritmatika, percabangan switch, dan penanganan error) sepenuhnya identik dengan Windows Forms. Perbedaannya terletak pada arsitektur UI:
- Desain dibuat secara deklaratif menggunakan XAML berbasis vector styling.
- Label digantikan oleh `TextBlock`.
- Properti `Text` pada tombol digantikan oleh properti universal `Content`.
- Dialog pesan dibuat secara asynchronous (`async/await`) menggunakan `Window.ShowDialog` untuk mencegah antarmuka utama mengalami *freeze*.

#### h. Keterbatasan dan Peluang Pengembangan
1. **Operasi Berantai**: Kalkulator saat ini mengevaluasi satu pasang operand pada satu waktu. Fitur evaluasi ekspresi berantai (misal `2 + 3 × 4`) dapat dikembangkan lebih lanjut.
2. **Karakter Tambahan**: Penambahan tombol `±` (negasi), `%` (persen), dan `⌫` (backspace) akan meningkatkan fleksibilitas pengguna.
3. **Pemisahan Arsitektur (MVVM)**: Untuk skala yang lebih besar, aplikasi dapat di-refactor menggunakan pola MVVM (*Model-View-ViewModel*) yang didukung penuh oleh Avalonia UI.

---

### 10. Kesimpulan
Proyek pembuatan aplikasi desktop Kalkulator berbasis C# dan .NET telah berhasil diimplementasikan di lingkungan macOS menggunakan **Avalonia UI**. Konsep inti pemrograman berorientasi objek dan pemrograman visual desktop yang dipelajari meliputi:
1. Pembuatan project desktop berbasis .NET modern di sistem operasi non-Windows.
2. Desain antarmuka responsif dan deklaratif menggunakan XAML (`TextBlock`, `TextBox`, `Button`, dan layout `UniformGrid`).
3. Pengelolaan event handler terpadu dengan type-checking `sender is Button`.
4. Implementasi logika aritmatika, validasi angka (`double.TryParse`), dan percabangan `switch`.
5. Pengamanan kestabilan aplikasi terhadap kesalahan input dan pembagian nol menggunakan blok `try-catch`.

Meskipun Windows Forms bersifat Windows-exclusive, konsep perancangan aplikasi desktop yang dipelajari tetap dapat dipraktikkan secara menyeluruh di macOS berkat fleksibilitas ekosistem .NET dan framework Avalonia UI.
