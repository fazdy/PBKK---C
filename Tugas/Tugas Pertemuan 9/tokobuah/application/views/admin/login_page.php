<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Login Admin - <?php echo SITE_NAME ?></title>
	<link href="<?php echo base_url('assets/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
	<link href="<?php echo base_url('css/sb-admin.min.css') ?>" rel="stylesheet">
	<style>
		body { background: linear-gradient(135deg, #e8f5e9, #ffffff); min-height: 100vh; }
		.login-box { max-width: 380px; }
	</style>
</head>

<body>
	<div class="container">
		<div class="login-box mx-auto pt-5">
			<div class="text-center mb-4 mt-4">
				<div class="display-4 text-success"><i class="fas fa-apple-alt"></i></div>
				<h1 class="h3 font-weight-bold">Login Admin</h1>
				<p class="lead text-muted">Silakan masuk ke Panel Admin <?php echo SITE_NAME ?></p>
			</div>

			<?php if ( ! empty($error)): ?>
				<div class="alert alert-danger small"><i class="fas fa-exclamation-circle"></i> <?php echo html_escape($error) ?></div>
			<?php endif ?>

			<?php echo form_open('admin/login') ?>
				<div class="form-group">
					<label for="email">Email</label>
					<input type="text" class="form-control <?php echo form_error('email') ? 'is-invalid' : '' ?>" id="email" name="email"
					 placeholder="Pakai username juga bisa.." value="<?php echo html_escape(set_value('email')) ?>" autofocus />
					<div class="invalid-feedback"><?php echo form_error('email', ' ', ' ') ?></div>
				</div>
				<div class="form-group">
					<label for="password">Password</label>
					<input type="password" class="form-control <?php echo form_error('password') ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Password.." />
					<div class="invalid-feedback"><?php echo form_error('password', ' ', ' ') ?></div>
				</div>
				<div class="form-group d-flex justify-content-between">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" id="remember" name="remember">
						<label class="custom-control-label" for="remember">Ingat Saya</label>
					</div>
					<a href="#" onclick="alert('Hubungi admin utama untuk reset password.'); return false;">Lupa Password?</a>
				</div>
				<button type="submit" class="btn btn-success btn-block">Login</button>
			<?php echo form_close() ?>

			<p class="text-center small mt-4"><a href="<?php echo site_url() ?>">&larr; Kembali ke website</a></p>
		</div>
	</div>
</body>

</html>
