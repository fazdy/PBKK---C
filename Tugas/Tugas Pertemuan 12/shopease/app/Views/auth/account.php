<?= $this->extend('layouts/store') ?>
<?= $this->section('content') ?>
<div class="container store-container dashboard-wrap">
    <div class="dashboard-heading"><div><span class="eyebrow muted">AREA PELANGGAN</span><h1>Akun saya</h1><p>Selamat datang, <?= esc($user['name']) ?>.</p></div><span class="role-badge">Customer</span></div>
    <div class="account-grid">
        <section class="panel"><div class="panel-heading"><h2>Profil pelanggan</h2><span class="pill success-pill">Akun aktif</span></div>
            <dl class="profile-details"><dt>Nama lengkap</dt><dd><?= esc($user['name']) ?></dd><dt>Email</dt><dd><?= esc($user['email']) ?></dd><dt>Nomor telepon</dt><dd><?= esc($user['phone'] ?: 'Belum diisi') ?></dd><dt>Alamat</dt><dd><?= esc($user['address'] ?: 'Belum diisi') ?></dd><dt>Terdaftar sejak</dt><dd><?= esc($user['created_at']) ?> WIB</dd></dl>
        </section>
        <aside class="panel next-card"><span class="eyebrow">PILIHAN UNTUK ANDA</span><h2>Temukan kebutuhan<br>sehari-hari.</h2><p>Jelajahi koleksi elektronik, fesyen, aksesori, dan perlengkapan rumah.</p><a class="primary-action" href="<?= site_url('catalog') ?>">Jelajahi katalog →</a><img src="<?= base_url('assets/img/headphone.svg') ?>" alt="Headphone pilihan ShopEase"></aside>
    </div>
</div>
<?= $this->endSection() ?>
