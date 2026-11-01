<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		// Sudah login? langsung ke overview
		if ( ! $this->user_model->isNotLogin()) redirect(site_url('admin'));

		$this->form_validation->set_rules($this->user_model->rules());

		if ($this->form_validation->run())
		{
			$messages = [
				'not_found'      => 'Akun tidak ditemukan.',
				'wrong_password' => 'Password yang Anda masukkan salah.',
				'not_admin'      => 'Akun ini bukan admin, sehingga tidak boleh masuk ke panel admin.',
				'inactive'       => 'Akun ini sedang dinonaktifkan.',
			];
			$status = $this->user_model->doLogin();

			if ($status === 'ok') redirect(site_url('admin'));
			$data['error'] = $messages[$status];
		}

		$data['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
		$this->load->view('admin/login_page', $data);
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect(site_url('admin/login'));
	}
}
