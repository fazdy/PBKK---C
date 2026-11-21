<?php
$navigation = ['home' => 'Beranda', 'catalog' => 'Katalog', 'about' => 'Tentang', 'contact' => 'Kontak'];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ShopEase, katalog toko online untuk elektronik, fesyen, rumah tangga, dan aksesori.">
    <title><?= esc($title) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= esc(base_url('assets/img/brand.svg'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(base_url('assets/vendor/bootstrap/bootstrap.min.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/shopease.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
    <script src="<?= base_url('assets/js/auth.js') ?>" defer></script>
</head>
<body>
    <?= $this->include('frontend/partials/icons') ?>
    <a class="visually-hidden-focusable skip-link" href="#main-content">Lewati ke konten</a>
    <header class="site-header">
        <div class="container store-container site-header-inner">
            <a class="brand" href="<?= esc(url_to('home'), 'attr') ?>">
                <img class="brand-mark" src="<?= esc(base_url('assets/img/brand.svg'), 'attr') ?>" width="40" height="40" alt="">
                <span>Shop<span class="brand-accent">Ease</span></span>
            </a>
            <nav class="store-nav" aria-label="Navigasi utama">
                <?php foreach ($navigation as $route => $label): ?>
                    <a href="<?= esc(url_to($route), 'attr') ?>"<?= $page === $route ? ' aria-current="page"' : '' ?>><?= esc($label) ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="account-nav">
                <?php if (session('isLoggedIn')): ?>
                    <?php $areaUrl = ['admin'=>'admin', 'operator'=>'operator', 'customer'=>'account'][session('role')] ?? 'account'; ?>
                    <a class="account-pill" href="<?= site_url($areaUrl) ?>"><span class="avatar-small"><?= esc(mb_strtoupper(mb_substr((string)session('name'),0,1))) ?></span><?= esc(explode(' ',(string)session('name'))[0]) ?></a>
                    <form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button class="logout-link" type="submit">Keluar</button></form>
                <?php else: ?>
                    <a class="login-link" href="<?= site_url('login') ?>">Masuk</a>
                    <a class="register-link" href="<?= site_url('register') ?>">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main id="main-content" tabindex="-1">
        <?php if ($notice = session()->getFlashdata('success')): ?><div class="container store-container mt-3"><div class="notice success" role="status"><?= esc($notice) ?></div></div><?php endif; ?>
        <?php if ($notice = session()->getFlashdata('error')): ?><div class="container store-container mt-3"><div class="notice error" role="alert"><?= esc($notice) ?></div></div><?php endif; ?>
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="site-footer">
        <div class="container store-container">
            <div class="footer-main">
                <div>
                    <a class="brand footer-brand" href="<?= esc(url_to('home'), 'attr') ?>">
                        <img class="brand-mark" src="<?= esc(base_url('assets/img/brand.svg'), 'attr') ?>" width="36" height="36" alt="">
                        <span>Shop<span class="brand-accent">Ease</span></span>
                    </a>
                    <p>Produk pilihan untuk kebutuhan sehari-hari.</p>
                </div>
                <nav class="footer-nav" aria-label="Navigasi footer">
                    <?php foreach ($navigation as $route => $label): ?>
                        <a href="<?= esc(url_to($route), 'attr') ?>"><?= esc($label) ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="footer-note">
                <span>ShopEase Online Store</span>
                <span>Prototipe PBKK · Data dan harga ilustratif</span>
            </div>
        </div>
    </footer>
</body>
</html>
