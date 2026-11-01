<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Login - SIAKAD</title>
	<link href="<?php echo base_url('assets/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
	<link href="<?php echo base_url('assets/css/sb-admin-2.min.css') ?>" rel="stylesheet">
	<style>.bg-login-side { background: linear-gradient(160deg, #4e73df 10%, #224abe 100%); }</style>
</head>

<body class="bg-gradient-primary">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-xl-10 col-lg-12 col-md-9">
				<div class="card o-hidden border-0 shadow-lg my-5">
					<div class="card-body p-0">
						<div class="row">
							<div class="col-lg-6 d-none d-lg-flex bg-login-side text-white flex-column justify-content-center p-5">
								<i class="fas fa-university fa-4x mb-4"></i>
								<h2 class="font-weight-bold">SIAKAD</h2>
								<p class="mb-4">Sistem Informasi Akademik untuk pengelolaan data mahasiswa, dosen, mata kuliah, KRS, dan nilai.</p>
								<ul class="small pl-3 mb-0">
									<li><strong>Admin</strong> mengelola master data dan jadwal kelas</li>
									<li><strong>Dosen</strong> mengisi nilai kelas yang diampu</li>
									<li><strong>Mahasiswa</strong> mengisi KRS dan melihat KHS</li>
								</ul>
							</div>
							<div class="col-lg-6">
								<div class="p-5">
									<div class="text-center">
										<h1 class="h4 text-gray-900 mb-1">Selamat Datang!</h1>
										<p class="small text-muted mb-4">Masuk memakai username, NIP, atau NRP</p>
									</div>

									<?php $alert = $this->session->flashdata('alert'); if ($alert): ?>
										<div class="alert alert-<?php echo $alert['type'] ?> small"><?php echo html_escape($alert['message']) ?></div>
									<?php endif ?>
									<?php if ($error): ?>
										<div class="alert alert-danger small"><i class="fas fa-exclamation-circle"></i> <?php echo html_escape($error) ?></div>
									<?php endif ?>

									<?php echo form_open('login', ['class' => 'user']) ?>
										<div class="form-group">
											<input type="text" name="username" class="form-control form-control-user <?php echo form_error('username') ? 'is-invalid' : '' ?>"
											 placeholder="Username / NIP / NRP" value="<?php echo html_escape(set_value('username')) ?>" autofocus>
											<div class="invalid-feedback pl-3"><?php echo form_error('username', ' ', ' ') ?></div>
										</div>
										<div class="form-group">
											<input type="password" name="password" class="form-control form-control-user <?php echo form_error('password') ? 'is-invalid' : '' ?>" placeholder="Password">
											<div class="invalid-feedback pl-3"><?php echo form_error('password', ' ', ' ') ?></div>
										</div>
										<button type="submit" class="btn btn-primary btn-user btn-block">
											<i class="fas fa-sign-in-alt"></i> Login
										</button>
									<?php echo form_close() ?>
									<hr>
									<p class="text-center small text-muted mb-0">Lupa password? Hubungi bagian akademik.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>

</html>
