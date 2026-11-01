<div class="card shadow mb-4">
	<div class="card-header py-3 d-flex justify-content-between align-items-center">
		<h6 class="m-0 font-weight-bold text-primary">Daftar Mahasiswa</h6>
		<a href="<?php echo site_url('admin/mahasiswa/tambah') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Mahasiswa</a>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-hover datatable" width="100%">
				<thead class="thead-light">
					<tr><th>NRP</th><th>Nama</th><th>Program Studi</th><th>Angkatan</th><th>Dosen Wali</th><th width="130">Aksi</th></tr>
				</thead>
				<tbody>
				<?php foreach ($mahasiswa as $m): ?>
					<tr>
						<td><?php echo $m->nrp ?></td>
						<td><?php echo html_escape($m->nama) ?><br><small class="text-muted"><?php echo html_escape($m->email) ?></small></td>
						<td><?php echo html_escape($m->prodi) ?></td>
						<td><?php echo $m->angkatan ?></td>
						<td><?php echo html_escape($m->dosen_wali ?: '-') ?></td>
						<td>
							<a href="<?php echo site_url('admin/mahasiswa/edit/'.$m->id) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
							<a href="#" class="btn btn-danger btn-sm btn-hapus" data-url="<?php echo site_url('admin/mahasiswa/hapus/'.$m->id) ?>" data-nama="<?php echo html_escape($m->nrp.' - '.$m->nama) ?>"><i class="fas fa-trash"></i></a>
						</td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
