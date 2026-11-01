<div class="card shadow mb-4">
	<div class="card-header py-3">
		<a href="<?php echo site_url('admin/kelas') ?>"><i class="fas fa-arrow-left"></i> Kembali</a>
	</div>
	<div class="card-body">
		<?php if ($error): ?>
			<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo html_escape($error) ?></div>
		<?php endif ?>
		<?php echo form_open() ?>
			<div class="row">
				<div class="col-md-6"><?php echo bs_select('matakuliah_id', 'Mata Kuliah', $matakuliah, $kelas ? $kelas->matakuliah_id : '') ?></div>
				<div class="col-md-6"><?php echo bs_select('dosen_id', 'Dosen Pengampu', $dosen, $kelas ? $kelas->dosen_id : '') ?></div>
			</div>
			<div class="row">
				<div class="col-md-2"><?php echo bs_input('nama_kelas', 'Kelas', $kelas ? $kelas->nama_kelas : '', 'text', 'maxlength="5" placeholder="A"') ?></div>
				<div class="col-md-4"><?php echo bs_input('tahun_ajaran', 'Tahun Ajaran', $kelas ? $kelas->tahun_ajaran : TA_AKTIF) ?></div>
				<div class="col-md-3"><?php echo bs_input('ruang', 'Ruang', $kelas ? $kelas->ruang : '', 'text', 'placeholder="IF-101"') ?></div>
				<div class="col-md-3"><?php echo bs_input('kuota', 'Kuota', $kelas ? $kelas->kuota : 40, 'number', 'min="1" max="100"') ?></div>
			</div>
			<div class="row">
				<div class="col-md-4"><?php echo bs_select('hari', 'Hari', ['' => '-- Pilih hari --'] + daftar_hari(), $kelas ? $kelas->hari : '') ?></div>
				<div class="col-md-4"><?php echo bs_input('jam_mulai', 'Jam Mulai', $kelas ? substr($kelas->jam_mulai, 0, 5) : '', 'time') ?></div>
				<div class="col-md-4"><?php echo bs_input('jam_selesai', 'Jam Selesai', $kelas ? substr($kelas->jam_selesai, 0, 5) : '', 'time') ?></div>
			</div>
			<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
		<?php echo form_close() ?>
	</div>
</div>
