<?php
$p = isset($product) ? $product : NULL;
$field = function ($name) use ($p) {
	return set_value($name, $p ? $p->$name : '');
};
?>
<?php if ( ! empty($upload_error)): ?>
	<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Upload foto gagal: <?php echo html_escape(strip_tags($upload_error)) ?></div>
<?php endif ?>

<div class="form-group">
	<label for="name">Nama*</label>
	<input class="form-control <?php echo form_error('name') ? 'is-invalid' : '' ?>"
	 type="text" name="name" id="name" placeholder="Nama produk" value="<?php echo html_escape($field('name')) ?>" />
	<div class="invalid-feedback"><?php echo form_error('name', ' ', ' ') ?></div>
</div>

<div class="form-group">
	<label for="price">Harga*</label>
	<div class="input-group">
		<div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
		<input class="form-control <?php echo form_error('price') ? 'is-invalid' : '' ?>"
		 type="number" name="price" id="price" min="0" placeholder="Harga produk" value="<?php echo html_escape($field('price')) ?>" />
		<div class="invalid-feedback"><?php echo form_error('price', ' ', ' ') ?></div>
	</div>
</div>

<div class="form-group">
	<label for="image">Foto</label>
	<?php if ($p): ?>
		<div class="mb-2"><img src="<?php echo base_url('upload/product/'.$p->image) ?>" class="img-product" alt="Foto saat ini"> <small class="text-muted ml-2">Foto saat ini. Kosongkan jika tidak ingin mengganti.</small></div>
		<input type="hidden" name="old_image" value="<?php echo html_escape($p->image) ?>" />
	<?php endif ?>
	<input class="form-control-file" type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.gif" />
	<small class="form-text text-muted">Format JPG, PNG, atau GIF. Maksimal 1 MB.</small>
</div>

<div class="form-group">
	<label for="description">Deskripsi*</label>
	<textarea class="form-control <?php echo form_error('description') ? 'is-invalid' : '' ?>"
	 name="description" id="description" rows="4" placeholder="Deskripsi produk..."><?php echo html_escape($field('description')) ?></textarea>
	<div class="invalid-feedback"><?php echo form_error('description', ' ', ' ') ?></div>
</div>
