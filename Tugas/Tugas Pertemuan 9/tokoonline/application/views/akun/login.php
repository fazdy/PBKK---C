<div class="container py-5">
	<div class="row justify-content-center">
		<div class="col-md-6 col-lg-4">
			<div class="bg-white rounded-3 border p-4 shadow-sm">
				<div class="text-center mb-3">
					<i class="bi bi-bag-heart-fill fs-1 text-brand"></i>
					<h1 class="h4 fw-bold">Masuk ke <?php echo SITE_NAME ?></h1>
				</div>
				<?php if ($error): ?><div class="alert alert-danger small py-2"><i class="bi bi-exclamation-circle"></i> <?php echo html_escape($error) ?></div><?php endif ?>
				<?php echo form_open('masuk') ?>
					<?php echo bs_input('email', 'Email', '', 'email', 'autofocus') ?>
					<?php echo bs_input('password', 'Password', '', 'password') ?>
					<button type="submit" class="btn btn-brand w-100">Masuk</button>
				<?php echo form_close() ?>
				<p class="small text-center mt-3 mb-0">Belum punya akun? <a href="<?php echo site_url('daftar') ?>">Daftar di sini</a></p>
			</div>
		</div>
	</div>
</div>
