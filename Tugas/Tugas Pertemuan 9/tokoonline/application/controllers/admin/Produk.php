<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produk extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model(['produk_model', 'kategori_model']);
	}

	public function index()
	{
		$this->render('admin/produk/index', ['title' => 'Produk', 'produk' => $this->produk_model->get_all_admin()]);
	}

	public function tambah()
	{
		$this->_form(NULL);
	}

	public function edit($id = NULL)
	{
		$produk = $this->produk_model->get($id) OR show_404();
		$this->_form($produk);
	}

	public function hapus($id = NULL)
	{
		if ($this->input->method() !== 'post') show_404();
		$produk = $this->produk_model->get($id) OR show_404();
		$hasil = $this->produk_model->hapus($produk);
		$this->flash('success', $hasil === 'hapus'
			? 'Produk '.$produk->nama.' dihapus.'
			: 'Produk '.$produk->nama.' sudah pernah dipesan, jadi dinonaktifkan (disembunyikan dari toko) agar riwayat pesanan tetap utuh.');
		redirect('admin/produk');
	}

	private function _form($produk)
	{
		$this->form_validation->set_rules('nama', 'Nama produk', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('kategori_id', 'Kategori', 'required');
		$this->form_validation->set_rules('harga', 'Harga', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('stok', 'Stok', 'required|is_natural');
		$this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required|trim');

		$upload_error = NULL;
		if ($this->form_validation->run())
		{
			$data = [
				'nama'        => $this->input->post('nama', TRUE),
				'kategori_id' => $this->input->post('kategori_id'),
				'harga'       => $this->input->post('harga'),
				'stok'        => $this->input->post('stok'),
				'deskripsi'   => $this->input->post('deskripsi', TRUE),
				'aktif'       => $this->input->post('aktif') ? 1 : 0,
			];
			if ($this->produk_model->simpan($data, $produk) !== FALSE)
			{
				$this->flash('success', 'Produk '.$data['nama'].' berhasil disimpan.');
				redirect('admin/produk');
			}
			$upload_error = $this->produk_model->upload_error;
		}

		$this->render('admin/produk/form', [
			'title'        => $produk ? 'Edit Produk' : 'Tambah Produk',
			'produk'       => $produk,
			'kategori'     => $this->kategori_model->dropdown(),
			'upload_error' => $upload_error,
		]);
	}
}
