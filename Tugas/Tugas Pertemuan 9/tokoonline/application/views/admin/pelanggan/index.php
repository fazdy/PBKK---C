<div class="bg-white border rounded-3 p-3">
	<table class="table align-middle mb-0">
		<thead class="table-light"><tr><th>Nama</th><th>Email</th><th>Telepon</th><th>Alamat</th><th class="text-center">Pesanan</th><th class="text-end">Total Belanja</th><th>Bergabung</th></tr></thead>
		<tbody>
		<?php foreach ($pelanggan as $p): ?>
			<tr>
				<td class="fw-semibold"><?php echo html_escape($p->nama) ?></td>
				<td class="small"><?php echo html_escape($p->email) ?></td>
				<td class="small"><?php echo html_escape($p->telepon) ?></td>
				<td class="small"><?php echo html_escape(character_limiter((string) $p->alamat, 40, '…')) ?></td>
				<td class="text-center"><?php echo $p->jumlah_pesanan ?></td>
				<td class="text-end"><?php echo rupiah($p->total_belanja) ?></td>
				<td class="small"><?php echo date('d/m/Y', strtotime($p->created_at)) ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
</div>
