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

				<div class="card mb-3">
					<div class="card-header">
						<a href="<?php echo site_url('admin/products') ?>"><i class="fas fa-arrow-left"></i> Kembali</a>
						<span class="ml-3 font-weight-bold">Edit Produk</span>
					</div>
					<div class="card-body">

						<?php echo form_open_multipart('admin/products/edit/'.$product->product_id) ?>
							<input type="hidden" name="id" value="<?php echo html_escape($product->product_id) ?>" />
							<?php $this->load->view('admin/product/_form_fields') ?>

							<input class="btn btn-success" type="submit" name="btn" value="Simpan" />
						<?php echo form_close() ?>

					</div>

					<div class="card-footer small text-muted">
						* wajib diisi
					</div>

				</div>
				<!-- /.card -->

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

</body>

</html>
