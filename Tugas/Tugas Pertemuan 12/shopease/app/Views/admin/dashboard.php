<?= $this->extend('layouts/management') ?>
<?= $this->section('subtitle') ?><p>Jumlah produk, status penayangan, dan ketersediaan stok toko.</p><?= $this->endSection() ?>
<?= $this->section('actions') ?><a class="pm-btn primary" href="<?= site_url('admin/products/create') ?>">+ Tambah produk</a><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="metrics">
<?php foreach ([['Semua produk',$stats['total'],'box'],['Kategori',count($categories),'layers'],['Produk tersedia',$stats['active'],'bag'],['Tidak tersedia',$stats['inactive'],'stock']] as [$label,$number,$icon]): ?>
<div class="metric"><svg><use href="#pm-<?= $icon ?>"/></svg><span><?= esc($label) ?></span><strong><?= (int) $number ?></strong></div>
<?php endforeach ?></div>
<div class="overview-grid"><section class="surface"><header class="section-top"><div><h2>Perlu perhatian</h2><p>Produk dengan sisa stok paling sedikit.</p></div><a href="<?= site_url('admin/inventory') ?>">Inventori →</a></header>
<?php foreach ($attention as $p): ?><div class="line-item"><img class="thumb" src="<?= esc(pm_cover($p['cover']), 'attr') ?>" alt=""><div class="grow"><a class="product-name" href="<?= site_url('admin/products/' . $p['id_product']) ?>"><?= esc($p['name']) ?></a><small><?= esc($p['category_name']) ?></small></div><span class="tag <?= $p['stock'] == 0 ? 'empty' : 'low' ?>"><?= (int) $p['stock'] ?> unit</span></div><?php endforeach ?>
<?php if (!$attention): ?><p class="empty-result">Stok semua produk di atas ambang rendah.</p><?php endif ?></section>
<section class="surface"><header class="section-top"><div><h2>Produk per kategori</h2><p>Distribusi seluruh katalog.</p></div></header>
<?php foreach ($categories as $cat): ?><div class="bar-row"><div><span><?= esc($cat['name']) ?></span><strong><?= (int) $cat['product_count'] ?></strong></div><progress max="<?= max(1,(int) $stats['total']) ?>" value="<?= (int) $cat['product_count'] ?>"></progress></div><?php endforeach ?>
<?php if (!$categories): ?><p class="empty-result">Belum ada kategori.</p><?php endif ?></section></div>
<?= $this->endSection() ?>
