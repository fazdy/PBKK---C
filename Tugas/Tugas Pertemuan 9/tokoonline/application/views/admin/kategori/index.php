<div class="row g-4">
	<div class="col-lg-7">
		<div class="bg-white border rounded-3 p-3">
			<table class="table align-middle mb-0">
				<thead class="table-light"><tr><th>Ikon</th><th>Nama</th><th>Slug</th><th class="text-center">Produk</th><th></th></tr></thead>
				<tbody>
				<?php foreach ($kategori as $k): ?>
					<tr>
						<td><i class="bi <?php echo $k->ikon ?> fs-5 text-brand"></i></td>
						<td><?php echo html_escape($k->nama) ?></td>
						<td class="small text-muted"><?php echo $k->slug ?></td>
						<td class="text-center"><?php echo $k->jumlah_produk ?></td>
						<td class="text-nowrap text-end">
							<a href="<?php echo site_url('admin/kategori/index/'.$k->id) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
							<?php echo form_open('admin/kategori/hapus/'.$k->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Hapus kategori?')"]) ?>
								<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
							<?php echo form_close() ?>
						</td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-lg-5">
		<div class="bg-white border rounded-3 p-3">
			<h2 class="h6 fw-bold"><?php echo $edit ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>
			<?php echo form_open($edit ? 'admin/kategori/index/'.$edit->id : 'admin/kategori') ?>
				<?php echo bs_input('nama', 'Nama kategori', $edit ? $edit->nama : '') ?>
				<?php echo bs_input('ikon', 'Kelas ikon Bootstrap Icons', $edit ? $edit->ikon : 'bi-tag', 'text', 'placeholder="bi-tag"') ?>
				<button class="btn btn-brand"><i class="bi bi-save"></i> Simpan</button>
				<?php if ($edit): ?><a href="<?php echo site_url('admin/kategori') ?>" class="btn btn-link">Batal</a><?php endif ?>
			<?php echo form_close() ?>
		</div>
	</div>
</div>
