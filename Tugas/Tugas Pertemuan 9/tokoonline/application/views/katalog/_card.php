<div class="card card-produk h-100 border-0 shadow-sm">
	<a href="<?php echo site_url('p/'.$p->slug) ?>" class="position-relative">
		<img src="<?php echo gambar_produk($p->gambar) ?>" class="card-img-top" alt="<?php echo html_escape($p->nama) ?>">
		<?php if ($p->stok < 1): ?><div class="habis"><span class="badge text-bg-dark fs-6">Stok habis</span></div><?php endif ?>
	</a>
	<div class="card-body d-flex flex-column">
		<?php if (isset($p->kategori)): ?><div class="small text-muted"><?php echo html_escape($p->kategori) ?></div><?php endif ?>
		<a href="<?php echo site_url('p/'.$p->slug) ?>" class="text-decoration-none text-dark fw-semibold stretched-link-none"><?php echo html_escape($p->nama) ?></a>
		<div class="fw-bold text-brand my-1"><?php echo rupiah($p->harga) ?></div>
		<div class="small text-muted mb-2"><?php echo $p->stok > 0 ? 'Stok '.$p->stok : '&nbsp;' ?></div>
		<?php echo form_open('cart/tambah', ['class' => 'mt-auto']) ?>
			<input type="hidden" name="produk_id" value="<?php echo $p->id ?>">
			<button class="btn btn-sm btn-outline-brand w-100" <?php echo $p->stok < 1 ? 'disabled' : '' ?> data-produk="<?php echo $p->id ?>"><i class="bi bi-cart-plus"></i> Keranjang</button>
		<?php echo form_close() ?>
	</div>
</div>
