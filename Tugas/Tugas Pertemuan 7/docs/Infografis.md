# Infografis Ruang Lingkup — SIBASA

> Versi visual satu halaman ada di [`infografis.html`](infografis.html). Buka di browser, lalu ambil tangkapan layar atau cetak ke PDF untuk dipasang di Blogspot.

---

## ♻️ SIBASA — Sistem Informasi Bank Sampah

**Aplikasi desktop untuk mencatat tabungan sampah warga: setor sampah, jadi saldo.**

| Identitas | Keterangan |
|---|---|
| Jenis produk | Aplikasi desktop (Windows dan macOS) |
| Teknologi | C#, .NET 10 LTS, Avalonia UI 11.3 (XAML), MVVM (CommunityToolkit.Mvvm), SQLite, LiveCharts2 |
| Pengguna | Admin (pengurus bank sampah) dan Petugas (teller) |
| Versi | 1.0 |

---

## Deskripsi singkat

SIBASA membantu pengurus bank sampah tingkat RT/RW mengganti buku tabungan kertas dengan aplikasi desktop modern. Setiap kali warga menyetor sampah terpilah, petugas cukup memilih nasabah dan jenis sampah, lalu memasukkan beratnya. Nilai rupiah dihitung otomatis dari harga per kg dan langsung masuk ke saldo nasabah. Pengurus melihat ringkasan sampah terkumpul, saldo, dan tren setoran di dashboard.

---

## Gambaran UI

```
┌──────────────┬───────────────────────────────────────────────────────────┐
│ ♻ SIBASA     │  Dashboard                                ☀/☾  Admin ▾     │
│              ├───────────────────────────────────────────────────────────┤
│ ▣ Dashboard  │ ┌─────────────┐┌─────────────┐┌─────────────┐┌───────────┐ │
│ 👥 Nasabah    │ │Nasabah aktif││Sampah bulan ││Total saldo  ││Transaksi  │ │
│ ♻ Jenis      │ │     20      ││ ini 245 kg  ││ Rp1.250.000 ││ hari ini 4│ │
│   Sampah     │ └─────────────┘└─────────────┘└─────────────┘└───────────┘ │
│ ⇄ Transaksi  │ ┌───────────────────────────────┐┌────────────────────────┐ │
│ 🕘 Riwayat    │ │ Grafik batang: kg per bulan   ││ Pie: komposisi kategori│ │
│ 👤 Pengguna   │ │  ▂ ▄ ▅ ▃ ▆ █                  ││  Plastik/Kertas/Logam  │ │
│ ⚙ Pengaturan │ └───────────────────────────────┘└────────────────────────┘ │
│              │ ┌───────────────────────────────────────────────────────────┐ │
│ ⎋ Keluar     │ │ Aktivitas terbaru: setoran, penarikan, perubahan data     │ │
└──────────────┴─┴───────────────────────────────────────────────────────────┘
```

Elemen UI: sidebar navy tetap, header dengan toggle tema terang/gelap, kartu statistik berwarna berbeda, grafik, tabel data (DataGrid) dengan pencarian dan filter, formulir terstruktur, dialog konfirmasi, dan notifikasi *toast*.

> Angka pada gambaran di atas hanya contoh tampilan. Di aplikasi, semua angka dihitung dari database.

---

## Tujuan produk

1. Menggantikan pencatatan manual (buku tabungan dan Excel) dengan satu aplikasi yang terpusat.
2. Menghitung nilai setoran secara otomatis dan akurat (berat × harga per kg).
3. Menjaga saldo nasabah selalu benar: bertambah saat setor dan tidak bisa minus saat tarik.
4. Menyajikan rekap sampah terkumpul per jenis dan per bulan untuk laporan pengurus.
5. Menerapkan kode yang terstruktur (MVVM) agar mudah diuji dan dikembangkan.

## Pernyataan produk

> Untuk **pengurus dan petugas bank sampah** yang masih mencatat tabungan sampah warga secara manual, **SIBASA** adalah aplikasi desktop yang menggabungkan pengelolaan nasabah, transaksi setoran dan penarikan, serta dashboard analitik. Berbeda dengan buku tabungan dan spreadsheet, SIBASA menghitung nilai setoran otomatis, menjaga konsistensi saldo dengan transaksi database, dan menampilkan statistik langsung dari data.

## Sasaran

| Sasaran | Indikator keberhasilan |
|---|---|
| Pencatatan transaksi cepat | Satu setoran tercatat dalam ≤ 30 detik, dengan nilai rupiah terhitung otomatis |
| Saldo akurat | Saldo nasabah selalu sama dengan total setoran dikurangi total penarikan. Penarikan melebihi saldo selalu ditolak |
| Data mudah dicari | Nasabah ditemukan lewat nama atau nomor rekening. Filter RT/RW dan status berfungsi |
| Dashboard dinamis | Kartu dan grafik berubah setelah transaksi baru, tanpa angka statis |
| Aman dan andal | Password di-hash, query berparameter, akses dibatasi per peran, dan error ditangani tanpa aplikasi tertutup |

## Manfaat

- **Untuk pengurus:** rekap kg per jenis dan per bulan siap dipakai untuk laporan dan penjualan ke pengepul.
- **Untuk petugas:** tidak perlu menghitung manual, sehingga lebih sedikit salah hitung dan antrean lebih cepat.
- **Untuk warga:** saldo tabungan sampah tercatat rapi dan bisa dicek kapan saja lewat petugas.
- **Untuk lingkungan:** pemilahan sampah lebih terdorong karena nilainya jelas dan tercatat.

## Batasan (di luar ruang lingkup rilis ini)

- Tidak ada portal web atau aplikasi mobile untuk nasabah. Nasabah tidak login ke aplikasi.
- Tidak ada integrasi pembayaran digital atau transfer bank. Penarikan dilakukan tunai.
- Tidak ada sinkronisasi antar-unit bank sampah. Satu aplikasi menyimpan satu database lokal (SQLite).
- Tidak ada ekspor PDF/Excel otomatis atau pengiriman laporan lewat email.
- Tidak ada pencatatan penjualan ke pengepul atau stok gudang.
- Harga sampah pada data contoh hanya ilustrasi, bukan harga pasar resmi.

## Daftar fitur utama

| No | Fitur | Keterangan singkat |
|---|---|---|
| 1 | Login dan hak akses | Peran Admin dan Petugas, password di-hash PBKDF2 |
| 2 | Dashboard | 4 kartu statistik, grafik kg per bulan, pie komposisi kategori, aktivitas terbaru |
| 3 | Data nasabah (CRUD) | Tambah, lihat, ubah, hapus/nonaktifkan; cari nama/no. rekening; filter RW dan status |
| 4 | Kategori dan jenis sampah | Kelola kategori, jenis, dan harga per kg (khusus Admin) |
| 5 | Transaksi setoran | Berat × harga dihitung otomatis, saldo bertambah dalam satu transaksi database |
| 6 | Transaksi penarikan | Saldo tidak boleh minus, dengan konfirmasi sebelum disimpan |
| 7 | Riwayat transaksi | Filter periode, jenis transaksi, dan nasabah; pembatalan transaksi oleh Admin |
| 8 | Kelola pengguna | Tambah petugas, ubah peran, nonaktifkan akun, reset password (khusus Admin) |
| 9 | Tema terang/gelap | Pilihan tema disimpan dan dipakai lagi saat aplikasi dibuka |
