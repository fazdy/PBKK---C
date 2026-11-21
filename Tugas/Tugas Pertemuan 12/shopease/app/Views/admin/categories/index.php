<?= $this->extend('layouts/management') ?>
<?= $this->section('subtitle') ?><p>Atur pengelompokan produk yang digunakan di katalog.</p><?= $this->endSection() ?>
<?= $this->section('actions') ?><a class="pm-btn primary" href="<?= site_url('admin/categories/create') ?>">+ Tambah kategori</a><?= $this->endSection() ?>
<?= $this->section('content') ?><section class="surface"><header class="section-top"><h2>Daftar kategori</h2><span class="tag neutral"><?= count($categories) ?> kategori</span></header>
<div class="table-wrap"><table><thead><tr><th>Nama kategori</th><th>Deskripsi</th><th>Produk</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($categories as $c): $id=(int) $c['id_category']; ?><tr><td><strong><?= esc($c['name']) ?></strong></td><td><?= esc($c['description']) ?></td><td><?= (int) $c['product_count'] ?></td><td><div class="row-actions"><a href="<?= site_url("admin/categories/edit/$id") ?>">Edit</a><form action="<?= site_url("admin/categories/delete/$id") ?>" method="post" data-confirm="Hapus kategori ini?"><?= csrf_field() ?><button class="text-danger">Hapus</button></form></div></td></tr><?php endforeach ?>
<?php if (!$categories): ?><tr><td colspan="4" class="empty-result">Belum ada kategori.</td></tr><?php endif ?></tbody></table></div></section><?= $this->endSection() ?>
