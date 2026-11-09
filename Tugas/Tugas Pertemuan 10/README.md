# Tugas Pertemuan 10 PBKK (C) — ShopEase

**Faizal Aldy Armiriawan · NRP 5025231266 · PBKK Kelas C**

ShopEase adalah landing page toko online berbasis CodeIgniter 4. Tugas ini menerapkan routing, controller, dan view berdasarkan PRD ShopEase dan bagian Routing, Controller, dan View pada Hands-On Lab.

- [Kode sumber ShopEase](shopease/)
- [Laporan Blogspot versi HTML](docs/Laporan-Blogspot-ShopEase.html)

## Hasil implementasi

| Halaman | Hasil |
|---|---|
| Beranda | Banner Headphone Aura X1, empat kategori, empat produk terbaru, empat produk terlaris, dan tautan ke katalog. |
| Katalog | Delapan produk contoh dengan pencarian nama atau deskripsi, filter kategori, jumlah hasil, dan tombol reset. |
| Tentang | Deskripsi ShopEase serta peran customer, administrator, dan operator toko dalam PRD. |
| Kontak | Alamat email contoh dan FAQ yang dapat dibuka langsung di halaman. |

Harga, koleksi terlaris, dan alamat kontak menggunakan data demonstrasi. Cakupan tugas adalah halaman publik dan penelusuran katalog. Akun, keranjang, pembayaran, dan pengiriman merupakan modul lanjutan dalam PRD.

## Menjalankan di MacBook Intel

Jalankan dari direktori repositori `PBKK---C`.

```bash
cd "Tugas/Tugas Pertemuan 10/shopease"
php -v
php spark serve
```

Buka [http://localhost:8080/](http://localhost:8080/) di Safari atau Chrome. Tekan **Control+C** di Terminal untuk menghentikan server.

Lingkungan pengembangan memakai MacBook Pro 2020 Intel Core i5 dan PHP **8.4.12**. Paket menyertakan **CodeIgniter 4.7.6** dan aset lokal **Bootstrap 5.3.3**. PHP 8.2 atau lebih baru dengan ekstensi `intl` dan `mbstring` sudah cukup untuk menjalankan demo. Produk berasal dari array PHP sehingga katalog dapat dibuka tanpa menyiapkan database atau Composer.

Jika port 8080 sudah dipakai, buat `.env` dari `env` apabila file tersebut belum ada, lalu atur `app.baseURL = 'http://localhost:8081/'`. Jalankan `php spark serve --port 8081`.

## Routing, controller, dan view

| URL | Method controller | View |
|---|---|---|
| `/` | `HomeController::index` | [home.php](shopease/app/Views/frontend/home.php) |
| `/catalog` | `HomeController::catalog` | [catalog.php](shopease/app/Views/frontend/catalog.php) |
| `/about` | `HomeController::about` | [about.php](shopease/app/Views/frontend/about.php) |
| `/contact` | `HomeController::contact` | [contact.php](shopease/app/Views/frontend/contact.php) |

Rute `GET` didefinisikan di [Routes.php](shopease/app/Config/Routes.php). Alias dipakai oleh `url_to()` untuk membuat tautan pada navigasi dan tombol.

```php
$routes->get('/', 'HomeController::index', ['as' => 'home']);
$routes->get('catalog', 'HomeController::catalog', ['as' => 'catalog']);
$routes->get('about', 'HomeController::about', ['as' => 'about']);
$routes->get('contact', 'HomeController::contact', ['as' => 'contact']);
```

[HomeController.php](shopease/app/Controllers/HomeController.php) menerima permintaan, mengolah data dari [DemoCatalog.php](shopease/app/Models/DemoCatalog.php), dan memanggil view. Sebagai contoh, method `contact()` memberikan judul, penanda halaman aktif, dan alamat email contoh kepada view.

```php
public function contact(): string
{
    return view('frontend/contact', [
        'title' => 'Kontak | ShopEase',
        'page' => 'contact',
        'email' => 'halo@shopease.example',
    ]);
}
```

Keempat view memperluas [layout store.php](shopease/app/Views/layouts/store.php) agar navigasi dan footer konsisten. Beranda dan katalog menggunakan ulang [product_grid.php](shopease/app/Views/frontend/partials/product_grid.php) untuk menampilkan kartu. [shopease.css](shopease/public/assets/css/shopease.css) mengatur banner dan tata letak responsif.

## Pengujian

Sintaks PHP diperiksa dengan `php -l` dan rute dengan `php spark routes`. Karena pembukaan port lokal dibatasi pada sesi pengujian, respons CodeIgniter diperiksa melalui PHP CGI 8.4.12. Pratinjau HTML diperiksa di Safari.

| Skenario | Hasil |
|---|---|
| Beranda, Katalog, Tentang, dan Kontak | HTTP 200 dengan menu aktif sesuai halaman. |
| `/catalog?kategori=elektronik` | Menampilkan Headphone Aura X1 dan Keyboard Nova 68. |
| `/catalog?q=lAmPu` | Menampilkan Lampu Meja Luma. |
| Kata kunci tanpa kecocokan | Menampilkan pesan hasil kosong dan tautan ke seluruh produk. |
| URL yang tidak terdaftar | HTTP 404. |
| Pratinjau responsif | Beranda diperiksa di desktop dan 390 piksel. Katalog diperiksa pada 390 piksel dan Kontak pada 320 piksel. |

## Laporan Blogspot

Unduh [Laporan-Blogspot-ShopEase.html](docs/Laporan-Blogspot-ShopEase.html) dan buka di VS Code. Gunakan judul **Tugas Pertemuan 10 PBKK (C) | Landing Page ShopEase dengan CodeIgniter 4** di Blogger, tempel sumber ke **Tampilan HTML**, lalu periksa melalui **Pratinjau**.
