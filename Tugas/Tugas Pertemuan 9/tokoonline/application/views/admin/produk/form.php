<a href="<?php echo site_url('admin/produk') ?>" class="small"><i class="bi bi-arrow-left"></i> Kembali</a>
<div class="bg-white border rounded-3 p-4 mt-2">
	<?php if ($upload_error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Upload gambar gagal: <?php echo html_escape(strip_tags($upload_error)) ?></div><?php endif ?>
	<?php echo form_open_multipart() ?>
	<div class="row g-4">
		<div class="col-lg-8">
			<?php echo bs_input('nama', 'Nama produk', $produk ? $produk->nama : '') ?>
			<div class="row">
				<div class="col-md-4">
					<div class="mb-3"><label class="form-label" for="kategori_id">Kategori</label>
					<?php echo form_dropdown('kategori_id', $kategori, set_value('kategori_id', $produk ? $produk->kategori_id : ''), 'class="form-select'.(form_error('kategori_id') ? ' is-invalid' : '').'" id="kategori_id"') ?>
					<?php echo form_error('kategori_id', '<div class="invalid-feedback">', '</div>') ?></div>
				</div>
				<div class="col-md-4"><?php echo bs_input('harga', 'Harga (Rp)', $produk ? $produk->harga : '', 'number', 'min="1"') ?></div>
				<div class="col-md-4"><?php echo bs_input('stok', 'Stok', $produk ? $produk->stok : '', 'number', 'min="0"') ?></div>
			</div>
			<?php echo bs_input('deskripsi', 'Deskripsi', $produk ? $produk->deskripsi : '', 'textarea', 'rows="4"') ?>
			<div class="form-check form-switch mb-3">
				<input class="form-check-input" type="checkbox" role="switch" id="aktif" name="aktif" value="1" <?php echo set_checkbox('aktif', '1', $produk ? (bool) $produk->aktif : TRUE) ?>>
				<label class="form-check-label" for="aktif">Tampilkan di toko</label>
			</div>
		</div>
		<div class="col-lg-4">
			<label class="form-label" for="gambar">Gambar produk</label>
			<img src="<?php echo gambar_produk($produk ? $produk->gambar : NULL) ?>" id="preview" class="img-fluid rounded-3 border mb-2 w-100" alt="Pratinjau">
			<input type="file" class="form-control" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp">
			<div class="form-text">JPG/PNG/WEBP, maks. 2 MB. Gambar lebih dari 800 px diperkecil otomatis.</div>
		</div>
	</div>
	<button type="submit" class="btn btn-brand"><i class="bi bi-save"></i> Simpan Produk</button>
	<?php echo form_close() ?>
</div>

<?php ob_start() ?>
<script>
// Pratinjau gambar sebelum diunggah
document.getElementById('gambar').addEventListener('change', function () {
	if (this.files[0]) document.getElementById('preview').src = URL.createObjectURL(this.files[0]);
});
</script>
<?php $this->load->vars('script', ob_get_clean()) ?>
