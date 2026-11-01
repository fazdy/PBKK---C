<div class="container py-4">
	<h1 class="h4 fw-bold mb-3"><i class="bi bi-receipt"></i> Pesanan Saya</h1>
	<?php if ( ! $pesanan): ?>
		<div class="text-center py-5 bg-white rounded-3 border"><p class="mb-2">Belum ada pesanan.</p><a href="<?php echo site_url('produk') ?>" class="btn btn-brand">Belanja Sekarang</a></div>
	<?php else: ?>
		<div class="bg-white rounded-3 border">
			<table class="table align-middle mb-0">
				<thead class="table-light"><tr><th>Kode</th><th>Tanggal</th><th>Barang</th><th class="text-end">Total</th><th>Pembayaran</th><th>Status</th><th></th></tr></thead>
				<tbody>
				<?php foreach ($pesanan as $p): ?>
					<tr>
						<td class="fw-semibold"><?php echo $p->kode ?></td>
						<td class="small"><?php echo date('d/m/Y H:i', strtotime($p->created_at)) ?></td>
						<td><?php echo $p->jumlah_barang ?></td>
						<td class="text-end"><?php echo rupiah($p->total) ?></td>
						<td class="small"><?php echo $p->metode_bayar === 'cod' ? 'COD' : 'Transfer' ?></td>
						<td><?php echo badge_status($p->status) ?></td>
						<td><a href="<?php echo site_url('pesanan/detail/'.$p->kode) ?>" class="btn btn-sm btn-outline-brand">Detail</a></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		</div>
	<?php endif ?>
</div>
