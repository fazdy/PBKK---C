<?php $this->load->view('page/_header') ?>
<section class="hero">
	<div class="container">
		<h1 class="font-weight-bold">Buah segar setiap hari</h1>
		<p class="lead mb-0">Dipetik langsung dari petani lokal dan diantar ke rumah Anda.</p>
	</div>
</section>
<main class="container py-4">
	<h2 class="h4 mb-3">Daftar Buah</h2>
	<div class="row">
		<?php foreach ($products as $product): ?>
		<div class="col-sm-6 col-lg-4 mb-4">
			<div class="card h-100 shadow-sm">
				<img class="card-img-top" src="<?php echo base_url('upload/product/'.$product->image) ?>" alt="<?php echo html_escape($product->name) ?>">
				<div class="card-body">
					<h5 class="card-title mb-1"><?php echo html_escape($product->name) ?></h5>
					<p class="text-success font-weight-bold mb-2">Rp <?php echo number_format($product->price, 0, ',', '.') ?></p>
					<p class="card-text small text-muted"><?php echo html_escape(character_limiter($product->description, 100, '…')) ?></p>
				</div>
			</div>
		</div>
		<?php endforeach ?>
	</div>
</main>
<?php $this->load->view('page/_footer') ?>
