				</div>
				<!-- /.container-fluid -->

			</div>
			<!-- End of Main Content -->

			<footer class="sticky-footer bg-white">
				<div class="container my-auto">
					<div class="copyright text-center my-auto">
						<span>SIAKAD &copy; <?php echo date('Y') ?> &middot; CodeIgniter <?php echo CI_VERSION ?> &middot; Halaman dimuat dalam {elapsed_time} detik</span>
					</div>
				</div>
			</footer>

		</div>
		<!-- End of Content Wrapper -->

	</div>
	<!-- End of Page Wrapper -->

	<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

	<!-- Logout Modal-->
	<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="logoutLabel">Keluar dari SIAKAD?</h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">Pilih "Logout" untuk mengakhiri sesi Anda.</div>
				<div class="modal-footer">
					<button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
					<a class="btn btn-primary" href="<?php echo site_url('logout') ?>">Logout</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal konfirmasi hapus (dipakai semua halaman master data) -->
	<div class="modal fade" id="hapusModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Hapus data?</h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">Data <strong id="hapus-nama"></strong> akan dihapus permanen.</div>
				<div class="modal-footer">
					<button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
					<?php echo form_open('', ['id' => 'form-hapus']) ?>
						<button type="submit" class="btn btn-danger">Hapus</button>
					<?php echo form_close() ?>
				</div>
			</div>
		</div>
	</div>

	<script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js') ?>"></script>
	<script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
	<script src="<?php echo base_url('assets/vendor/jquery-easing/jquery.easing.min.js') ?>"></script>
	<script src="<?php echo base_url('assets/js/sb-admin-2.min.js') ?>"></script>
	<script src="<?php echo base_url('assets/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
	<script src="<?php echo base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>
	<script src="<?php echo base_url('assets/vendor/chart.js/Chart.min.js') ?>"></script>
	<script>
		$('.datatable').DataTable({
			language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: '_START_ - _END_ dari _TOTAL_ data',
				infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan', paginate: { previous: '&lsaquo;', next: '&rsaquo;' } }
		});
		$(document).on('click', '.btn-hapus', function (e) {
			e.preventDefault();
			$('#form-hapus').attr('action', $(this).data('url'));
			$('#hapus-nama').text($(this).data('nama'));
			$('#hapusModal').modal('show');
		});
	</script>
	<?php if (isset($script)) echo $script ?>
</body>

</html>
