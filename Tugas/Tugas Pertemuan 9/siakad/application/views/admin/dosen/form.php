<div class="card shadow mb-4">
	<div class="card-header py-3">
		<a href="<?php echo site_url('admin/dosen') ?>"><i class="fas fa-arrow-left"></i> Kembali</a>
	</div>
	<div class="card-body">
		<?php echo form_open() ?>
			<div class="row">
				<div class="col-md-5"><?php echo bs_input('nip', 'NIP', $dosen ? $dosen->nip : '', 'text', 'maxlength="18" placeholder="18 digit"', 'NIP dipakai sebagai username login.') ?></div>
				<div class="col-md-7"><?php echo bs_input('nama', 'Nama Lengkap & Gelar', $dosen ? $dosen->nama : '') ?></div>
			</div>
			<div class="row">
				<div class="col-md-7"><?php echo bs_input('email', 'Email', $dosen ? $dosen->email : '', 'email') ?></div>
				<div class="col-md-5"><?php echo bs_select('prodi_id', 'Program Studi', $prodi, $dosen ? $dosen->prodi_id : '') ?></div>
			</div>
			<?php if ( ! $dosen): ?>
				<div class="row"><div class="col-md-5"><?php echo bs_input('password', 'Password Awal', '', 'password', 'autocomplete="new-password"', 'Minimal 6 karakter.') ?></div></div>
			<?php endif ?>
			<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
		<?php echo form_close() ?>
	</div>
</div>
