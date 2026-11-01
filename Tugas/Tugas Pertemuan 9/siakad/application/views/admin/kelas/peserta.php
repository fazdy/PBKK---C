<div class="card shadow mb-4">
	<div class="card-header py-3">
		<a href="<?php echo site_url('admin/kelas?ta='.urlencode($kelas->tahun_ajaran)) ?>"><i class="fas fa-arrow-left"></i> Kembali</a>
		<span class="ml-3 font-weight-bold text-primary"><?php echo html_escape($kelas->kode.' '.$kelas->matakuliah.' - Kelas '.$kelas->nama_kelas) ?></span>
	</div>
	<div class="card-body">
		<p class="small mb-3">
			<i class="fas fa-chalkboard-teacher"></i> <?php echo html_escape($kelas->dosen) ?> &middot;
			<i class="far fa-clock"></i> <?php echo jadwal($kelas) ?> &middot;
			<i class="fas fa-door-open"></i> <?php echo html_escape($kelas->ruang) ?> &middot;
			<?php echo $kelas->tahun_ajaran ?> &middot; Peserta <?php echo $kelas->peserta.'/'.$kelas->kuota ?>
		</p>
		<table class="table table-bordered table-sm">
			<thead class="thead-light"><tr><th>No</th><th>NRP</th><th>Nama</th><th>Prodi</th><th>Nilai</th></tr></thead>
			<tbody>
			<?php foreach ($peserta as $i => $p): ?>
				<tr><td><?php echo $i + 1 ?></td><td><?php echo $p->nrp ?></td><td><?php echo html_escape($p->nama) ?></td><td><?php echo $p->prodi ?></td><td><?php echo badge_nilai($p->nilai_huruf) ?></td></tr>
			<?php endforeach ?>
			<?php if ( ! $peserta): ?><tr><td colspan="5" class="text-center text-muted">Belum ada peserta.</td></tr><?php endif ?>
			</tbody>
		</table>
	</div>
</div>
