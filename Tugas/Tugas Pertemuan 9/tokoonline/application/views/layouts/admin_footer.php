		</main>
		<footer class="px-4 pb-4 small text-muted">&copy; <?php echo date('Y').' '.SITE_NAME ?> &middot; CodeIgniter <?php echo CI_VERSION ?> &middot; {elapsed_time} detik</footer>
	</div>
</div>
<script src="<?php echo base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?php echo base_url('assets/vendor/Chart.min.js') ?>"></script>
<?php if (isset($script)) echo $script ?>
</body>
</html>
