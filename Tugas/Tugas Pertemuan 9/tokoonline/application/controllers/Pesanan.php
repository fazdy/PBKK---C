<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pesanan extends Customer_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('pesanan_model');
	}

	public function index()
	{
		$this->render('pesanan/index', [
			'title'   => 'Pesanan Saya',
			'pesanan' => $this->pesanan_model->by_user($this->user['id']),
		]);
	}

	public function detail($kode = NULL)
	{
		// Pelanggan hanya bisa melihat pesanannya sendiri
		$pesanan = $this->pesanan_model->by_kode($kode, $this->user['id']) OR show_404();
		$this->render('pesanan/detail', ['title' => 'Pesanan '.$pesanan->kode, 'pesanan' => $pesanan]);
	}

	public function batal($kode = NULL)
	{
		if ($this->input->method() !== 'post') show_404();
		$pesanan = $this->pesanan_model->by_kode($kode, $this->user['id']) OR show_404();
		if ($pesanan->status !== 'menunggu')
		{
			$this->flash('danger', 'Pesanan yang sudah diproses tidak bisa dibatalkan.');
		}
		else
		{
			$this->pesanan_model->ubah_status($pesanan, 'dibatalkan');
			$this->flash('success', 'Pesanan '.$pesanan->kode.' dibatalkan dan stok dikembalikan.');
		}
		redirect('pesanan/detail/'.$pesanan->kode);
	}
}
