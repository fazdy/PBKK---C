<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<div class="container store-container">
    <section class="page-intro page-intro-illustrated" aria-labelledby="page-title">
        <div>
            <p class="eyebrow">Cerita toko</p>
            <h1 id="page-title">Tentang ShopEase</h1>
            <p>ShopEase adalah konsep toko online yang menyatukan beragam produk dalam satu katalog yang mudah dijelajahi.</p>
            <a class="btn btn-store" href="<?= esc(url_to('catalog'), 'attr') ?>">
                Jelajahi katalog <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
            </a>
        </div>
        <div class="about-emblem" aria-hidden="true">
            <img src="<?= esc(base_url('assets/img/brand.svg'), 'attr') ?>" width="104" height="104" alt="">
        </div>
    </section>

    <section class="store-section" aria-labelledby="roles-title">
        <div class="section-heading">
            <p class="eyebrow">Peran dalam rancangan</p>
            <h2 id="roles-title">Siapa yang menggunakan ShopEase?</h2>
        </div>
        <div class="row row-cols-1 row-cols-md-3 g-3">
            <div class="col">
                <article class="info-card">
                    <span class="info-card-number">01</span>
                    <h3>Customer</h3>
                    <p>Menjelajahi katalog dan, pada sistem penuh, membuat serta memantau pesanan.</p>
                </article>
            </div>
            <div class="col">
                <article class="info-card">
                    <span class="info-card-number">02</span>
                    <h3>Administrator</h3>
                    <p>Mengelola kategori, produk, pesanan, dan verifikasi pembayaran dalam rancangan toko.</p>
                </article>
            </div>
            <div class="col">
                <article class="info-card">
                    <span class="info-card-number">03</span>
                    <h3>Operator Toko</h3>
                    <p>Menyiapkan pesanan dan memperbarui informasi pengiriman sesuai hak aksesnya.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="store-section store-section-last scope-panel" aria-labelledby="scope-title">
        <div>
            <p class="eyebrow">Lingkup demo</p>
            <h2 id="scope-title">Dari beranda ke katalog</h2>
            <p>Versi ini mencakup halaman publik dan penelusuran data contoh. Alur pada rancangan sistem berlanjut ke keranjang, checkout, pembayaran, dan pengiriman.</p>
        </div>
        <a class="section-link" href="<?= esc(url_to('contact'), 'attr') ?>">
            Informasi kontak <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
        </a>
    </section>
</div>
<?= $this->endSection() ?>
