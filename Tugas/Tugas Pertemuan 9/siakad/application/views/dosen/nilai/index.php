<div class="card shadow mb-4">
	<div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Pilih kelas untuk mengisi nilai</h6></div>
	<div class="card-body">
		<table class="table table-bordered table-hover">
			<thead class="thead-light"><tr><th>Tahun Ajaran</th><th>Mata Kuliah</th><th>Kelas</th><th>Jadwal</th><th>Peserta</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($kelas as $k): ?>
				<tr>
					<td><?php echo $k->tahun_ajaran ?> <?php if ($k->tahun_ajaran === TA_AKTIF): ?><span class="badge badge-success">aktif</span><?php endif ?></td>
					<td><?php echo html_escape($k->kode.' - '.$k->matakuliah) ?></td>
					<td><?php echo $k->nama_kelas ?></td>
					<td><?php echo jadwal($k) ?>, <?php echo html_escape($k->ruang) ?></td>
					<td><?php echo $k->peserta ?></td>
					<td><a href="<?php echo site_url('dosen/nilai/input/'.$k->id) ?>" class="btn btn-sm btn-primary"><i class="fas fa-pen"></i> Isi Nilai</a></td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>
	</div>
</div>
