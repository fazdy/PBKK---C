<div class="bg-white border rounded-3 p-3">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<span class="small text-muted"><?php echo count($produk) ?> produk</span>
		<a href="<?php echo site_url('admin/produk/tambah') ?>" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg"></i> Tambah Produk</a>
	</div>
	<div class="table-responsive">
	<table class="table align-middle">
		<thead class="table-light"><tr><th>Produk</th><th>Kategori</th><th class="text-end">Harga</th><th class="text-center">Stok</th><th class="text-center">Terjual</th><th>Status</th><th width="140"></th></tr></thead>
		<tbody>
		<?php foreach ($produk as $p): ?>
			<tr class="<?php echo $p->aktif ? '' : 'table-secondary' ?>">
				<td><div class="d-flex align-items-center gap-2"><img src="<?php echo gambar_produk($p->gambar) ?>" class="img-thumb" alt=""><span class="fw-semibold"><?php echo html_escape($p->nama) ?></span></div></td>
				<td class="small"><?php echo html_escape($p->kategori) ?></td>
				<td class="text-end text-nowrap"><?php echo rupiah($p->harga) ?></td>
				<td class="text-center"><span class="badge text-bg-<?php echo $p->stok == 0 ? 'danger' : ($p->stok <= 5 ? 'warning' : 'light') ?>"><?php echo $p->stok ?></span></td>
				<td class="text-center"><?php echo $p->terjual ?></td>
				<td><?php echo $p->aktif ? '<span class="badge text-bg-success">Tampil</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>' ?></td>
				<td class="text-nowrap">
					<a href="<?php echo site_url('admin/produk/edit/'.$p->id) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
					<?php echo form_open('admin/produk/hapus/'.$p->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Hapus produk ini?')"]) ?>
						<button class="btn btn-sm btn-outline-danger" data-hapus="<?php echo $p->id ?>"><i class="bi bi-trash"></i></button>
					<?php echo form_close() ?>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	</div>
</div>
