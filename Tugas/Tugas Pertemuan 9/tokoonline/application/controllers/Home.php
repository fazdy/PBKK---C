<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller
{
	public function index()
	{
		$this->load->model(['produk_model', 'kategori_model']);
		$this->render('home/index', [
			'title'    => 'Perlengkapan Kuliah Lengkap',
			'kategori' => $this->kategori_model->get_all(),
			'terbaru'  => $this->produk_model->terbaru(8),
		]);
	}
}
