<a href="<?php echo site_url('admin/pesanan') ?>" class="small"><i class="bi bi-arrow-left"></i> Semua pesanan</a>
<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-3">
	<div><span class="h5 fw-bold"><?php echo $pesanan->kode ?></span> <span class="small text-muted ms-2"><?php echo date('d/m/Y H:i', strtotime($pesanan->created_at)) ?></span></div>
	<div class="fs-5"><?php echo badge_status($pesanan->status) ?></div>
</div>
<div class="row g-4">
	<div class="col-lg-8"><?php $this->load->view('pesanan/_ringkasan') ?></div>
	<div class="col-lg-4">
		<div class="bg-white border rounded-3 p-3 mb-3">
			<h2 class="h6 fw-bold">Ubah Status</h2>
			<?php echo form_open('admin/pesanan/detail/'.$pesanan->kode) ?>
				<?php $opsi = array_map(function ($s) { return $s[0]; }, status_pesanan()); ?>
				<?php echo form_dropdown('status', $opsi, $pesanan->status, 'class="form-select mb-2" id="status" '.($pesanan->status === 'dibatalkan' ? 'disabled' : '')) ?>
				<button class="btn btn-brand w-100" <?php echo $pesanan->status === 'dibatalkan' ? 'disabled' : '' ?>>Simpan Status</button>
				<div class="form-text">Memilih "Dibatalkan" akan mengembalikan stok produk.</div>
			<?php echo form_close() ?>
		</div>
		<div class="bg-white border rounded-3 p-3">
			<h2 class="h6 fw-bold">Pelanggan & Pengiriman</h2>
			<p class="small mb-1"><i class="bi bi-person"></i> <?php echo html_escape($pesanan->nama_akun) ?> (<?php echo html_escape($pesanan->email) ?>)</p>
			<p class="small mb-1"><i class="bi bi-geo-alt"></i> <strong><?php echo html_escape($pesanan->nama_penerima) ?></strong>, <?php echo html_escape($pesanan->telepon) ?><br><?php echo nl2br(html_escape($pesanan->alamat)) ?></p>
			<?php if ($pesanan->catatan): ?><p class="small text-muted mb-1">Catatan: <?php echo html_escape($pesanan->catatan) ?></p><?php endif ?>
			<p class="small mb-0"><i class="bi bi-wallet2"></i> <?php echo $pesanan->metode_bayar === 'cod' ? 'Bayar di Tempat (COD)' : 'Transfer Bank' ?></p>
		</div>
	</div>
</div>
