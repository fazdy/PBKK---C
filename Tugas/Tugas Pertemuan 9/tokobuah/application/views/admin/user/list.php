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
					<div class="card-header"><i class="fas fa-users"></i> Daftar Pengguna</div>
					<div class="card-body">
						<p class="small text-muted">Hanya pengguna dengan role <span class="badge badge-success">admin</span> yang dapat login ke panel ini.
						Role <span class="badge badge-secondary">customer</span> akan ditolak oleh <code>User_model::doLogin()</code>.</p>
						<div class="table-responsive">
							<table class="table table-bordered table-sm">
								<thead class="thead-light">
									<tr><th>Username</th><th>Nama Lengkap</th><th>Email</th><th>Role</th><th>Login Terakhir</th><th>Status</th></tr>
								</thead>
								<tbody>
								<?php foreach ($users as $u): ?>
									<tr>
										<td><?php echo html_escape($u->username) ?></td>
										<td><?php echo html_escape($u->full_name) ?></td>
										<td><?php echo html_escape($u->email) ?></td>
										<td><span class="badge badge-<?php echo $u->role === 'admin' ? 'success' : 'secondary' ?>"><?php echo $u->role ?></span></td>
										<td><?php echo $u->last_login ? date('d/m/Y H:i', strtotime($u->last_login)) : '<span class="text-muted">belum pernah</span>' ?></td>
										<td><?php echo $u->is_active ? 'Aktif' : 'Nonaktif' ?></td>
									</tr>
								<?php endforeach ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>

			</div>

			<?php $this->load->view('admin/_partials/footer.php') ?>

		</div>
	</div>

	<?php $this->load->view('admin/_partials/scrolltop.php') ?>
	<?php $this->load->view('admin/_partials/modal.php') ?>
	<?php $this->load->view('admin/_partials/js.php') ?>
</body>

</html>
