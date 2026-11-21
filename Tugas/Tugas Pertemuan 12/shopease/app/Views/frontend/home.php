<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<div class="container store-container">
    <section class="store-hero" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="hero-kicker"><span></span> Sorotan koleksi</p>
            <h1 id="hero-title">Belanja online,<br><span>lebih mudah.</span></h1>
            <p class="hero-description">Temukan produk pilihan untuk bekerja, bersantai, dan melengkapi kebutuhan sehari-hari.</p>
            <div class="hero-actions">
                <a class="btn btn-gold" href="<?= esc(url_to('catalog'), 'attr') ?>">
                    Lihat katalog <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
                </a>
                <a class="hero-text-link" href="#kategori">Jelajahi kategori</a>
            </div>
        </div>
        <?php if ($featured): ?><figure class="hero-art">
            <div class="hero-ring" aria-hidden="true"></div>
            <img src="<?= esc(pm_cover($featured['cover']), 'attr') ?>" width="420" height="320" alt="">
            <figcaption>
                <span>Produk sorotan</span>
                <strong><?= esc($featured['name']) ?></strong>
            </figcaption>
        </figure><?php endif ?>
    </section>

    <section class="store-section" id="kategori" aria-labelledby="category-title">
        <div class="section-heading">
            <p class="eyebrow">Jelajahi toko</p>
            <h2 id="category-title">Kategori pilihan</h2>
        </div>
        <div class="row row-cols-2 row-cols-lg-4 g-3">
            <?php foreach ($categories as $item): ?>
                <div class="col">
                    <a class="category-card" href="<?= esc(url_to('catalog') . '?kategori=' . rawurlencode($item['slug']), 'attr') ?>">
                        <span class="icon-tile" data-tone="lavender">
                            <svg class="icon" aria-hidden="true"><use href="#i-shirt"></use></svg>
                        </span>
                        <span class="category-copy">
                            <strong><?= esc($item['name']) ?></strong>
                            <small><?= esc($item['description']) ?></small>
                        </span>
                        <svg class="icon category-arrow" aria-hidden="true"><use href="#i-arrow"></use></svg>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="store-section" aria-labelledby="new-title">
        <div class="section-heading heading-with-link">
            <div>
                <p class="eyebrow">Baru di katalog</p>
                <h2 id="new-title">Produk terbaru</h2>
            </div>
            <a class="section-link" href="<?= esc(url_to('catalog'), 'attr') ?>">
                Semua produk <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
            </a>
        </div>
        <?= view('frontend/partials/product_grid', ['products' => $newProducts, 'categories' => $categories]) ?>
    </section>

</div>
<?= $this->endSection() ?>
