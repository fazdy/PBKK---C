# Tugas PBKK - Pertemuan 3: Aplikasi Kalkulator Desktop (.NET & Avalonia UI)

Repositori ini berisi implementasi **Aplikasi Desktop Kalkulator** menggunakan **C#** dan **Avalonia UI (.NET 8)** pada macOS (MacBook Pro 2020 Intel Core i5), sebagai penyesuaian dari modul Windows Forms.

## Struktur Direktori

```
.
├── CalculatorApp/             # Folder project aplikasi desktop Avalonia
│   ├── CalculatorApp.csproj   # Konfigurasi project .NET 8 & Avalonia 11.2.5
│   ├── Program.cs             # Titik masuk (entry point) aplikasi
│   ├── App.axaml              # Konfigurasi aplikasi & tema Fluent
│   ├── App.axaml.cs           # Logika inisialisasi aplikasi
│   ├── MainWindow.axaml       # Desain antarmuka XAML kalkulator
│   └── MainWindow.axaml.cs    # Logika C#, event handler, dan error handling
├── LAPORAN.md                 # Laporan praktikum lengkap (Bagian 1 s.d. 10)
└── README.md                  # Panduan ringkas repositori
```

## Cara Menjalankan Aplikasi

1. Buka Terminal dan masuk ke folder `CalculatorApp`:
   ```bash
   cd CalculatorApp
   ```
2. Jalankan aplikasi menggunakan .NET CLI:
   ```bash
   dotnet run
   ```

## Dokumen Laporan Lengkap
Silakan baca [LAPORAN.md](LAPORAN.md) untuk detail pendahuluan, perbandingan Windows Forms vs Avalonia UI, tata letak UI, alur program, kode lengkap, tabel hasil pengujian, pembahasan, dan kesimpulan.
