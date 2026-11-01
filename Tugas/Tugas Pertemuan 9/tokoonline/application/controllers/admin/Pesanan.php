<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pesanan extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('pesanan_model');
	}

	public function index()
	{
		$status = $this->input->get('status');
		if ($status && ! array_key_exists($status, status_pesanan())) $status = NULL;
		$this->render('admin/pesanan/index', [
			'title'      => 'Pesanan',
			'status'     => $status,
			'per_status' => $this->pesanan_model->jumlah_per_status(),
			'pesanan'    => $this->pesanan_model->semua($status),
		]);
	}

	public function detail($kode = NULL)
	{
		$pesanan = $this->pesanan_model->by_kode($kode) OR show_404();

		if ($this->input->method() === 'post')
		{
			$status = $this->input->post('status');
			if (array_key_exists($status, status_pesanan()) && $this->pesanan_model->ubah_status($pesanan, $status))
			{
				$this->flash('success', 'Status pesanan '.$pesanan->kode.' diubah menjadi "'.status_pesanan($status)[0].'".');
			}
			else
			{
				$this->flash('danger', 'Status tidak dapat diubah. Pesanan yang sudah dibatalkan bersifat final.');
			}
			redirect('admin/pesanan/detail/'.$pesanan->kode);
		}

		$this->render('admin/pesanan/detail', ['title' => 'Detail Pesanan', 'pesanan' => $pesanan]);
	}
}
