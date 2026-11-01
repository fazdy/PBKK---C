<?php $this->load->view('page/_header') ?>
<main class="container py-5">
	<h1 class="h3">Halo, <?php echo html_escape($name) ?>!</h1>
	<p>Teks <strong><?php echo html_escape($name) ?></strong> diambil dari segmen ketiga URL dan diteruskan sebagai parameter method <code>Page::hello($name)</code>.</p>
</main>
<?php $this->load->view('page/_footer') ?>
