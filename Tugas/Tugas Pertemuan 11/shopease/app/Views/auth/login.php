<?= $this->extend('layouts/store') ?>
<?= $this->section('content') ?>
<div class="container store-container auth-shell">
    <?= $this->include('auth/intro') ?>
    <section class="auth-form-card" aria-labelledby="login-title">
        <span class="eyebrow muted">SELAMAT DATANG KEMBALI</span>
        <h2 id="login-title">Masuk ke ShopEase</h2>
        <p class="form-subtitle">Masukkan email dan password untuk melanjutkan.</p>
        <?php if (request()->getGet('logout') === '1'): ?><div class="notice success" role="status">Anda berhasil logout. Sesi telah diakhiri.</div><?php endif; ?>
        <?= $this->include('auth/errors') ?>
        <form action="<?= site_url('login') ?>" method="post">
            <?= csrf_field() ?>
            <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="username" maxlength="100" placeholder="nama@email.com" value="<?= esc($values['email'] ?? '', 'attr') ?>" required></div>
            <div class="field"><label for="password">Password</label><div class="password-field"><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan password" required><button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan password" aria-pressed="false">Lihat</button></div></div>
            <button type="submit" class="primary-action">Masuk <span aria-hidden="true">→</span></button>
        </form>
        <p class="form-switch">Belum punya akun? <a href="<?= site_url('register') ?>">Daftar sekarang</a></p>
        <div class="trust-note"><span class="status-dot"></span> Password Anda dilindungi dengan hashing.</div>
    </section>
</div>
<?= $this->endSection() ?>
