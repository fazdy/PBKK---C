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

				<!-- Icon Cards-->
				<div class="row">
					<div class="col-xl-3 col-sm-6 mb-3">
						<div class="card text-white bg-primary o-hidden h-100">
							<div class="card-body">
								<div class="card-body-icon"><i class="fas fa-fw fa-boxes"></i></div>
								<div class="mr-5"><?php echo $product_count ?> Produk Buah</div>
							</div>
							<a class="card-footer text-white clearfix small z-1" href="<?php echo site_url('admin/products') ?>">
								<span class="float-left">Lihat Detail</span>
								<span class="float-right"><i class="fas fa-angle-right"></i></span>
							</a>
						</div>
					</div>
					<div class="col-xl-3 col-sm-6 mb-3">
						<div class="card text-white bg-warning o-hidden h-100">
							<div class="card-body">
								<div class="card-body-icon"><i class="fas fa-fw fa-tags"></i></div>
								<div class="mr-5">Rata-rata Rp <?php echo number_format((float) $stats->avg_price, 0, ',', '.') ?></div>
							</div>
							<a class="card-footer text-white clearfix small z-1" href="<?php echo site_url('admin/products') ?>">
								<span class="float-left">Harga per produk</span>
								<span class="float-right"><i class="fas fa-angle-right"></i></span>
							</a>
						</div>
					</div>
					<div class="col-xl-3 col-sm-6 mb-3">
						<div class="card text-white bg-success o-hidden h-100">
							<div class="card-body">
								<div class="card-body-icon"><i class="fas fa-fw fa-user-shield"></i></div>
								<div class="mr-5"><?php echo $admin_count ?> Admin</div>
							</div>
							<a class="card-footer text-white clearfix small z-1" href="<?php echo site_url('admin/users') ?>">
								<span class="float-left">Lihat Detail</span>
								<span class="float-right"><i class="fas fa-angle-right"></i></span>
							</a>
						</div>
					</div>
					<div class="col-xl-3 col-sm-6 mb-3">
						<div class="card text-white bg-danger o-hidden h-100">
							<div class="card-body">
								<div class="card-body-icon"><i class="fas fa-fw fa-users"></i></div>
								<div class="mr-5"><?php echo $customer_count ?> Customer</div>
							</div>
							<a class="card-footer text-white clearfix small z-1" href="<?php echo site_url('admin/users') ?>">
								<span class="float-left">Lihat Detail</span>
								<span class="float-right"><i class="fas fa-angle-right"></i></span>
							</a>
						</div>
					</div>
				</div>

				<!-- Bar Chart: harga setiap produk diambil dari database -->
				<div class="card mb-3">
					<div class="card-header">
						<i class="fas fa-chart-bar"></i> Perbandingan Harga Produk (Rp)
					</div>
					<div class="card-body">
						<canvas id="priceChart" width="100%" height="28"></canvas>
					</div>
					<div class="card-footer small text-muted">
						Termurah Rp <?php echo number_format((float) $stats->min_price, 0, ',', '.') ?> &middot;
						Termahal Rp <?php echo number_format((float) $stats->max_price, 0, ',', '.') ?>
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
	new Chart(document.getElementById('priceChart'), {
		type: 'bar',
		data: {
			labels: <?php echo json_encode(array_column($products, 'name')) ?>,
			datasets: [{
				label: 'Harga',
				backgroundColor: 'rgba(30,126,52,0.75)',
				borderColor: 'rgba(30,126,52,1)',
				data: <?php echo json_encode(array_map('intval', array_column($products, 'price'))) ?>
			}]
		},
		options: {
			legend: { display: false },
			scales: { yAxes: [{ ticks: { beginAtZero: true, callback: function (v) { return 'Rp ' + v.toLocaleString('id-ID'); } } }] }
		}
	});
	</script>
</body>

</html>
