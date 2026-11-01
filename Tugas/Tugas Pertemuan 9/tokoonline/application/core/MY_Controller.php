<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller       : controller toko (publik), punya render() untuk layout toko.
 * Customer_Controller : wajib login (checkout & riwayat pesanan).
 * Admin_Controller    : wajib login sebagai admin, memakai layout panel admin.
 */
class MY_Controller extends CI_Controller
{
	protected $user;

	public function __construct()
	{
		parent::__construct();
		$this->user = $this->session->userdata('user');
	}

	protected function render($view, $data = [])
	{
		$data['user'] = $this->user;
		$data['jumlah_keranjang'] = $this->keranjang->jumlah();
		$data['kategori_menu'] = $this->db->order_by('id')->get('kategori')->result();
		$this->load->view('layouts/shop_header', $data);
		$this->load->view($view, $data);
		$this->load->view('layouts/shop_footer', $data);
	}

	protected function flash($type, $message)
	{
		$this->session->set_flashdata('alert', ['type' => $type, 'message' => $message]);
	}
}

class Customer_Controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		if ( ! $this->user)
		{
			// Simpan halaman tujuan agar setelah login pengguna kembali ke sini
			$this->session->set_userdata('redirect_setelah_login', uri_string());
			$this->flash('warning', 'Silakan masuk terlebih dahulu untuk melanjutkan.');
			redirect('masuk');
		}
	}
}

class Admin_Controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		if ( ! $this->user || $this->user['role'] !== 'admin')
		{
			$this->flash('danger', 'Halaman admin hanya untuk akun admin.');
			redirect('masuk');
		}
	}

	protected function render($view, $data = [])
	{
		$data['user'] = $this->user;
		$data['pesanan_baru'] = $this->db->where('status', 'menunggu')->count_all_results('pesanan');
		$this->load->view('layouts/admin_header', $data);
		$this->load->view($view, $data);
		$this->load->view('layouts/admin_footer', $data);
	}
}
