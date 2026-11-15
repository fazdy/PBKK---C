<?= $this->extend('layouts/store') ?>
<?= $this->section('content') ?>
<?php $isAdmin = $area === 'admin'; $roleLabel = $isAdmin ? 'Administrator' : 'Operator'; ?>
<div class="container store-container dashboard-wrap">
    <div class="dashboard-heading"><div><span class="eyebrow muted">RUANG <?= $isAdmin ? 'ADMINISTRASI' : 'OPERATOR' ?></span><h1>Dashboard <?= esc($roleLabel) ?></h1><p>Selamat datang kembali, <?= esc(session('name')) ?>.</p></div><span class="role-badge"><?= esc($roleLabel) ?></span></div>
    <?php if ($isAdmin): ?>
    <div class="stats-grid">
        <?php foreach (['customer'=>'Pelanggan terdaftar','admin'=>'Administrator','operator'=>'Operator toko'] as $key=>$label): ?>
        <section class="stat-card"><span><?= esc($label) ?></span><strong><?= (int)$counts[$key] ?></strong><span class="stat-caption">Akun aktif di ShopEase</span></section>
        <?php endforeach; ?>
    </div>
    <section class="panel"><div class="panel-heading"><div><h2>Pengguna terbaru</h2><p>Sepuluh akun terakhir yang terdaftar.</p></div><span class="pill">Data pengguna</span></div>
        <div class="table-responsive"><table class="table user-table"><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Tanggal daftar (WIB)</th></tr></thead><tbody>
        <?php foreach ($users as $user): ?><tr><td><?= esc($user['name']) ?></td><td><?= esc($user['email']) ?></td><td><span class="pill role-<?= esc($user['role'],'attr') ?>"><?= esc(ucfirst($user['role'])) ?></span></td><td><?= esc($user['created_at']) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
    <?php else: ?>
    <div class="account-grid"><section class="panel"><div class="panel-heading"><h2>Identitas operator</h2><span class="pill success-pill">Sesi aktif</span></div>
        <dl class="profile-details"><dt>Nama</dt><dd><?= esc(session('name')) ?></dd><dt>Email</dt><dd><?= esc(session('email')) ?></dd><dt>Peran</dt><dd>Operator toko</dd><dt>Akses</dt><dd>Area operator dan katalog publik</dd></dl>
        <div class="notice success">Anda berhasil masuk ke area operator ShopEase.</div>
    </section><aside class="panel next-card"><span class="eyebrow">KOLEKSI SHOPEASE</span><h2>Kenali produk<br>pilihan toko.</h2><p>Lihat produk dan kategori yang tersedia pada katalog publik.</p><a class="primary-action" href="<?= site_url('catalog') ?>">Buka katalog →</a><img src="<?= base_url('assets/img/keyboard.svg') ?>" alt="Ilustrasi keyboard"></aside></div>
    <?php endif; ?>
    <div class="dashboard-foot"><span class="status-dot"></span> Anda masuk sebagai <?= esc($roleLabel) ?>.<a href="<?= site_url() ?>">Kembali ke beranda →</a></div>
</div>
<?= $this->endSection() ?>
