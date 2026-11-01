<div class="container py-4">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb small">
			<li class="breadcrumb-item"><a href="<?php echo site_url() ?>">Beranda</a></li>
			<li class="breadcrumb-item <?php echo $kategori ? '' : 'active' ?>"><?php echo $kategori ? '<a href="'.site_url('produk').'">Produk</a>' : 'Produk' ?></li>
			<?php if ($kategori): ?><li class="breadcrumb-item active"><?php echo html_escape($kategori->nama) ?></li><?php endif ?>
		</ol>
	</nav>

	<div class="row g-4">
		<aside class="col-lg-3">
			<div class="list-group shadow-sm">
				<a href="<?php echo site_url('produk') ?>" class="list-group-item list-group-item-action <?php echo $kategori ? '' : 'active' ?>"><i class="bi bi-grid me-2"></i>Semua Produk</a>
				<?php foreach ($kategori_menu as $k): ?>
					<a href="<?php echo site_url('kategori/'.$k->slug) ?>" class="list-group-item list-group-item-action <?php echo $kategori && $kategori->id == $k->id ? 'active' : '' ?>"><i class="bi <?php echo $k->ikon ?> me-2"></i><?php echo html_escape($k->nama) ?></a>
				<?php endforeach ?>
			</div>
		</aside>

		<div class="col-lg-9">
			<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
				<div>
					<h1 class="h4 fw-bold mb-0"><?php echo html_escape($title) ?></h1>
					<div class="small text-muted">
						<?php echo $total ?> produk<?php if ($filter['q']): ?> untuk &ldquo;<strong><?php echo html_escape($filter['q']) ?></strong>&rdquo;
						<a href="<?php echo current_url() ?>" class="ms-1">hapus pencarian</a><?php endif ?>
					</div>
				</div>
				<form method="get" class="d-flex align-items-center gap-2">
					<?php if ($filter['q']): ?><input type="hidden" name="q" value="<?php echo html_escape($filter['q']) ?>"><?php endif ?>
					<label for="urut" class="small text-muted text-nowrap">Urutkan</label>
					<?php echo form_dropdown('urut', ['terbaru' => 'Terbaru', 'murah' => 'Harga terendah', 'mahal' => 'Harga tertinggi', 'nama' => 'Nama A-Z'], $filter['urut'], 'class="form-select form-select-sm" id="urut" onchange="this.form.submit()"') ?>
				</form>
			</div>

			<?php if ( ! $produk): ?>
				<div class="text-center py-5 bg-white rounded-3 border">
					<i class="bi bi-search fs-1 text-muted"></i>
					<p class="mt-2 mb-0">Produk tidak ditemukan. Coba kata kunci lain.</p>
				</div>
			<?php endif ?>

			<div class="row g-3">
				<?php foreach ($produk as $p): ?>
					<div class="col-6 col-md-4 col-xl-3"><?php $this->load->view('katalog/_card', ['p' => $p]) ?></div>
				<?php endforeach ?>
			</div>

			<nav class="mt-4" aria-label="Halaman"><?php echo $pagination ?></nav>
		</div>
	</div>
</div>
