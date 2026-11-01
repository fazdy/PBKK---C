<?php $this->load->view('page/_header') ?>
<main class="container py-5">
	<h1 class="h3 mb-3">Tentang <?php echo SITE_NAME ?></h1>
	<p><?php echo SITE_NAME ?> adalah toko buah daring contoh yang dibuat untuk latihan framework CodeIgniter 3.
	Halaman ini ditampilkan oleh method <code>about()</code> pada controller <code>Page</code>.</p>
	<div class="card bg-light">
		<div class="card-body">
			<h2 class="h6">Bagaimana halaman ini sampai ke browser?</h2>
			<ol class="mb-0 small">
				<li>Browser meminta URL <code><?php echo current_url() ?></code>.</li>
				<li>Router membaca segmen <code><?php echo html_escape(uri_string()) ?></code> dan aturan <code>$route['about'] = 'page/about'</code>.</li>
				<li>Controller <code>Page</code> dijalankan, lalu method <code>about()</code> memanggil <code>$this-&gt;load-&gt;view('page/about')</code>.</li>
				<li>View menghasilkan HTML yang Anda lihat sekarang.</li>
			</ol>
		</div>
	</div>
</main>
<?php $this->load->view('page/_footer') ?>
