<?= $this->extend('layouts/store') ?>
<?= $this->section('content') ?>
<div class="container store-container auth-shell register-shell">
    <?= $this->include('auth/intro') ?>
    <section class="auth-form-card" aria-labelledby="register-title">
        <span class="eyebrow muted">MULAI DARI SINI</span>
        <h2 id="register-title">Buat akun baru</h2>
        <p class="form-subtitle">Daftar sebagai pelanggan ShopEase.</p>
        <?= $this->include('auth/errors') ?>
        <form action="<?= site_url('register') ?>" method="post">
            <?= csrf_field() ?>
            <div class="field"><label for="name">Nama lengkap</label><input id="name" name="name" autocomplete="name" minlength="3" maxlength="100" placeholder="Nama lengkap Anda" value="<?= esc($values['name'] ?? '', 'attr') ?>" required></div>
            <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="username" maxlength="100" placeholder="nama@email.com" value="<?= esc($values['email'] ?? '', 'attr') ?>" required></div>
            <div class="field"><label for="phone">Nomor telepon <span>(opsional)</span></label><input id="phone" name="phone" type="tel" autocomplete="tel" maxlength="20" placeholder="08xxxxxxxxxx" value="<?= esc($values['phone'] ?? '', 'attr') ?>"></div>
            <div class="field"><label for="address">Alamat <span>(opsional)</span></label><textarea id="address" name="address" autocomplete="street-address" maxlength="500" rows="2" placeholder="Alamat Anda"><?= esc($values['address'] ?? '') ?></textarea></div>
            <div class="field-grid">
                <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="72" placeholder="Minimal 8 karakter" required></div>
                <div class="field"><label for="password_confirm">Konfirmasi password</label><input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" minlength="8" maxlength="72" placeholder="Ulangi password" required></div>
            </div>
            <button type="submit" class="primary-action">Daftar sekarang <span aria-hidden="true">→</span></button>
        </form>
        <p class="form-switch">Sudah punya akun? <a href="<?= site_url('login') ?>">Masuk di sini</a></p>
    </section>
</div>
<?= $this->endSection() ?>
