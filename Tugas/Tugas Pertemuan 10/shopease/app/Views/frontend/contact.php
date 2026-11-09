<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<div class="container store-container">
    <section class="page-intro" aria-labelledby="page-title">
        <p class="eyebrow">Pusat bantuan</p>
        <h1 id="page-title">Kontak ShopEase</h1>
        <p>Informasi kontak contoh dan jawaban untuk pertanyaan seputar katalog.</p>
    </section>

    <div class="row g-4 store-section store-section-last">
        <section class="col-lg-5" aria-labelledby="contact-title">
            <div class="contact-card">
                <span class="icon-tile" data-tone="blue"><svg class="icon" aria-hidden="true"><use href="#i-mail"></use></svg></span>
                <h2 id="contact-title">Email toko</h2>
                <p class="contact-email"><?= esc($email) ?></p>
                <p>Alamat contoh untuk demonstrasi tampilan kontak ShopEase.</p>
                <a class="section-link" href="<?= esc(url_to('catalog'), 'attr') ?>">
                    Kembali ke katalog <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
                </a>
            </div>
        </section>

        <section class="col-lg-7" aria-labelledby="faq-title">
            <div class="faq-panel">
                <div class="section-heading">
                    <p class="eyebrow">FAQ</p>
                    <h2 id="faq-title">Pertanyaan umum</h2>
                </div>
                <details class="faq-item">
                    <summary>Bagaimana cara mencari produk?</summary>
                    <p>Buka Katalog, ketik nama produk, pilih kategori jika perlu, lalu tekan Cari.</p>
                </details>
                <details class="faq-item">
                    <summary>Apa arti harga yang tampil?</summary>
                    <p>Harga ditulis dalam rupiah dan berasal dari data ilustratif untuk praktikum.</p>
                </details>
                <details class="faq-item">
                    <summary>Apakah sudah bisa melakukan pemesanan?</summary>
                    <p>Demo pertemuan ini mencakup penelusuran katalog. Pemesanan dan pembayaran merupakan bagian dari pengembangan sistem ShopEase lengkap.</p>
                </details>
            </div>
        </section>
    </div>
</div>
<?= $this->endSection() ?>
