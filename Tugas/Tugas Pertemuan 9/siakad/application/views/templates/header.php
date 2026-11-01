<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo html_escape($title) ?> - SIAKAD</title>

	<link href="<?php echo base_url('assets/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
	<link href="<?php echo base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css') ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/sb-admin-2.min.css') ?>" rel="stylesheet">
	<style>
		.table td, .table th { vertical-align: middle; }
		.input-nilai { width: 90px; }
	</style>
</head>

<body id="page-top">
<?php
$role = $user['role'];
$seg1 = $this->uri->segment(1);
$seg2 = $this->uri->segment(2);
$aktif = function ($a, $b = NULL) use ($seg1, $seg2) {
	return ($seg1 === $a && ($b === NULL || $seg2 === $b)) ? 'active' : '';
};
$menu = [
	'admin' => [
		'Master Data' => [
			['admin/mahasiswa',  'fa-user-graduate',      'Mahasiswa'],
			['admin/dosen',      'fa-chalkboard-teacher', 'Dosen'],
			['admin/matakuliah', 'fa-book',               'Mata Kuliah'],
		],
		'Perkuliahan' => [
			['admin/kelas', 'fa-calendar-alt', 'Kelas & Jadwal'],
		],
	],
	'dosen' => [
		'Perkuliahan' => [
			['dosen/nilai', 'fa-clipboard-check', 'Input Nilai'],
		],
	],
	'mahasiswa' => [
		'Akademik' => [
			['mahasiswa/krs', 'fa-list-alt',      'Isi KRS'],
			['mahasiswa/khs', 'fa-graduation-cap', 'KHS & Transkrip'],
		],
	],
];
?>
	<!-- Page Wrapper -->
	<div id="wrapper">

		<!-- Sidebar -->
		<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

			<a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo site_url('dashboard') ?>">
				<div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-university"></i></div>
				<div class="sidebar-brand-text mx-3">SIAKAD</div>
			</a>

			<hr class="sidebar-divider my-0">

			<li class="nav-item <?php echo $aktif('dashboard') ?: ($seg1 === NULL ? 'active' : '') ?>">
				<a class="nav-link" href="<?php echo site_url('dashboard') ?>">
					<i class="fas fa-fw fa-tachometer-alt"></i>
					<span>Dashboard</span></a>
			</li>

			<?php foreach ($menu[$role] as $heading => $items): ?>
				<hr class="sidebar-divider">
				<div class="sidebar-heading"><?php echo $heading ?></div>
				<?php foreach ($items as $item): list($uri, $icon, $label) = $item; list($a, $b) = explode('/', $uri); ?>
					<li class="nav-item <?php echo $aktif($a, $b) ?>">
						<a class="nav-link" href="<?php echo site_url($uri) ?>">
							<i class="fas fa-fw <?php echo $icon ?>"></i>
							<span><?php echo $label ?></span></a>
					</li>
				<?php endforeach ?>
			<?php endforeach ?>

			<hr class="sidebar-divider">
			<div class="sidebar-heading">Akun</div>
			<li class="nav-item <?php echo $aktif('profil') ?>">
				<a class="nav-link" href="<?php echo site_url('profil') ?>">
					<i class="fas fa-fw fa-user-cog"></i>
					<span>Profil & Password</span></a>
			</li>

			<hr class="sidebar-divider d-none d-md-block">
			<div class="text-center d-none d-md-inline">
				<button class="rounded-circle border-0" id="sidebarToggle"></button>
			</div>
		</ul>
		<!-- End of Sidebar -->

		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">

				<!-- Topbar -->
				<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
					<button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
						<i class="fa fa-bars"></i>
					</button>

					<span class="d-none d-sm-inline small text-gray-600"><i class="far fa-calendar-alt"></i> Tahun ajaran aktif: <strong><?php echo TA_AKTIF ?></strong></span>

					<ul class="navbar-nav ml-auto">
						<li class="nav-item dropdown no-arrow">
							<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="mr-2 d-none d-lg-inline text-gray-600 small text-right">
									<?php echo html_escape($user['nama']) ?><br>
									<span class="badge badge-<?php echo ['admin' => 'danger', 'dosen' => 'success', 'mahasiswa' => 'info'][$role] ?>"><?php echo ucfirst($role) ?></span>
								</span>
								<i class="fas fa-user-circle fa-2x text-gray-400"></i>
							</a>
							<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
								<a class="dropdown-item" href="<?php echo site_url('profil') ?>">
									<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profil
								</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
									<i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
								</a>
							</div>
						</li>
					</ul>
				</nav>
				<!-- End of Topbar -->

				<div class="container-fluid">
					<h1 class="h3 mb-3 text-gray-800"><?php echo html_escape($title) ?></h1>

					<?php $alert = $this->session->flashdata('alert'); if ($alert): ?>
						<div class="alert alert-<?php echo $alert['type'] ?> alert-dismissible fade show" role="alert">
							<?php echo html_escape($alert['message']) ?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
						</div>
					<?php endif ?>
