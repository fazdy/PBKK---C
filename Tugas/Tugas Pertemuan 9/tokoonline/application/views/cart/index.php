<div class="container py-4">
	<h1 class="h4 fw-bold mb-3"><i class="bi bi-cart3"></i> Keranjang Belanja</h1>

	<?php if ( ! $items): ?>
		<div class="text-center py-5 bg-white rounded-3 border">
			<i class="bi bi-cart-x fs-1 text-muted"></i>
			<p class="mt-2">Keranjang Anda masih kosong.</p>
			<a href="<?php echo site_url('produk') ?>" class="btn btn-brand">Mulai Belanja</a>
		</div>
	<?php else: ?>
	<div class="row g-4">
		<div class="col-lg-8">
			<?php echo form_open('cart/ubah', ['id' => 'form-keranjang']) ?>
			<div class="bg-white rounded-3 border">
				<table class="table align-middle mb-0">
					<thead class="table-light"><tr><th>Produk</th><th class="text-end">Harga</th><th width="110">Jumlah</th><th class="text-end">Subtotal</th><th></th></tr></thead>
					<tbody>
					<?php foreach ($items as $i): ?>
						<tr>
							<td>
								<div class="d-flex align-items-center gap-2">
									<img src="<?php echo gambar_produk($i->gambar) ?>" class="img-thumb" alt="">
									<div><a href="<?php echo site_url('p/'.$i->slug) ?>" class="text-dark fw-semibold text-decoration-none"><?php echo html_escape($i->nama) ?></a>
									<div class="small text-muted">Stok <?php echo $i->stok ?></div></div>
								</div>
							</td>
							<td class="text-end text-nowrap"><?php echo rupiah($i->harga) ?></td>
							<td><input type="number" name="qty[<?php echo $i->id ?>]" value="<?php echo $i->qty ?>" min="0" max="<?php echo $i->stok ?>" class="form-control form-control-sm"></td>
							<td class="text-end fw-semibold text-nowrap"><?php echo rupiah($i->subtotal) ?></td>
							<td><button type="submit" form="hapus-<?php echo $i->id ?>" class="btn btn-sm btn-link text-danger" title="Hapus"><i class="bi bi-trash"></i></button></td>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			</div>
			<div class="d-flex justify-content-between mt-3">
				<a href="<?php echo site_url('produk') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Lanjut Belanja</a>
				<button type="submit" class="btn btn-outline-brand"><i class="bi bi-arrow-repeat"></i> Perbarui Keranjang</button>
			</div>
			<?php echo form_close() ?>

			<?php foreach ($items as $i): ?>
				<?php echo form_open('cart/hapus', ['id' => 'hapus-'.$i->id, 'class' => 'd-none']) ?><input type="hidden" name="produk_id" value="<?php echo $i->id ?>"><?php echo form_close() ?>
			<?php endforeach ?>
		</div>
		<div class="col-lg-4">
			<div class="bg-white rounded-3 border p-3">
				<h2 class="h6 fw-bold">Ringkasan Belanja</h2>
				<div class="d-flex justify-content-between small"><span>Subtotal (<?php echo array_sum(array_column($items, 'qty')) ?> barang)</span><span><?php echo rupiah($subtotal) ?></span></div>
				<div class="d-flex justify-content-between small"><span>Ongkos kirim</span><span><?php echo rupiah(ONGKIR) ?></span></div>
				<hr>
				<div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span class="text-brand"><?php echo rupiah($subtotal + ONGKIR) ?></span></div>
				<a href="<?php echo site_url('checkout') ?>" class="btn btn-brand w-100 mt-3">Checkout <i class="bi bi-arrow-right"></i></a>
				<?php if ( ! $user): ?><p class="small text-muted mt-2 mb-0">Anda akan diminta masuk sebelum checkout.</p><?php endif ?>
			</div>
		</div>
	</div>
	<?php endif ?>
</div>
