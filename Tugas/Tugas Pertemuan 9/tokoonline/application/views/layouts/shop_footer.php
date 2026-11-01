</main>

<footer class="bg-dark text-white-50 mt-5 pt-4 pb-3">
	<div class="container">
		<div class="row g-4">
			<div class="col-md-5">
				<div class="h5 text-white"><i class="bi bi-bag-heart-fill"></i> <?php echo SITE_NAME ?></div>
				<p class="small mb-0">Toko perlengkapan kuliah untuk mahasiswa: alat tulis, buku, tas, sampai gawai belajar. Ongkir flat <?php echo rupiah(ONGKIR) ?> ke area kampus.</p>
			</div>
			<div class="col-6 col-md-3">
				<div class="text-white fw-semibold mb-2">Belanja</div>
				<ul class="list-unstyled small">
					<?php foreach ($kategori_menu as $k): ?><li><a class="link-light link-opacity-50" href="<?php echo site_url('kategori/'.$k->slug) ?>"><?php echo html_escape($k->nama) ?></a></li><?php endforeach ?>
				</ul>
			</div>
			<div class="col-6 col-md-4">
				<div class="text-white fw-semibold mb-2">Pembayaran</div>
				<p class="small mb-0"><i class="bi bi-bank"></i> Transfer bank &middot; <i class="bi bi-cash-coin"></i> Bayar di tempat (COD)</p>
			</div>
		</div>
		<hr class="border-secondary">
		<div class="small d-flex justify-content-between flex-wrap">
			<span>&copy; <?php echo date('Y').' '.SITE_NAME ?></span>
			<span>Dibangun dengan CodeIgniter <?php echo CI_VERSION ?> &middot; {elapsed_time} detik</span>
		</div>
	</div>
</footer>

<script src="<?php echo base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
