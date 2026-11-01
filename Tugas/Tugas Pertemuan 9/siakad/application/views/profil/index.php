<div class="row">
	<div class="col-lg-5 mb-4">
		<div class="card shadow h-100">
			<div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Informasi Akun</h6></div>
			<div class="card-body">
				<table class="table table-sm table-borderless mb-0">
					<tr><th width="140">Nama</th><td><?php echo html_escape($akun->nama) ?></td></tr>
					<tr><th>Username</th><td><?php echo html_escape($akun->username) ?></td></tr>
					<tr><th>Role</th><td><?php echo ucfirst($akun->role) ?></td></tr>
					<tr><th>Login terakhir</th><td><?php echo $akun->last_login ? date('d/m/Y H:i', strtotime($akun->last_login)) : '-' ?></td></tr>
					<tr><th>Terdaftar</th><td><?php echo date('d/m/Y', strtotime($akun->created_at)) ?></td></tr>
				</table>
			</div>
		</div>
	</div>
	<div class="col-lg-7 mb-4">
		<div class="card shadow h-100">
			<div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Ganti Password</h6></div>
			<div class="card-body">
				<?php echo form_open('profil') ?>
					<?php echo bs_input('password_lama', 'Password Lama', '', 'password') ?>
					<?php echo bs_input('password_baru', 'Password Baru', '', 'password', '', 'Minimal 6 karakter.') ?>
					<?php echo bs_input('konfirmasi', 'Ulangi Password Baru', '', 'password') ?>
					<button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Simpan Password</button>
				<?php echo form_close() ?>
			</div>
		</div>
	</div>
</div>
