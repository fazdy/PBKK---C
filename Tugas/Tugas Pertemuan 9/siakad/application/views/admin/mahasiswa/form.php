<div class="card shadow mb-4">
	<div class="card-header py-3">
		<a href="<?php echo site_url('admin/mahasiswa') ?>"><i class="fas fa-arrow-left"></i> Kembali</a>
	</div>
	<div class="card-body">
		<?php echo form_open() ?>
			<div class="row">
				<div class="col-md-4"><?php echo bs_input('nrp', 'NRP', $mhs ? $mhs->nrp : '', 'text', 'maxlength="10" placeholder="10 digit"', $mhs ? '' : 'NRP juga menjadi username dan password awal.') ?></div>
				<div class="col-md-8"><?php echo bs_input('nama', 'Nama Lengkap', $mhs ? $mhs->nama : '') ?></div>
			</div>
			<div class="row">
				<div class="col-md-8"><?php echo bs_input('email', 'Email', $mhs ? $mhs->email : '', 'email') ?></div>
				<div class="col-md-4"><?php echo bs_input('angkatan', 'Angkatan', $mhs ? $mhs->angkatan : date('Y'), 'number') ?></div>
			</div>
			<div class="row">
				<div class="col-md-6"><?php echo bs_select('prodi_id', 'Program Studi', $prodi, $mhs ? $mhs->prodi_id : '') ?></div>
				<div class="col-md-6"><?php echo bs_select('dosen_wali_id', 'Dosen Wali', $dosen, $mhs ? $mhs->dosen_wali_id : '') ?></div>
			</div>
			<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
		<?php echo form_close() ?>
	</div>
</div>
