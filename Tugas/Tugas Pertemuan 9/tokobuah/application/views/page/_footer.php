<footer class="mt-auto py-3 bg-light border-top">
	<div class="container small text-muted d-flex justify-content-between">
		<span>&copy; <?php echo date('Y').' '.SITE_NAME ?></span>
		<span>Controller: <code><?php echo $this->router->fetch_class().'::'.$this->router->fetch_method().'()' ?></code> &middot; Halaman dimuat dalam {elapsed_time} detik</span>
	</div>
</footer>
</body>
</html>
