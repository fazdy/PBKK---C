<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Akun extends MY_Controller
{
	public function login()
	{
		if ($this->user) redirect($this->user['role'] === 'admin' ? 'admin' : 'pesanan');

		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');
		$error = NULL;

		if ($this->form_validation->run())
		{
			$user = $this->user_model->login($this->input->post('email', TRUE), $this->input->post('password'));
			if ($user)
			{
				$tujuan = $this->session->userdata('redirect_setelah_login');
				$this->session->sess_regenerate(TRUE);
				$this->session->set_userdata('user', ['id' => $user->id, 'nama' => $user->nama, 'email' => $user->email, 'role' => $user->role]);
				$this->session->unset_userdata('redirect_setelah_login');
				$this->flash('success', 'Halo, '.$user->nama.'! Anda berhasil masuk.');
				redirect($user->role === 'admin' ? 'admin' : ($tujuan ?: 'produk'));
			}
			$error = 'Email atau password salah.';
		}
		$this->render('akun/login', ['title' => 'Masuk', 'error' => $error]);
	}

	public function register()
	{
		if ($this->user) redirect('produk');

		$this->form_validation->set_rules('nama', 'Nama lengkap', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[users.email]',
			['is_unique' => 'Email ini sudah terdaftar. Silakan masuk.']);
		$this->form_validation->set_rules('telepon', 'Nomor telepon', 'required|trim|numeric|min_length[9]|max_length[15]');
		$this->form_validation->set_rules('alamat', 'Alamat', 'required|trim|min_length[10]');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
		$this->form_validation->set_rules('konfirmasi', 'Konfirmasi password', 'required|matches[password]');

		if ($this->form_validation->run())
		{
			$this->user_model->register([
				'nama'     => $this->input->post('nama', TRUE),
				'email'    => $this->input->post('email', TRUE),
				'telepon'  => $this->input->post('telepon', TRUE),
				'alamat'   => $this->input->post('alamat', TRUE),
				'password' => $this->input->post('password'),
			]);
			$this->flash('success', 'Pendaftaran berhasil. Silakan masuk dengan email Anda.');
			redirect('masuk');
		}
		$this->render('akun/register', ['title' => 'Daftar Akun']);
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('home');
	}
}
