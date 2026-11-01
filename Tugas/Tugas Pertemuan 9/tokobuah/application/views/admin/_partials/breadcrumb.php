<!-- Breadcrumbs: dibuat otomatis dari segmen URL -->
<ol class="breadcrumb">
	<li class="breadcrumb-item">
		<a href="<?php echo site_url('admin') ?>">Admin</a>
	</li>
	<?php
	$segments = $this->uri->segment_array();
	$labels = ['overview' => 'Overview', 'products' => 'Produk', 'users' => 'Pengguna', 'add' => 'Tambah', 'edit' => 'Edit'];
	$url = 'admin';
	foreach (array_slice($segments, 1, 2) as $i => $segment):
		$url .= '/'.$segment;
		$label = isset($labels[$segment]) ? $labels[$segment] : ucfirst($segment);
		$is_last = $i === count(array_slice($segments, 1, 2)) - 1;
	?>
		<?php if ($is_last): ?>
			<li class="breadcrumb-item active"><?php echo html_escape($label) ?></li>
		<?php else: ?>
			<li class="breadcrumb-item"><a href="<?php echo site_url($url) ?>"><?php echo html_escape($label) ?></a></li>
		<?php endif ?>
	<?php endforeach ?>
	<?php if (count($segments) <= 1): ?>
		<li class="breadcrumb-item active">Overview</li>
	<?php endif ?>
</ol>

<?php if ($this->session->flashdata('success')): ?>
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		<i class="fas fa-check-circle"></i> <?php echo html_escape($this->session->flashdata('success')) ?>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	</div>
<?php endif ?>
