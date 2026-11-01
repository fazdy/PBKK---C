<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Admin_Controller
{
	public function index()
	{
		$this->load->model(['pesanan_model', 'produk_model']);
		$this->render('admin/dashboard', [
			'title'      => 'Dashboard',
			'omzet'      => $this->pesanan_model->omzet(),
			'per_status' => $this->pesanan_model->jumlah_per_status(),
			'produk'     => $this->db->where('aktif', 1)->count_all_results('produk'),
			'pelanggan'  => $this->db->where('role', 'customer')->count_all_results('users'),
			'grafik'     => $this->pesanan_model->penjualan_7_hari(),
			'terlaris'   => $this->pesanan_model->terlaris(),
			'menipis'    => $this->produk_model->stok_menipis(),
			'terbaru'    => array_slice($this->pesanan_model->semua(), 0, 5),
		]);
	}
}
