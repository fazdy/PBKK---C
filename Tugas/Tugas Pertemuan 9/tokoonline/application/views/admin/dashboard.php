<div class="row g-3 mb-4">
	<?php foreach ([
		['Omzet (diproses s.d. selesai)', rupiah($omzet), 'bi-cash-stack', 'success'],
		['Pesanan menunggu', isset($per_status['menunggu']) ? $per_status['menunggu'] : 0, 'bi-hourglass-split', 'warning'],
		['Produk aktif', $produk, 'bi-box-seam', 'primary'],
		['Pelanggan', $pelanggan, 'bi-people', 'info'],
	] as $s): ?>
		<div class="col-sm-6 col-xl-3">
			<div class="bg-white border rounded-3 p-3 d-flex align-items-center gap-3 h-100">
				<div class="stat-icon bg-<?php echo $s[3] ?>-subtle text-<?php echo $s[3] ?>"><i class="bi <?php echo $s[2] ?>"></i></div>
				<div><div class="small text-muted"><?php echo $s[0] ?></div><div class="fs-5 fw-bold"><?php echo $s[1] ?></div></div>
			</div>
		</div>
	<?php endforeach ?>
</div>

<div class="row g-3 mb-4">
	<div class="col-lg-8">
		<div class="bg-white border rounded-3 p-3 h-100">
			<h2 class="h6 fw-bold">Penjualan 7 Hari Terakhir</h2>
			<canvas id="grafik" height="110"></canvas>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="bg-white border rounded-3 p-3 h-100">
			<h2 class="h6 fw-bold">Produk Terlaris</h2>
			<ol class="list-group list-group-numbered list-group-flush">
				<?php foreach ($terlaris as $t): ?>
					<li class="list-group-item d-flex justify-content-between align-items-start px-0">
						<div class="ms-2 me-auto small"><?php echo html_escape($t->nama_produk) ?><div class="text-muted"><?php echo rupiah($t->omzet) ?></div></div>
						<span class="badge text-bg-light"><?php echo $t->qty ?> terjual</span>
					</li>
				<?php endforeach ?>
			</ol>
		</div>
	</div>
</div>

<div class="row g-3">
	<div class="col-lg-8">
		<div class="bg-white border rounded-3 p-3 h-100">
			<div class="d-flex justify-content-between"><h2 class="h6 fw-bold">Pesanan Terbaru</h2><a href="<?php echo site_url('admin/pesanan') ?>" class="small">Semua &rarr;</a></div>
			<table class="table table-sm align-middle mb-0">
				<thead><tr><th>Kode</th><th>Pelanggan</th><th>Tanggal</th><th class="text-end">Total</th><th>Status</th></tr></thead>
				<tbody>
				<?php foreach ($terbaru as $p): ?>
					<tr>
						<td><a href="<?php echo site_url('admin/pesanan/detail/'.$p->kode) ?>"><?php echo $p->kode ?></a></td>
						<td><?php echo html_escape($p->pelanggan) ?></td>
						<td class="small"><?php echo date('d/m H:i', strtotime($p->created_at)) ?></td>
						<td class="text-end"><?php echo rupiah($p->total) ?></td>
						<td><?php echo badge_status($p->status) ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="bg-white border rounded-3 p-3 h-100">
			<h2 class="h6 fw-bold"><i class="bi bi-exclamation-triangle text-danger"></i> Stok Menipis (&le; 5)</h2>
			<?php foreach ($menipis as $m): ?>
				<div class="d-flex justify-content-between small border-bottom py-1">
					<a href="<?php echo site_url('admin/produk/edit/'.$m->id) ?>"><?php echo html_escape($m->nama) ?></a>
					<span class="badge text-bg-<?php echo $m->stok ? 'warning' : 'danger' ?>"><?php echo $m->stok ?: 'Habis' ?></span>
				</div>
			<?php endforeach ?>
			<?php if ( ! $menipis): ?><p class="small text-muted mb-0">Semua stok aman.</p><?php endif ?>
		</div>
	</div>
</div>

<?php ob_start() ?>
<script>
new Chart(document.getElementById('grafik'), {
	type: 'bar',
	data: {
		labels: <?php echo json_encode(array_keys($grafik)) ?>,
		datasets: [{ label: 'Penjualan', data: <?php echo json_encode(array_values($grafik)) ?>, backgroundColor: 'rgba(232,89,12,.75)' }]
	},
	options: { legend: { display: false }, scales: { yAxes: [{ ticks: { beginAtZero: true, callback: function (v) { return 'Rp ' + v.toLocaleString('id-ID'); } } }] } }
});
</script>
<?php $this->load->vars('script', ob_get_clean()) ?>
