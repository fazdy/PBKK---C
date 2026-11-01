<div class="container py-4">
	<h1 class="h4 fw-bold mb-3"><i class="bi bi-bag-check"></i> Checkout</h1>
	<?php echo form_open('checkout') ?>
	<div class="row g-4">
		<div class="col-lg-7">
			<div class="bg-white rounded-3 border p-3 mb-3">
				<h2 class="h6 fw-bold mb-3"><span class="step bg-warning text-white me-2">1</span>Alamat Pengiriman</h2>
				<div class="row">
					<div class="col-md-7"><?php echo bs_input('nama_penerima', 'Nama penerima', $akun->nama) ?></div>
					<div class="col-md-5"><?php echo bs_input('telepon', 'Nomor telepon', $akun->telepon) ?></div>
				</div>
				<?php echo bs_input('alamat', 'Alamat lengkap', $akun->alamat, 'textarea', 'rows="3"') ?>
				<?php echo bs_input('catatan', 'Catatan untuk penjual (opsional)', '', 'text', 'placeholder="Contoh: titip di pos satpam"') ?>
			</div>
			<div class="bg-white rounded-3 border p-3">
				<h2 class="h6 fw-bold mb-3"><span class="step bg-warning text-white me-2">2</span>Metode Pembayaran</h2>
				<?php foreach (['transfer' => ['bi-bank', 'Transfer Bank', 'Transfer ke rekening KampusMart, pesanan diproses setelah dana diterima.'],
				                'cod' => ['bi-cash-coin', 'Bayar di Tempat (COD)', 'Bayar tunai ke kurir saat barang sampai.']] as $val => $m): ?>
					<label class="d-flex gap-3 border rounded-3 p-3 mb-2" for="bayar-<?php echo $val ?>">
						<input class="form-check-input mt-1" type="radio" name="metode_bayar" id="bayar-<?php echo $val ?>" value="<?php echo $val ?>" <?php echo set_radio('metode_bayar', $val, $val === 'transfer') ?>>
						<span><i class="bi <?php echo $m[0] ?> text-brand"></i> <strong><?php echo $m[1] ?></strong><br><span class="small text-muted"><?php echo $m[2] ?></span></span>
					</label>
				<?php endforeach ?>
				<?php echo form_error('metode_bayar', '<div class="text-danger small">', '</div>') ?>
			</div>
		</div>
		<div class="col-lg-5">
			<div class="bg-white rounded-3 border p-3">
				<h2 class="h6 fw-bold mb-3">Pesanan Anda</h2>
				<?php foreach ($items as $i): ?>
					<div class="d-flex align-items-center gap-2 mb-2">
						<img src="<?php echo gambar_produk($i->gambar) ?>" class="img-thumb" alt="">
						<div class="flex-grow-1 small"><?php echo html_escape($i->nama) ?><br><span class="text-muted"><?php echo $i->qty ?> &times; <?php echo rupiah($i->harga) ?></span></div>
						<div class="small fw-semibold"><?php echo rupiah($i->subtotal) ?></div>
					</div>
				<?php endforeach ?>
				<hr>
				<div class="d-flex justify-content-between small"><span>Subtotal</span><span><?php echo rupiah($subtotal) ?></span></div>
				<div class="d-flex justify-content-between small"><span>Ongkos kirim</span><span><?php echo rupiah(ONGKIR) ?></span></div>
				<div class="d-flex justify-content-between fw-bold fs-5 mt-2"><span>Total</span><span class="text-brand"><?php echo rupiah($subtotal + ONGKIR) ?></span></div>
				<button type="submit" class="btn btn-brand w-100 mt-3"><i class="bi bi-lock"></i> Buat Pesanan</button>
			</div>
		</div>
	</div>
	<?php echo form_close() ?>
</div>
