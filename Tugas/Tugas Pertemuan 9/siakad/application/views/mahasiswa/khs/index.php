<div class="card shadow mb-4">
	<div class="card-body d-flex flex-wrap justify-content-between">
		<div>
			<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo html_escape($mhs->nama) ?></div>
			<div class="small text-muted"><?php echo $mhs->nrp ?> &middot; <?php echo html_escape($mhs->jenjang.' '.$mhs->prodi) ?> &middot; Angkatan <?php echo $mhs->angkatan ?></div>
		</div>
		<div class="text-right">
			<div class="small text-muted">IPK &middot; SKS lulus</div>
			<div class="h4 mb-0 font-weight-bold text-primary"><?php echo number_format($khs['ipk'], 2) ?> <small class="text-gray-600">&middot; <?php echo $khs['total_sks'] ?> SKS</small></div>
		</div>
	</div>
</div>

<?php foreach ($khs['semester'] as $ta => $s): ?>
<div class="card shadow mb-4">
	<div class="card-header py-3 d-flex justify-content-between">
		<h6 class="m-0 font-weight-bold text-primary"><?php echo $ta ?></h6>
		<span class="small">IP Semester: <strong><?php echo $s['ip'] === NULL ? '-' : number_format($s['ip'], 2) ?></strong> &middot; <?php echo $s['sks'] ?> SKS</span>
	</div>
	<div class="card-body">
		<table class="table table-bordered table-sm mb-0">
			<thead class="thead-light"><tr><th>Kode</th><th>Mata Kuliah</th><th>Kelas</th><th>SKS</th><th>Nilai Angka</th><th>Nilai Huruf</th><th>Bobot &times; SKS</th></tr></thead>
			<tbody>
			<?php foreach ($s['nilai'] as $n): $b = bobot_nilai($n->nilai_huruf); ?>
				<tr>
					<td><?php echo $n->kode ?></td><td><?php echo html_escape($n->matakuliah) ?></td><td><?php echo $n->nama_kelas ?></td><td><?php echo $n->sks ?></td>
					<td><?php echo $n->nilai_angka === NULL ? '-' : rtrim(rtrim($n->nilai_angka, '0'), '.') ?></td>
					<td><?php echo badge_nilai($n->nilai_huruf) ?></td>
					<td><?php echo $b === NULL ? '-' : number_format($b * $n->sks, 1) ?></td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>
	</div>
</div>
<?php endforeach ?>
<?php if ( ! $khs['semester']): ?>
	<div class="alert alert-info">Belum ada riwayat studi.</div>
<?php endif ?>
