<div class="row">
	<div class="col-lg-4 mb-4">
		<div class="card shadow h-100">
			<div class="card-body text-center">
				<i class="fas fa-user-graduate fa-4x text-primary mb-3"></i>
				<h5 class="mb-0 font-weight-bold text-gray-800"><?php echo html_escape($mhs->nama) ?></h5>
				<p class="text-muted mb-2"><?php echo $mhs->nrp ?></p>
				<p class="small mb-0"><?php echo html_escape($mhs->jenjang.' '.$mhs->prodi) ?> &middot; Angkatan <?php echo $mhs->angkatan ?><br>
				Dosen wali: <?php echo html_escape($mhs->dosen_wali ?: '-') ?></p>
			</div>
		</div>
	</div>
	<div class="col-lg-8 mb-4">
		<div class="row h-100">
			<div class="col-sm-4 mb-3">
				<div class="card border-left-primary shadow h-100 py-2"><div class="card-body">
					<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">IPK</div>
					<div class="h4 mb-0 font-weight-bold text-gray-800"><?php echo number_format($khs['ipk'], 2) ?></div>
				</div></div>
			</div>
			<div class="col-sm-4 mb-3">
				<div class="card border-left-success shadow h-100 py-2"><div class="card-body">
					<div class="text-xs font-weight-bold text-success text-uppercase mb-1">SKS lulus</div>
					<div class="h4 mb-0 font-weight-bold text-gray-800"><?php echo $khs['total_sks'] ?></div>
				</div></div>
			</div>
			<div class="col-sm-4 mb-3">
				<div class="card border-left-warning shadow h-100 py-2"><div class="card-body">
					<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">SKS diambil semester ini</div>
					<div class="h4 mb-0 font-weight-bold text-gray-800"><?php echo array_sum(array_column($krs, 'sks')) ?> / <?php echo MAX_SKS ?></div>
				</div></div>
			</div>
			<div class="col-12">
				<div class="card shadow">
					<div class="card-body py-3">
						<?php if ($krs): ?>
							<i class="fas fa-check-circle text-success"></i> Anda sudah mengambil <?php echo count($krs) ?> kelas pada <?php echo TA_AKTIF ?>.
						<?php else: ?>
							<i class="fas fa-exclamation-triangle text-warning"></i> Anda belum mengisi KRS untuk <?php echo TA_AKTIF ?>.
						<?php endif ?>
						<a href="<?php echo site_url('mahasiswa/krs') ?>" class="btn btn-sm btn-primary float-right">Buka KRS</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
