<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo html_escape($title) ?> | Admin <?php echo SITE_NAME ?></title>
	<link href="<?php echo base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/toko.css') ?>" rel="stylesheet">
	<style>
		.admin-sidebar { width: 240px; min-height: 100vh; background: #1f2937; }
		.admin-sidebar .nav-link { color: #cbd5e1; border-radius: .5rem; }
		.admin-sidebar .nav-link:hover { background: rgba(255,255,255,.06); color: #fff; }
		.admin-sidebar .nav-link.active { background: var(--brand); color: #fff; }
		.stat-icon { width: 48px; height: 48px; border-radius: .75rem; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
	</style>
</head>
<body class="bg-body-tertiary">
<div class="d-flex">
	<aside class="admin-sidebar p-3 flex-shrink-0">
		<a href="<?php echo site_url('admin') ?>" class="d-block text-white text-decoration-none fs-5 fw-bold mb-4"><i class="bi bi-bag-heart-fill text-brand"></i> <?php echo SITE_NAME ?> <small class="fs-6 fw-normal text-white-50">Admin</small></a>
		<?php $seg = $this->uri->segment(2) ?: 'dashboard'; ?>
		<ul class="nav nav-pills flex-column gap-1">
			<?php foreach ([
				'dashboard' => ['bi-speedometer2', 'Dashboard'],
				'pesanan'   => ['bi-receipt', 'Pesanan'],
				'produk'    => ['bi-box-seam', 'Produk'],
				'kategori'  => ['bi-tags', 'Kategori'],
				'pelanggan' => ['bi-people', 'Pelanggan'],
			] as $uri => $m): ?>
				<li class="nav-item">
					<a class="nav-link <?php echo $seg === $uri ? 'active' : '' ?>" href="<?php echo site_url('admin/'.$uri) ?>">
						<i class="bi <?php echo $m[0] ?> me-2"></i><?php echo $m[1] ?>
						<?php if ($uri === 'pesanan' && $pesanan_baru): ?><span class="badge rounded-pill text-bg-warning float-end"><?php echo $pesanan_baru ?></span><?php endif ?>
					</a>
				</li>
			<?php endforeach ?>
		</ul>
		<hr class="border-secondary">
		<a class="nav-link text-white-50 small mb-2 d-block" href="<?php echo site_url() ?>" target="_blank"><i class="bi bi-shop me-2"></i>Lihat Toko</a>
		<a class="nav-link text-white-50 small d-block" href="<?php echo site_url('keluar') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
	</aside>

	<div class="flex-grow-1">
		<header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
			<h1 class="h5 fw-bold mb-0"><?php echo html_escape($title) ?></h1>
			<span class="small text-muted"><i class="bi bi-person-circle"></i> <?php echo html_escape($user['nama']) ?></span>
		</header>
		<main class="p-4">
			<?php $alert = $this->session->flashdata('alert'); if ($alert): ?>
				<div class="alert alert-<?php echo $alert['type'] ?> alert-dismissible fade show" role="alert">
					<?php echo html_escape($alert['message']) ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
				</div>
			<?php endif ?>
