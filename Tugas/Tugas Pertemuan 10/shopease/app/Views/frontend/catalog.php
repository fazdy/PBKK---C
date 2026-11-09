<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<div class="container store-container">
    <section class="page-intro" aria-labelledby="page-title">
        <p class="eyebrow">Koleksi ShopEase</p>
        <h1 id="page-title">Katalog produk</h1>
        <p>Temukan produk yang kamu cari lewat kata kunci atau pilih kategori yang sesuai.</p>
    </section>

    <section class="catalog-results store-section-last" aria-labelledby="results-title">
        <form id="pencarian" class="catalog-form" method="get" action="<?= esc(url_to('catalog'), 'attr') ?>">
            <div>
                <label class="form-label" for="q">Cari produk</label>
                <input class="form-control" type="search" id="q" name="q" maxlength="80" value="<?= esc($query, 'attr') ?>" placeholder="Misalnya headphone atau lampu">
            </div>
            <div>
                <label class="form-label" for="kategori-produk">Kategori</label>
                <select class="form-select" id="kategori-produk" name="kategori">
                    <option value="">Semua kategori</option>
                    <?php foreach ($categories as $slug => $item): ?>
                        <option value="<?= esc($slug, 'attr') ?>"<?= $category === $slug ? ' selected' : '' ?>><?= esc($item['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-store" type="submit">
                <svg class="icon" aria-hidden="true"><use href="#i-search"></use></svg> Cari
            </button>
        </form>

        <div class="section-heading heading-with-link results-heading">
            <div>
                <h2 id="results-title"><?= $category === '' ? 'Daftar produk' : esc($categories[$category]['name']) ?></h2>
                <p><?= count($products) ?> produk<?= $query !== '' ? ' untuk “' . esc($query) . '”' : '' ?></p>
            </div>
            <?php if ($query !== '' || $category !== ''): ?>
                <a class="section-link" href="<?= esc(url_to('catalog'), 'attr') ?>">Reset pencarian</a>
            <?php endif; ?>
        </div>

        <?php if ($products === []): ?>
            <div class="empty-state">
                <span class="icon-tile" data-tone="blue"><svg class="icon" aria-hidden="true"><use href="#i-search"></use></svg></span>
                <h3>Produk belum ditemukan</h3>
                <p>Coba kata kunci yang lebih singkat atau pilih semua kategori.</p>
                <a class="btn btn-store" href="<?= esc(url_to('catalog'), 'attr') ?>">Lihat semua produk</a>
            </div>
        <?php else: ?>
            <?= $this->include('frontend/partials/product_grid') ?>
        <?php endif; ?>
    </section>
</div>
<?= $this->endSection() ?>
