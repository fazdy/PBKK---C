<?= $this->extend('layouts/store') ?>
<?= $this->section('content') ?>
<section class="container store-container forbidden-wrap"><div class="forbidden-number">403</div><span class="eyebrow muted">AKSES TERBATAS</span><h1>Halaman ini bukan area akun Anda.</h1><p>Akun dengan peran <strong><?= esc(session('role')) ?></strong> tidak memiliki izin untuk membuka halaman ini.</p><a class="primary-action" href="<?= site_url(['admin'=>'admin','operator'=>'operator','customer'=>'account'][session('role')] ?? '') ?>">Kembali ke area saya →</a></section>
<?= $this->endSection() ?>
