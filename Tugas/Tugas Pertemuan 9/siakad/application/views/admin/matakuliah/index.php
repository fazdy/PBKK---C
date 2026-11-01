<div class="card shadow mb-4">
	<div class="card-header py-3 d-flex justify-content-between align-items-center">
		<h6 class="m-0 font-weight-bold text-primary">Daftar Mata Kuliah</h6>
		<a href="<?php echo site_url('admin/matakuliah/tambah') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Mata Kuliah</a>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-hover datatable" width="100%">
				<thead class="thead-light"><tr><th>Kode</th><th>Nama Mata Kuliah</th><th>SKS</th><th>Semester</th><th width="130">Aksi</th></tr></thead>
				<tbody>
				<?php foreach ($matakuliah as $mk): ?>
					<tr>
						<td><?php echo $mk->kode ?></td>
						<td><?php echo html_escape($mk->nama) ?></td>
						<td><?php echo $mk->sks ?></td>
						<td><?php echo $mk->semester ?></td>
						<td>
							<a href="<?php echo site_url('admin/matakuliah/edit/'.$mk->id) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
							<a href="#" class="btn btn-danger btn-sm btn-hapus" data-url="<?php echo site_url('admin/matakuliah/hapus/'.$mk->id) ?>" data-nama="<?php echo html_escape($mk->kode.' - '.$mk->nama) ?>"><i class="fas fa-trash"></i></a>
						</td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
