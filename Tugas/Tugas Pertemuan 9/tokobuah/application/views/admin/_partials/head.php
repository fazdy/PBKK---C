<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<title><?php echo SITE_NAME.' Admin: '.ucfirst($this->uri->segment(2) ?: 'overview').($this->uri->segment(3) ? ' - '.ucfirst($this->uri->segment(3)) : '') ?></title>

<!-- Custom fonts for this template-->
<link href="<?php echo base_url('assets/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">

<!-- Page level plugin CSS-->
<link href="<?php echo base_url('assets/datatables/dataTables.bootstrap4.min.css') ?>" rel="stylesheet">

<!-- Custom styles for this template (sudah termasuk Bootstrap 4)-->
<link href="<?php echo base_url('css/sb-admin.min.css') ?>" rel="stylesheet">
<style>
	.navbar-brand { font-weight: 700; }
	.bg-tokobuah { background: #1e7e34 !important; }
	.img-product { width: 64px; height: 64px; object-fit: cover; border-radius: 6px; }
</style>
