<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo html_escape($title).' - '.SITE_NAME ?></title>
	<link href="<?php echo base_url('assets/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
	<link href="<?php echo base_url('css/sb-admin.min.css') ?>" rel="stylesheet">
	<style>
		.hero { background: linear-gradient(135deg, #1e7e34, #43a047); color: #fff; padding: 56px 0; }
		.card-img-top { height: 180px; object-fit: cover; }
	</style>
</head>
<body class="d-flex flex-column" style="min-height:100vh">
<nav class="navbar navbar-expand-md navbar-dark" style="background:#1e7e34">
	<div class="container">
		<a class="navbar-brand font-weight-bold" href="<?php echo site_url('beranda') ?>"><i class="fas fa-apple-alt"></i> <?php echo SITE_NAME ?></a>
		<ul class="navbar-nav ml-auto">
			<?php foreach (['beranda' => 'Beranda', 'about' => 'Tentang', 'contact' => 'Kontak'] as $uri => $label): ?>
				<li class="nav-item <?php echo uri_string() === $uri || ($uri === 'beranda' && uri_string() === 'page') ? 'active' : '' ?>"><a class="nav-link" href="<?php echo site_url($uri) ?>"><?php echo $label ?></a></li>
			<?php endforeach ?>
			<li class="nav-item"><a class="nav-link" href="<?php echo site_url('admin') ?>"><i class="fas fa-lock"></i> Admin</a></li>
		</ul>
	</div>
</nav>
