<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * File ini otomatis dimuat CodeIgniter karena memakai prefix MY_ (subclass_prefix).
 * Semua controller panel admin cukup extends Admin_Controller agar halaman
 * hanya bisa dibuka oleh pengguna yang sudah login sebagai admin.
 */
class MY_Controller extends CI_Controller
{
}

class Admin_Controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');

		if ($this->user_model->isNotLogin())
		{
			$this->session->set_flashdata('error', 'Silakan login terlebih dahulu.');
			redirect(site_url('admin/login'));
		}
	}
}
