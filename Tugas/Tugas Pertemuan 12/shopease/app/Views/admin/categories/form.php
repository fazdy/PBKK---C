<?php $editing = isset($category['id_category']); ?>
<form class="surface form-surface narrow" method="post" action="<?= site_url('admin/categories/' . ($editing ? 'update/' . $category['id_category'] : 'store')) ?>"><?= csrf_field() ?>
<label for="name">Nama kategori</label><input id="name" name="name" required minlength="3" maxlength="100" value="<?= old('name', $category['name'] ?? '', 'attr') ?>">
<label for="description">Deskripsi</label><textarea id="description" name="description" rows="4" maxlength="4000"><?= old('description', $category['description'] ?? '') ?></textarea>
<div class="form-end"><a class="pm-btn secondary" href="<?= site_url('admin/categories') ?>">Batal</a><button class="pm-btn primary">Simpan kategori</button></div></form>
