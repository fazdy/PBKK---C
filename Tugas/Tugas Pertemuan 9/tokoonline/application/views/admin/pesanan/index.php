<ul class="nav nav-pills mb-3 gap-1">
	<li class="nav-item"><a class="nav-link <?php echo $status ? '' : 'active' ?>" href="<?php echo site_url('admin/pesanan') ?>">Semua <span class="badge text-bg-light"><?php echo array_sum($per_status) ?></span></a></li>
	<?php foreach (status_pesanan() as $key => $s): ?>
		<li class="nav-item"><a class="nav-link <?php echo $status === $key ? 'active' : '' ?>" href="<?php echo site_url('admin/pesanan?status='.$key) ?>"><?php echo $s[0] ?> <span class="badge text-bg-light"><?php echo isset($per_status[$key]) ? $per_status[$key] : 0 ?></span></a></li>
	<?php endforeach ?>
</ul>
<div class="bg-white border rounded-3 p-3">
	<table class="table align-middle mb-0">
		<thead class="table-light"><tr><th>Kode</th><th>Pelanggan</th><th>Tanggal</th><th class="text-center">Barang</th><th class="text-end">Total</th><th>Bayar</th><th>Status</th><th></th></tr></thead>
		<tbody>
		<?php foreach ($pesanan as $p): ?>
			<tr>
				<td class="fw-semibold"><?php echo $p->kode ?></td>
				<td><?php echo html_escape($p->pelanggan) ?></td>
				<td class="small"><?php echo date('d/m/Y H:i', strtotime($p->created_at)) ?></td>
				<td class="text-center"><?php echo $p->jumlah_barang ?></td>
				<td class="text-end"><?php echo rupiah($p->total) ?></td>
				<td class="small"><?php echo $p->metode_bayar === 'cod' ? 'COD' : 'Transfer' ?></td>
				<td><?php echo badge_status($p->status) ?></td>
				<td><a href="<?php echo site_url('admin/pesanan/detail/'.$p->kode) ?>" class="btn btn-sm btn-outline-brand">Kelola</a></td>
			</tr>
		<?php endforeach ?>
		<?php if ( ! $pesanan): ?><tr><td colspan="8" class="text-center text-muted py-4">Tidak ada pesanan.</td></tr><?php endif ?>
		</tbody>
	</table>
</div>
