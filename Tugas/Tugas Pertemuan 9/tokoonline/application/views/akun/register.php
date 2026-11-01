<div class="container py-5">
	<div class="row justify-content-center">
		<div class="col-md-8 col-lg-6">
			<div class="bg-white rounded-3 border p-4 shadow-sm">
				<h1 class="h4 fw-bold mb-3">Daftar Akun Pelanggan</h1>
				<?php echo form_open('daftar') ?>
					<?php echo bs_input('nama', 'Nama lengkap') ?>
					<div class="row">
						<div class="col-md-7"><?php echo bs_input('email', 'Email', '', 'email') ?></div>
						<div class="col-md-5"><?php echo bs_input('telepon', 'Nomor telepon') ?></div>
					</div>
					<?php echo bs_input('alamat', 'Alamat pengiriman', '', 'textarea', 'rows="2"') ?>
					<div class="row">
						<div class="col-md-6"><?php echo bs_input('password', 'Password', '', 'password', 'autocomplete="new-password"') ?></div>
						<div class="col-md-6"><?php echo bs_input('konfirmasi', 'Ulangi password', '', 'password', 'autocomplete="new-password"') ?></div>
					</div>
					<button type="submit" class="btn btn-brand w-100">Daftar</button>
				<?php echo form_close() ?>
				<p class="small text-center mt-3 mb-0">Sudah punya akun? <a href="<?php echo site_url('masuk') ?>">Masuk</a></p>
			</div>
		</div>
	</div>
</div>
