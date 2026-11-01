<!DOCTYPE html>
<html lang="id">

<head>
	<?php $this->load->view('admin/_partials/head.php') ?>
</head>

<body id="page-top">

	<?php $this->load->view('admin/_partials/navbar.php') ?>
	<div id="wrapper">

		<?php $this->load->view('admin/_partials/sidebar.php') ?>

		<div id="content-wrapper">

			<div class="container-fluid">

				<?php $this->load->view('admin/_partials/breadcrumb.php') ?>

				<!-- DataTables -->
				<div class="card mb-3">
					<div class="card-header">
						<a href="<?php echo site_url('admin/products/add') ?>" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Tambah Produk</a>
					</div>
					<div class="card-body">

						<div class="table-responsive">
							<table class="table table-hover" id="dataTable" width="100%" cellspacing="0">
								<thead>
									<tr>
										<th>Nama</th>
										<th>Harga</th>
										<th>Foto</th>
										<th>Deskripsi</th>
										<th>Aksi</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($products as $product): ?>
									<tr>
										<td width="160">
											<?php echo html_escape($product->name) ?>
										</td>
										<td class="text-nowrap" data-order="<?php echo $product->price ?>">
											Rp <?php echo number_format($product->price, 0, ',', '.') ?>
										</td>
										<td>
											<img src="<?php echo base_url('upload/product/'.$product->image) ?>" class="img-product" alt="<?php echo html_escape($product->name) ?>" />
										</td>
										<td class="small">
											<?php echo html_escape(character_limiter($product->description, 90, '…')) ?></td>
										<td width="190">
											<a href="<?php echo site_url('admin/products/edit/'.$product->product_id) ?>"
											 class="btn btn-sm btn-link"><i class="fas fa-edit"></i> Edit</a>
											<a onclick="deleteConfirm('<?php echo site_url('admin/products/delete/'.$product->product_id) ?>', <?php echo html_escape(json_encode($product->name)) ?>)"
											 href="#!" class="btn btn-sm btn-link text-danger"><i class="fas fa-trash"></i> Hapus</a>
										</td>
									</tr>
									<?php endforeach; ?>

								</tbody>
							</table>
						</div>
					</div>
				</div>

			</div>
			<!-- /.container-fluid -->

			<?php $this->load->view('admin/_partials/footer.php') ?>

		</div>
		<!-- /.content-wrapper -->

	</div>
	<!-- /#wrapper -->


	<?php $this->load->view('admin/_partials/scrolltop.php') ?>
	<?php $this->load->view('admin/_partials/modal.php') ?>

	<?php $this->load->view('admin/_partials/js.php') ?>

	<script>
	$('#dataTable').DataTable({
		search: { search: <?php echo json_encode((string) $this->input->get('q')) ?> },
		language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_ - _END_ dari _TOTAL_ produk',
			infoEmpty: 'Tidak ada data', zeroRecords: 'Produk tidak ditemukan', paginate: { previous: 'Sebelumnya', next: 'Berikutnya' } }
	});
	</script>
</body>

</html>
