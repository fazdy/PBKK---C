<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo html_escape($title) ?> | <?php echo SITE_NAME ?></title>
	<link href="<?php echo base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/toko.css') ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top shadow-sm">
	<div class="container">
		<a class="navbar-brand fw-bold text-brand" href="<?php echo site_url() ?>"><i class="bi bi-bag-heart-fill"></i> <?php echo SITE_NAME ?></a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navToko" aria-controls="navToko" aria-expanded="false" aria-label="Menu">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navToko">
			<ul class="navbar-nav me-3">
				<li class="nav-item"><a class="nav-link" href="<?php echo site_url('produk') ?>">Semua Produk</a></li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kategori</a>
					<ul class="dropdown-menu">
						<?php foreach ($kategori_menu as $k): ?>
							<li><a class="dropdown-item" href="<?php echo site_url('kategori/'.$k->slug) ?>"><i class="bi <?php echo $k->ikon ?> me-2"></i><?php echo html_escape($k->nama) ?></a></li>
						<?php endforeach ?>
					</ul>
				</li>
			</ul>
			<form class="d-flex flex-grow-1 me-lg-3 my-2 my-lg-0" action="<?php echo site_url('produk') ?>" method="get" role="search">
				<div class="input-group">
					<input class="form-control" type="search" name="q" placeholder="Cari pulpen, binder, flashdisk..." value="<?php echo html_escape($this->input->get('q')) ?>" aria-label="Cari">
					<button class="btn btn-brand" type="submit"><i class="bi bi-search"></i></button>
				</div>
			</form>
			<ul class="navbar-nav align-items-lg-center">
				<li class="nav-item">
					<a class="nav-link position-relative me-2" href="<?php echo site_url('keranjang') ?>" title="Keranjang">
						<i class="bi bi-cart3 fs-5"></i>
						<?php if ($jumlah_keranjang): ?>
							<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo $jumlah_keranjang ?></span>
						<?php endif ?>
					</a>
				</li>
				<?php if ($user): ?>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person-circle"></i> <?php echo html_escape($user['nama']) ?></a>
						<ul class="dropdown-menu dropdown-menu-end">
							<?php if ($user['role'] === 'admin'): ?><li><a class="dropdown-item" href="<?php echo site_url('admin') ?>"><i class="bi bi-speedometer2 me-2"></i>Panel Admin</a></li><?php endif ?>
							<li><a class="dropdown-item" href="<?php echo site_url('pesanan') ?>"><i class="bi bi-receipt me-2"></i>Pesanan Saya</a></li>
							<li><hr class="dropdown-divider"></li>
							<li><a class="dropdown-item" href="<?php echo site_url('keluar') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
						</ul>
					</li>
				<?php else: ?>
					<li class="nav-item"><a class="btn btn-outline-brand btn-sm me-2" href="<?php echo site_url('masuk') ?>">Masuk</a></li>
					<li class="nav-item"><a class="btn btn-brand btn-sm" href="<?php echo site_url('daftar') ?>">Daftar</a></li>
				<?php endif ?>
			</ul>
		</div>
	</div>
</nav>

<main class="flex-grow-1">
<?php $alert = $this->session->flashdata('alert'); if ($alert): ?>
	<div class="container mt-3">
		<div class="alert alert-<?php echo $alert['type'] ?> alert-dismissible fade show mb-0" role="alert">
			<?php echo html_escape($alert['message']) ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
		</div>
	</div>
<?php endif ?>
