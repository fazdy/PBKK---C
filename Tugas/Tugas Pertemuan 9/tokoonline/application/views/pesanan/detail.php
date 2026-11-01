<div class="container py-4">
	<a href="<?php echo site_url('pesanan') ?>" class="small"><i class="bi bi-arrow-left"></i> Semua pesanan</a>
	<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-3 gap-2">
		<div>
			<h1 class="h4 fw-bold mb-0">Pesanan <?php echo $pesanan->kode ?></h1>
			<div class="small text-muted"><?php echo date('d F Y, H:i', strtotime($pesanan->created_at)) ?> WIB</div>
		</div>
		<div class="fs-5"><?php echo badge_status($pesanan->status) ?></div>
	</div>

	<?php if ($pesanan->status === 'menunggu' && $pesanan->metode_bayar === 'transfer'): ?>
		<div class="alert alert-warning">
			<i class="bi bi-info-circle"></i> Silakan transfer <strong><?php echo rupiah($pesanan->total) ?></strong> ke <strong>Bank Kampus 123-456-7890 a.n. KampusMart</strong>
			dengan berita <strong><?php echo $pesanan->kode ?></strong>. Pesanan diproses setelah pembayaran dikonfirmasi admin.
		</div>
	<?php endif ?>

	<div class="row g-4">
		<div class="col-lg-8"><?php $this->load->view('pesanan/_ringkasan') ?></div>
		<div class="col-lg-4">
			<div class="bg-white rounded-3 border p-3 mb-3">
				<h2 class="h6 fw-bold">Pengiriman</h2>
				<p class="small mb-1"><strong><?php echo html_escape($pesanan->nama_penerima) ?></strong> &middot; <?php echo html_escape($pesanan->telepon) ?></p>
				<p class="small mb-1"><?php echo nl2br(html_escape($pesanan->alamat)) ?></p>
				<?php if ($pesanan->catatan): ?><p class="small text-muted mb-0">Catatan: <?php echo html_escape($pesanan->catatan) ?></p><?php endif ?>
				<hr>
				<p class="small mb-0">Pembayaran: <strong><?php echo $pesanan->metode_bayar === 'cod' ? 'Bayar di Tempat (COD)' : 'Transfer Bank' ?></strong></p>
			</div>
			<?php if ($pesanan->status === 'menunggu'): ?>
				<?php echo form_open('pesanan/batal/'.$pesanan->kode, ['onsubmit' => "return confirm('Batalkan pesanan ini?')"]) ?>
					<button class="btn btn-outline-danger w-100"><i class="bi bi-x-circle"></i> Batalkan Pesanan</button>
				<?php echo form_close() ?>
			<?php endif ?>
		</div>
	</div>
</div>
