<section class="hero py-5">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-md-7">
				<span class="badge rounded-pill text-bg-light text-brand mb-3"><i class="bi bi-mortarboard"></i> Siap semester baru</span>
				<h1 class="display-5 fw-bold">Perlengkapan kuliah,<br>diantar sampai asrama.</h1>
				<p class="lead text-secondary">Pulpen, binder, kalkulator ilmiah, hingga tas laptop. Pesan sekarang, bayar transfer atau di tempat.</p>
				<a href="<?php echo site_url('produk') ?>" class="btn btn-brand btn-lg me-2"><i class="bi bi-shop"></i> Mulai Belanja</a>
				<a href="#kategori" class="btn btn-outline-dark btn-lg">Lihat Kategori</a>
			</div>
			<div class="col-md-5 text-center d-none d-md-block">
				<div class="emoji">🎒✏️<br>📓📏</div>
			</div>
		</div>
	</div>
</section>

<section class="container py-5" id="kategori">
	<h2 class="h4 fw-bold mb-3">Kategori</h2>
	<div class="row g-3">
		<?php foreach ($kategori as $k): ?>
			<div class="col-6 col-md-3">
				<a href="<?php echo site_url('kategori/'.$k->slug) ?>" class="d-block kategori-tile rounded-3 p-3 text-decoration-none text-dark bg-white h-100">
					<i class="bi <?php echo $k->ikon ?> fs-2 text-brand"></i>
					<div class="fw-semibold mt-2"><?php echo html_escape($k->nama) ?></div>
					<div class="small text-muted"><?php echo $k->jumlah_produk ?> produk</div>
				</a>
			</div>
		<?php endforeach ?>
	</div>
</section>

<section class="container pb-4">
	<div class="d-flex justify-content-between align-items-end mb-3">
		<h2 class="h4 fw-bold mb-0">Produk Terbaru</h2>
		<a href="<?php echo site_url('produk') ?>" class="small">Lihat semua &rarr;</a>
	</div>
	<div class="row g-3">
		<?php foreach ($terbaru as $p): ?>
			<div class="col-6 col-md-4 col-lg-3"><?php $this->load->view('katalog/_card', ['p' => $p]) ?></div>
		<?php endforeach ?>
	</div>
</section>
