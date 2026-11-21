<div class="row row-cols-2 row-cols-lg-4 g-3 g-xl-4">
    <?php foreach ($products as $product): ?>
    <div class="col"><article class="card product-card h-100">
        <div class="product-art" data-tone="lavender"><img src="<?= esc(pm_cover($product['cover']), 'attr') ?>" width="420" height="320" loading="lazy" alt="<?= esc($product['name'], 'attr') ?>"></div>
        <div class="card-body product-info">
            <p class="product-category"><?= esc($product['category_name']) ?></p><h3><?= esc($product['name']) ?></h3>
            <p class="product-description"><?= esc($product['description']) ?></p>
            <p class="product-price"><?= esc(pm_rupiah($product['price'])) ?></p>
            <small><?= (int) $product['stock'] === 0 ? 'Stok habis' : 'Stok ' . (int) $product['stock'] . ' unit' ?></small>
        </div>
    </article></div>
    <?php endforeach ?>
</div>
