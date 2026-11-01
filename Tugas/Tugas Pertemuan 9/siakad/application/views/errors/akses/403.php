<div class="text-center py-5">
	<div class="error mx-auto" data-text="403">403</div>
	<p class="lead text-gray-800 mb-3">Akses Ditolak</p>
	<p class="text-gray-500 mb-4">Halaman ini hanya untuk role <strong><?php echo implode(', ', $roles) ?></strong>,
	sedangkan Anda login sebagai <strong><?php echo html_escape($user['role']) ?></strong>.</p>
	<a href="<?php echo site_url('dashboard') ?>">&larr; Kembali ke Dashboard</a>
</div>
