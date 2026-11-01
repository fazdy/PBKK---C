<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow">
			<div class="card-header py-3 d-flex justify-content-between align-items-center">
				<h6 class="m-0 font-weight-bold text-primary">KRS Saya &middot; <?php echo TA_AKTIF ?></h6>
				<span class="badge badge-<?php echo $total_sks > 0 ? 'primary' : 'secondary' ?> p-2">Total <?php echo $total_sks ?> / <?php echo MAX_SKS ?> SKS</span>
			</div>
			<div class="card-body">
				<table class="table table-bordered table-sm mb-0">
					<thead class="thead-light"><tr><th>Kode</th><th>Mata Kuliah</th><th>Kelas</th><th>SKS</th><th>Jadwal</th><th>Ruang</th><th>Dosen</th><th></th></tr></thead>
					<tbody>
					<?php foreach ($krs as $k): ?>
						<tr>
							<td><?php echo $k->kode ?></td><td><?php echo html_escape($k->matakuliah) ?></td><td><?php echo $k->nama_kelas ?></td><td><?php echo $k->sks ?></td>
							<td><?php echo jadwal($k) ?></td><td><?php echo html_escape($k->ruang) ?></td><td class="small"><?php echo html_escape($k->dosen) ?></td>
							<td>
								<?php echo form_open('mahasiswa/krs/batal', ['class' => 'd-inline']) ?>
									<input type="hidden" name="krs_id" value="<?php echo $k->id ?>">
									<button class="btn btn-outline-danger btn-sm text-nowrap" <?php echo $k->nilai_huruf ? 'disabled' : '' ?> onclick="return confirm('Batalkan kelas ini dari KRS?')"><i class="fas fa-times"></i> Batal</button>
								<?php echo form_close() ?>
							</td>
						</tr>
					<?php endforeach ?>
					<?php if ( ! $krs): ?><tr><td colspan="8" class="text-center text-muted py-3">KRS masih kosong. Pilih kelas di tabel bawah.</td></tr><?php endif ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
	<div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Kelas yang Dibuka</h6></div>
	<div class="card-body">
		<p class="small text-muted">Sistem menolak kelas jika kuota penuh, jadwal bentrok, mata kuliah sudah diambil di kelas lain, atau total SKS melebihi <?php echo MAX_SKS ?>.</p>
		<div class="table-responsive">
		<table class="table table-bordered table-hover table-sm">
			<thead class="thead-light"><tr><th>Mata Kuliah</th><th>Kelas</th><th>SKS</th><th>Sem</th><th>Jadwal</th><th>Dosen</th><th>Kuota</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($kelas as $k): $sudah = in_array($k->id, $diambil); $penuh = $k->peserta >= $k->kuota; ?>
				<tr class="<?php echo $sudah ? 'table-success' : '' ?>">
					<td><?php echo html_escape($k->kode.' - '.$k->matakuliah) ?></td>
					<td><?php echo $k->nama_kelas ?></td><td><?php echo $k->sks ?></td><td><?php echo $k->semester ?></td>
					<td><?php echo jadwal($k) ?></td><td class="small"><?php echo html_escape($k->dosen) ?></td>
					<td><span class="badge badge-<?php echo $penuh ? 'danger' : 'light' ?>"><?php echo $k->peserta.'/'.$k->kuota ?></span></td>
					<td>
						<?php if ($sudah): ?>
							<span class="text-success small"><i class="fas fa-check"></i> Diambil</span>
						<?php else: ?>
							<?php echo form_open('mahasiswa/krs/ambil', ['class' => 'd-inline']) ?>
								<input type="hidden" name="kelas_id" value="<?php echo $k->id ?>">
								<button class="btn btn-primary btn-sm btn-ambil text-nowrap" data-kelas="<?php echo $k->id ?>"><i class="fas fa-plus"></i> Ambil</button>
							<?php echo form_close() ?>
						<?php endif ?>
					</td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>
		</div>
	</div>
</div>
