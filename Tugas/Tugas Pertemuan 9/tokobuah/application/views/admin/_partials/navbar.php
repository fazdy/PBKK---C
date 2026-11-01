<?php $user = $this->session->userdata('user_logged'); ?>
<nav class="navbar navbar-expand navbar-dark bg-tokobuah static-top">

	<a class="navbar-brand mr-1" href="<?php echo site_url('admin') ?>"><i class="fas fa-apple-alt"></i> <?php echo SITE_NAME ?></a>

	<button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" href="#">
		<i class="fas fa-bars"></i>
	</button>

	<!-- Navbar Search -->
	<form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0" method="get" action="<?php echo site_url('admin/products') ?>">
		<div class="input-group">
			<input type="text" name="q" class="form-control" placeholder="Cari produk..." aria-label="Search">
			<div class="input-group-append">
				<button class="btn btn-light" type="submit">
					<i class="fas fa-search"></i>
				</button>
			</div>
		</div>
	</form>

	<!-- Navbar -->
	<ul class="navbar-nav ml-auto ml-md-0">
		<li class="nav-item dropdown no-arrow">
			<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-user-circle fa-fw"></i> <?php echo html_escape($user['full_name']) ?>
			</a>
			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
				<span class="dropdown-item-text small text-muted"><?php echo html_escape($user['email']) ?></span>
				<a class="dropdown-item" href="<?php echo site_url() ?>" target="_blank">Lihat Website</a>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">Logout</a>
			</div>
		</li>
	</ul>

</nav>
