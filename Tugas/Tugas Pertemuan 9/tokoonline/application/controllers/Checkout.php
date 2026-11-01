<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Checkout extends Customer_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('pesanan_model');
	}

	public function index()
	{
		$items = $this->keranjang->items();
		if ( ! $items)
		{
			$this->flash('warning', 'Keranjang masih kosong.');
			redirect('keranjang');
		}

		$this->form_validation->set_rules('nama_penerima', 'Nama penerima', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('telepon', 'Nomor telepon', 'required|trim|numeric|min_length[9]|max_length[15]');
		$this->form_validation->set_rules('alamat', 'Alamat pengiriman', 'required|trim|min_length[10]');
		$this->form_validation->set_rules('metode_bayar', 'Metode pembayaran', 'required|in_list[transfer,cod]');
		$this->form_validation->set_rules('catatan', 'Catatan', 'trim|max_length[255]');

		if ($this->form_validation->run())
		{
			$hasil = $this->pesanan_model->buat($this->user['id'], [
				'nama_penerima' => $this->input->post('nama_penerima', TRUE),
				'telepon'       => $this->input->post('telepon', TRUE),
				'alamat'        => $this->input->post('alamat', TRUE),
				'metode_bayar'  => $this->input->post('metode_bayar'),
				'catatan'       => $this->input->post('catatan', TRUE) ?: NULL,
			], $items);

			if (strpos($hasil, 'KM-') === 0)
			{
				$this->keranjang->kosongkan();
				$this->flash('success', 'Terima kasih! Pesanan '.$hasil.' berhasil dibuat.');
				redirect('pesanan/detail/'.$hasil);
			}
			$this->flash('danger', $hasil);
			redirect('keranjang');
		}

		$subtotal = $this->keranjang->subtotal($items);
		$this->render('checkout/index', [
			'title'    => 'Checkout',
			'items'    => $items,
			'subtotal' => $subtotal,
			'akun'     => $this->user_model->get($this->user['id']),
		]);
	}
}
