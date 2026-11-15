<div class="row row-cols-2 row-cols-lg-4 g-3 g-xl-4">
    <?php foreach ($products as $product): ?>
        <div class="col">
            <article class="card product-card h-100">
                <div class="product-art" data-tone="<?= esc($product['tone'], 'attr') ?>">
                    <img src="<?= esc(base_url('assets/img/' . $product['image']), 'attr') ?>" width="420" height="320" loading="lazy" alt="">
                </div>
                <div class="card-body product-info">
                    <p class="product-category"><?= esc($categories[$product['category']]['name']) ?></p>
                    <h3><?= esc($product['name']) ?></h3>
                    <p class="product-description"><?= esc($product['description']) ?></p>
                    <p class="product-price">Rp<?= number_format($product['price'], 0, ',', '.') ?></p>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>
