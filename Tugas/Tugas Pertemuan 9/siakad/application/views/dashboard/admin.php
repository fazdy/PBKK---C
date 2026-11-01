<div class="row">
	<?php foreach ([
		['Mahasiswa', $jumlah_mahasiswa, 'fa-user-graduate', 'primary', 'admin/mahasiswa'],
		['Dosen', $jumlah_dosen, 'fa-chalkboard-teacher', 'success', 'admin/dosen'],
		['Mata Kuliah', $jumlah_mk, 'fa-book', 'info', 'admin/matakuliah'],
		['Kelas '.TA_AKTIF, $jumlah_kelas, 'fa-calendar-alt', 'warning', 'admin/kelas'],
	] as $c): ?>
	<div class="col-xl-3 col-md-6 mb-4">
		<a href="<?php echo site_url($c[4]) ?>" class="text-decoration-none">
		<div class="card border-left-<?php echo $c[3] ?> shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-<?php echo $c[3] ?> text-uppercase mb-1"><?php echo $c[0] ?></div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $c[1] ?></div>
					</div>
					<div class="col-auto"><i class="fas <?php echo $c[2] ?> fa-2x text-gray-300"></i></div>
				</div>
			</div>
		</div>
		</a>
	</div>
	<?php endforeach ?>
</div>

<div class="row">
	<div class="col-lg-5 mb-4">
		<div class="card shadow h-100">
			<div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Mahasiswa per Program Studi</h6></div>
			<div class="card-body"><canvas id="chartProdi" height="230"></canvas></div>
		</div>
	</div>
	<div class="col-lg-7 mb-4">
		<div class="card shadow h-100">
			<div class="card-header py-3 d-flex justify-content-between align-items-center">
				<h6 class="m-0 font-weight-bold text-primary">Keterisian Kelas <?php echo TA_AKTIF ?></h6>
				<a href="<?php echo site_url('admin/kelas') ?>" class="small">Kelola &rarr;</a>
			</div>
			<div class="card-body">
				<?php foreach ($kelas as $k): $persen = round($k->peserta / $k->kuota * 100); ?>
					<h4 class="small font-weight-bold"><?php echo html_escape($k->matakuliah.' '.$k->nama_kelas) ?>
						<span class="float-right"><?php echo $k->peserta.'/'.$k->kuota ?></span></h4>
					<div class="progress mb-3" style="height:.6rem">
						<div class="progress-bar bg-<?php echo $persen >= 100 ? 'danger' : ($persen >= 70 ? 'warning' : 'success') ?>" style="width: <?php echo max($persen, 2) ?>%"></div>
					</div>
				<?php endforeach ?>
			</div>
		</div>
	</div>
</div>

<?php ob_start() ?>
<script>
new Chart(document.getElementById('chartProdi'), {
	type: 'doughnut',
	data: {
		labels: <?php echo json_encode(array_column($per_prodi, 'nama')) ?>,
		datasets: [{ data: <?php echo json_encode(array_map('intval', array_column($per_prodi, 'total'))) ?>,
			backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'] }]
	},
	options: { maintainAspectRatio: false, legend: { position: 'bottom' }, cutoutPercentage: 65 }
});
</script>
<?php $this->load->vars('script', ob_get_clean()) ?>
