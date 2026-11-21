<?php
$role = session('role') === 'admin' ? 'Administrator' : 'Operator';
$links = ['catalog-dashboard' => ['Ringkasan','overview'], 'products' => ['Produk','box'], 'categories' => ['Kategori','layers'], 'inventory' => ['Inventori','stock']];
$current = service('uri')->getSegment(2, 'catalog-dashboard');
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= esc($title) ?> · ShopEase</title>
<link rel="icon" href="<?= base_url('assets/img/brand.svg') ?>"><link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/management.css') ?>"><script defer src="<?= base_url('assets/js/management.js') ?>"></script>
</head><body class="manage">
<svg class="symbols" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><defs>
<symbol id="overview" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/></symbol>
<symbol id="box" viewBox="0 0 24 24"><path d="m12 2 9 5v10l-9 5-9-5V7zM3 7l9 5 9-5M12 12v10M7 4.8l9 5"/></symbol>
<symbol id="layers" viewBox="0 0 24 24"><path d="m12 3 10 5-10 5L2 8zM3 12l9 5 9-5M3 16l9 5 9-5"/></symbol>
<symbol id="stock" viewBox="0 0 24 24"><path d="M3 21h18M5 17v-6h3v6m3 0V4h3v13m3 0V8h3v9"/></symbol>
<symbol id="bag" viewBox="0 0 24 24"><path d="M4 8h16l1 13H3zM8 9V7a4 4 0 0 1 8 0v2"/></symbol>
<symbol id="external" viewBox="0 0 24 24"><path d="M14 3h7v7m0-7L10 14m0-9H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/></symbol>
</defs></svg>
<a class="visually-hidden-focusable" href="#main">Lewati navigasi</a>
<div class="workspace"><aside class="rail"><a class="shop-brand" href="<?= site_url('admin/catalog-dashboard') ?>"><span><svg><use href="#bag"/></svg></span>ShopEase</a>
<nav aria-label="Menu pengelolaan"><?php foreach ($links as $path => [$label,$icon]): ?>
<a href="<?= site_url('admin/' . $path) ?>" <?= $current === $path ? 'aria-current="page"' : '' ?>><svg><use href="#<?= $icon ?>"/></svg><?= $label ?></a>
<?php endforeach ?></nav><div class="rail-foot"><a href="<?= site_url('catalog') ?>"><svg><use href="#external"/></svg> Katalog publik</a>
<form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button>Keluar</button></form></div></aside>
<div class="work-main"><header class="work-top"><span>ShopEase <b>/</b> Pengelolaan katalog</span><a class="profile" href="<?= site_url(session('role') === 'admin' ? 'admin' : 'operator') ?>"><i><?= $role === 'Operator' ? 'OP' : 'AD' ?></i><?= esc($role) ?></a></header>
<main id="main"><div class="heading"><div><h1><?= esc($title) ?></h1><?= $this->renderSection('subtitle') ?></div><div class="heading-action"><?= $this->renderSection('actions') ?></div></div>
<?php foreach (['success'=>'ok','error'=>'error'] as $key=>$class): if ($msg = session()->getFlashdata($key)): ?><div class="notice-box <?= $class ?>" role="<?= $key === 'success' ? 'status' : 'alert' ?>"><?= esc($msg) ?></div><?php endif; endforeach ?>
<?php if ($errors = session()->getFlashdata('errors')): ?><div class="notice-box error" role="alert"><strong>Periksa kembali formulir.</strong><ul><?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach ?></ul></div><?php endif ?>
<?= $this->renderSection('content') ?></main></div></div></body></html>
