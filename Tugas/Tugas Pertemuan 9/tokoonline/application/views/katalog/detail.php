<div class="container py-4">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb small">
			<li class="breadcrumb-item"><a href="<?php echo site_url() ?>">Beranda</a></li>
			<li class="breadcrumb-item"><a href="<?php echo site_url('kategori/'.$produk->kategori_slug) ?>"><?php echo html_escape($produk->kategori) ?></a></li>
			<li class="breadcrumb-item active"><?php echo html_escape($produk->nama) ?></li>
		</ol>
	</nav>

	<div class="row g-4 bg-white rounded-3 border p-3 mx-0">
		<div class="col-md-5">
			<img src="<?php echo gambar_produk($produk->gambar) ?>" class="img-fluid rounded-3 w-100" alt="<?php echo html_escape($produk->nama) ?>">
		</div>
		<div class="col-md-7">
			<span class="badge text-bg-light"><?php echo html_escape($produk->kategori) ?></span>
			<h1 class="h3 fw-bold mt-2"><?php echo html_escape($produk->nama) ?></h1>
			<div class="display-6 fw-bold text-brand mb-3"><?php echo rupiah($produk->harga) ?></div>
			<p class="text-secondary"><?php echo nl2br(html_escape($produk->deskripsi)) ?></p>
			<p class="mb-4">
				<?php if ($produk->stok > 0): ?>
					<i class="bi bi-check-circle-fill text-success"></i> Stok tersedia: <strong><?php echo $produk->stok ?></strong>
				<?php else: ?>
					<i class="bi bi-x-circle-fill text-danger"></i> <strong>Stok habis</strong>
				<?php endif ?>
			</p>
			<?php echo form_open('cart/tambah', ['class' => 'row g-2 align-items-center']) ?>
				<input type="hidden" name="produk_id" value="<?php echo $produk->id ?>">
				<div class="col-auto"><label for="qty" class="col-form-label">Jumlah</label></div>
				<div class="col-auto"><input type="number" id="qty" name="qty" value="1" min="1" max="<?php echo max(1, $produk->stok) ?>" class="form-control" style="width:90px" <?php echo $produk->stok < 1 ? 'disabled' : '' ?>></div>
				<div class="col-auto"><button class="btn btn-outline-brand" <?php echo $produk->stok < 1 ? 'disabled' : '' ?>><i class="bi bi-cart-plus"></i> Tambah ke Keranjang</button></div>
				<div class="col-auto"><button class="btn btn-brand" name="langsung_beli" value="1" <?php echo $produk->stok < 1 ? 'disabled' : '' ?>>Beli Sekarang</button></div>
			<?php echo form_close() ?>
			<hr>
			<div class="small text-muted"><i class="bi bi-truck"></i> Ongkir flat <?php echo rupiah(ONGKIR) ?> &middot; <i class="bi bi-cash-coin"></i> Bisa bayar di tempat (COD)</div>
		</div>
	</div>

	<?php if ($terkait): ?>
		<h2 class="h5 fw-bold mt-5 mb-3">Produk Terkait</h2>
		<div class="row g-3">
			<?php foreach ($terkait as $p): ?>
				<div class="col-6 col-md-3"><?php $this->load->view('katalog/_card', ['p' => $p]) ?></div>
			<?php endforeach ?>
		</div>
	<?php endif ?>
</div>
