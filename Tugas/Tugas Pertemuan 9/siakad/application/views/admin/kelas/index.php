<div class="card shadow mb-4">
	<div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
		<form method="get" class="form-inline">
			<label class="mr-2 small font-weight-bold text-primary" for="ta">Tahun ajaran</label>
			<?php echo form_dropdown('ta', $daftar_ta, $ta, 'class="form-control form-control-sm" id="ta" onchange="this.form.submit()"') ?>
		</form>
		<a href="<?php echo site_url('admin/kelas/tambah') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buka Kelas</a>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-hover datatable" width="100%">
				<thead class="thead-light"><tr><th>Mata Kuliah</th><th>Kelas</th><th>Dosen</th><th>Jadwal</th><th>Ruang</th><th>Peserta</th><th width="150">Aksi</th></tr></thead>
				<tbody>
				<?php foreach ($kelas as $k): ?>
					<tr>
						<td><?php echo html_escape($k->kode.' - '.$k->matakuliah) ?> <small class="text-muted">(<?php echo $k->sks ?> SKS)</small></td>
						<td><?php echo $k->nama_kelas ?></td>
						<td class="small"><?php echo html_escape($k->dosen) ?></td>
						<td data-order="<?php echo array_search($k->hari, array_values(daftar_hari())).$k->jam_mulai ?>"><?php echo jadwal($k) ?></td>
						<td><?php echo html_escape($k->ruang) ?></td>
						<td><span class="badge badge-<?php echo $k->peserta >= $k->kuota ? 'danger' : 'success' ?>"><?php echo $k->peserta.'/'.$k->kuota ?></span></td>
						<td>
							<a href="<?php echo site_url('admin/kelas/peserta/'.$k->id) ?>" class="btn btn-info btn-sm" title="Peserta"><i class="fas fa-users"></i></a>
							<a href="<?php echo site_url('admin/kelas/edit/'.$k->id) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
							<a href="#" class="btn btn-danger btn-sm btn-hapus" data-url="<?php echo site_url('admin/kelas/hapus/'.$k->id) ?>" data-nama="<?php echo html_escape($k->matakuliah.' '.$k->nama_kelas) ?>"><i class="fas fa-trash"></i></a>
						</td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
