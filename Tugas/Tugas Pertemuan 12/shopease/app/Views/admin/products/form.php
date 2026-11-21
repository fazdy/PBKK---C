<?php $id = $product['id_product'] ?? null; ?>
<form method="post" enctype="multipart/form-data" action="<?= site_url('admin/products/' . ($id ? "update/$id" : 'store')) ?>"><?= csrf_field() ?>
<div class="form-grid"><section class="surface form-surface"><h2>Informasi produk</h2>
<label for="name">Nama produk</label><input id="name" name="name" required minlength="3" maxlength="150" value="<?= old('name', $product['name'] ?? '', 'attr') ?>">
<label for="category">Kategori</label><select id="category" name="id_category" required><option value="">Pilih kategori</option><?php foreach ($categories as $c): ?><option value="<?= (int) $c['id_category'] ?>" <?= (string) old('id_category', $product['id_category'] ?? '') === (string) $c['id_category'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php endforeach ?></select>
<label for="description">Deskripsi</label><textarea id="description" rows="3" name="description" maxlength="4000"><?= old('description', $product['description'] ?? '') ?></textarea>
<div class="twocol"><div><label for="price">Harga (Rp)</label><input id="price" type="number" name="price" min="0" max="9999999999.99" step=".01" required value="<?= old('price', $product['price'] ?? '', 'attr') ?>"></div><div><label for="stock">Stok (unit)</label><input id="stock" type="number" name="stock" min="0" max="2147483647" step="1" required value="<?= old('stock', $product['stock'] ?? 0, 'attr') ?>"></div></div>
<label for="status">Status produk</label><select id="status" name="status"><?php foreach (['tersedia'=>'Tersedia','tidak_tersedia'=>'Tidak tersedia'] as $value=>$label): ?><option value="<?= $value ?>" <?= old('status',$product['status'] ?? 'tersedia') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?></select>
</section><section class="surface form-surface"><h2>Gambar produk</h2><p>JPG, PNG atau WebP, maksimal 2 MiB per gambar. Satu produk dapat memiliki hingga 6 gambar.</p>
<label for="images">Pilih gambar</label><input id="images" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
<div id="image-preview" class="preview" aria-live="polite"></div><?php if ($id): ?><p class="file-hint"><?= count($images) ?> dari 6 gambar tersimpan.</p><?php endif ?>
</section></div><div class="form-end"><a class="pm-btn secondary" href="<?= site_url('admin/products') ?>">Batal</a><button class="pm-btn primary">Simpan produk</button></div></form>
<?php if ($id): ?><section class="surface form-surface saved-gallery"><h2>Galeri tersimpan</h2>
<?php if (!$images): ?><p>Produk ini belum memiliki gambar.</p><?php endif ?>
<div class="gallery"><?php foreach ($images as $im): $path = "admin/products/$id/images/" . $im['id_image']; ?><article><img src="<?= esc(pm_cover($im['image']), 'attr') ?>" alt="Gambar <?= (int) $im['id_image'] ?> dari <?= esc($product['name'], 'attr') ?>">
<div><?php if ($im['is_primary']): ?><span class="tag good">Gambar utama</span><?php else: ?><form method="post" action="<?= site_url($path . '/primary') ?>"><?= csrf_field() ?><button class="link-btn">Jadikan utama</button></form><?php endif ?>
<form method="post" action="<?= site_url($path . '/delete') ?>" data-confirm="Hapus gambar ini?"><?= csrf_field() ?><button class="link-btn text-danger">Hapus gambar</button></form></div></article><?php endforeach ?></div></section><?php endif ?>
