<div class="card shadow mb-4">
	<div class="card-header py-3 d-flex justify-content-between align-items-center">
		<h6 class="m-0 font-weight-bold text-primary">Daftar Dosen</h6>
		<a href="<?php echo site_url('admin/dosen/tambah') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Dosen</a>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered table-hover datatable" width="100%">
				<thead class="thead-light">
					<tr><th>NIP</th><th>Nama</th><th>Program Studi</th><th>Kelas <?php echo TA_AKTIF ?></th><th>Login Terakhir</th><th width="130">Aksi</th></tr>
				</thead>
				<tbody>
				<?php foreach ($dosen as $d): ?>
					<tr>
						<td><?php echo $d->nip ?></td>
						<td><?php echo html_escape($d->nama) ?><br><small class="text-muted"><?php echo html_escape($d->email) ?></small></td>
						<td><?php echo html_escape($d->prodi) ?></td>
						<td><?php echo $d->jumlah_kelas ?></td>
						<td><?php echo $d->last_login ? date('d/m/Y H:i', strtotime($d->last_login)) : '-' ?></td>
						<td>
							<a href="<?php echo site_url('admin/dosen/edit/'.$d->id) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
							<a href="#" class="btn btn-danger btn-sm btn-hapus" data-url="<?php echo site_url('admin/dosen/hapus/'.$d->id) ?>" data-nama="<?php echo html_escape($d->nama) ?>"><i class="fas fa-trash"></i></a>
						</td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
