<div class="row">
	<div class="col-md-4 mb-4">
		<div class="card border-left-success shadow h-100 py-2"><div class="card-body">
			<div class="text-xs font-weight-bold text-success text-uppercase mb-1">Kelas diampu (<?php echo TA_AKTIF ?>)</div>
			<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($kelas) ?> kelas</div>
		</div></div>
	</div>
	<div class="col-md-4 mb-4">
		<div class="card border-left-warning shadow h-100 py-2"><div class="card-body">
			<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Peserta belum dinilai</div>
			<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $belum_dinilai ?> mahasiswa</div>
		</div></div>
	</div>
	<div class="col-md-4 mb-4">
		<div class="card border-left-info shadow h-100 py-2"><div class="card-body">
			<div class="text-xs font-weight-bold text-info text-uppercase mb-1">Mahasiswa perwalian</div>
			<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $bimbingan ?> mahasiswa</div>
		</div></div>
	</div>
</div>

<div class="card shadow mb-4">
	<div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Jadwal Mengajar Semester Ini</h6></div>
	<div class="card-body">
		<table class="table table-bordered table-sm mb-0">
			<thead class="thead-light"><tr><th>Mata Kuliah</th><th>Kelas</th><th>Jadwal</th><th>Ruang</th><th>Peserta</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($kelas as $k): ?>
				<tr>
					<td><?php echo html_escape($k->kode.' - '.$k->matakuliah) ?></td>
					<td><?php echo $k->nama_kelas ?></td>
					<td><?php echo jadwal($k) ?></td>
					<td><?php echo html_escape($k->ruang) ?></td>
					<td><?php echo $k->peserta ?></td>
					<td><a href="<?php echo site_url('dosen/nilai/input/'.$k->id) ?>" class="btn btn-sm btn-primary"><i class="fas fa-pen"></i> Nilai</a></td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>
	</div>
</div>
