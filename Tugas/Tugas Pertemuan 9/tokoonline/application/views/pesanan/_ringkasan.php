<?php /* Dipakai di halaman detail pesanan pelanggan dan admin */ ?>
<div class="bg-white rounded-3 border mb-3">
	<table class="table align-middle mb-0">
		<thead class="table-light"><tr><th>Produk</th><th class="text-end">Harga</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
		<tbody>
		<?php foreach ($pesanan->items as $i): ?>
			<tr>
				<td><div class="d-flex align-items-center gap-2"><img src="<?php echo gambar_produk($i->gambar) ?>" class="img-thumb" alt=""><?php echo html_escape($i->nama_produk) ?></div></td>
				<td class="text-end"><?php echo rupiah($i->harga) ?></td>
				<td class="text-center"><?php echo $i->qty ?></td>
				<td class="text-end"><?php echo rupiah($i->subtotal) ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
		<tfoot>
			<tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end"><?php echo rupiah($pesanan->subtotal) ?></td></tr>
			<tr><td colspan="3" class="text-end">Ongkos kirim</td><td class="text-end"><?php echo rupiah($pesanan->ongkir) ?></td></tr>
			<tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td class="text-end text-brand fs-5"><?php echo rupiah($pesanan->total) ?></td></tr>
		</tfoot>
	</table>
</div>
