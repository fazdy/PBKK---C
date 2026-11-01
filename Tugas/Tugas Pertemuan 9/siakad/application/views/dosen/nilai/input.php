<div class="card shadow mb-4">
	<div class="card-header py-3">
		<a href="<?php echo site_url('dosen/nilai') ?>"><i class="fas fa-arrow-left"></i> Kembali</a>
		<span class="ml-3 font-weight-bold text-primary"><?php echo html_escape($kelas->kode.' '.$kelas->matakuliah.' - Kelas '.$kelas->nama_kelas) ?></span>
		<span class="ml-2 small text-muted"><?php echo $kelas->tahun_ajaran ?> &middot; <?php echo jadwal($kelas) ?></span>
	</div>
	<div class="card-body">
		<div class="alert alert-light border small">
			<strong>Konversi nilai:</strong> A &ge; 86 &middot; AB &ge; 76 &middot; B &ge; 66 &middot; BC &ge; 61 &middot; C &ge; 56 &middot; D &ge; 41 &middot; E &lt; 41.
			Nilai huruf dihitung otomatis oleh helper <code>nilai_huruf()</code>. Kosongkan jika belum ada nilai.
		</div>
		<?php echo form_open('dosen/nilai/input/'.$kelas->id) ?>
		<table class="table table-bordered table-sm">
			<thead class="thead-light"><tr><th width="50">No</th><th>NRP</th><th>Nama</th><th>Prodi</th><th width="130">Nilai Angka</th><th width="110">Huruf</th></tr></thead>
			<tbody>
			<?php foreach ($peserta as $i => $p): ?>
				<tr>
					<td><?php echo $i + 1 ?></td>
					<td><?php echo $p->nrp ?></td>
					<td><?php echo html_escape($p->nama) ?></td>
					<td><?php echo $p->prodi ?></td>
					<td><input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm input-nilai" name="nilai[<?php echo $p->krs_id ?>]"
						value="<?php echo $p->nilai_angka === NULL ? '' : rtrim(rtrim($p->nilai_angka, '0'), '.') ?>" data-huruf="#huruf-<?php echo $p->krs_id ?>"></td>
					<td id="huruf-<?php echo $p->krs_id ?>"><?php echo badge_nilai($p->nilai_huruf) ?></td>
				</tr>
			<?php endforeach ?>
			<?php if ( ! $peserta): ?><tr><td colspan="6" class="text-center text-muted">Belum ada mahasiswa yang mengambil kelas ini.</td></tr><?php endif ?>
			</tbody>
		</table>
		<?php if ($peserta): ?>
			<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Nilai</button>
		<?php endif ?>
		<?php echo form_close() ?>
	</div>
</div>

<?php ob_start() ?>
<script>
// Pratinjau nilai huruf langsung saat dosen mengetik (aturan sama dengan helper PHP)
function huruf(n) {
	if (n === '' || isNaN(n)) return '';
	n = parseFloat(n);
	return n >= 86 ? 'A' : n >= 76 ? 'AB' : n >= 66 ? 'B' : n >= 61 ? 'BC' : n >= 56 ? 'C' : n >= 41 ? 'D' : 'E';
}
$('.input-nilai').on('input', function () {
	var h = huruf(this.value), warna = ['A', 'AB'].indexOf(h) >= 0 ? 'success' : (['B', 'BC', 'C'].indexOf(h) >= 0 ? 'primary' : 'danger');
	$($(this).data('huruf')).html(h ? '<span class="badge badge-' + warna + '">' + h + '</span> <small class="text-muted">belum disimpan</small>' : '<span class="badge badge-light">Belum dinilai</span>');
});
</script>
<?php $this->load->vars('script', ob_get_clean()) ?>
