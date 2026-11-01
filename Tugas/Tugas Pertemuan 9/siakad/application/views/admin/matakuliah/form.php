<div class="card shadow mb-4">
	<div class="card-header py-3">
		<a href="<?php echo site_url('admin/matakuliah') ?>"><i class="fas fa-arrow-left"></i> Kembali</a>
	</div>
	<div class="card-body">
		<?php echo form_open() ?>
			<div class="row">
				<div class="col-md-3"><?php echo bs_input('kode', 'Kode', $mk ? $mk->kode : '', 'text', 'maxlength="10"') ?></div>
				<div class="col-md-9"><?php echo bs_input('nama', 'Nama Mata Kuliah', $mk ? $mk->nama : '') ?></div>
			</div>
			<div class="row">
				<div class="col-md-3"><?php echo bs_input('sks', 'SKS', $mk ? $mk->sks : '', 'number', 'min="1" max="6"') ?></div>
				<div class="col-md-3"><?php echo bs_input('semester', 'Semester', $mk ? $mk->semester : '', 'number', 'min="1" max="8"') ?></div>
			</div>
			<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
		<?php echo form_close() ?>
	</div>
</div>
