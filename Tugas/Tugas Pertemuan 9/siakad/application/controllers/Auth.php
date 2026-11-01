<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
	public function login()
	{
		if ($this->user) redirect('dashboard');

		$this->form_validation->set_rules('username', 'Username', 'required|trim');
		$this->form_validation->set_rules('password', 'Password', 'required');

		$data['error'] = NULL;
		if ($this->form_validation->run())
		{
			$user = $this->user_model->login($this->input->post('username', TRUE), $this->input->post('password'));
			if ($user)
			{
				$this->session->sess_regenerate(TRUE);
				$this->session->set_userdata('user', [
					'id'       => $user->id,
					'username' => $user->username,
					'nama'     => $user->nama,
					'role'     => $user->role,
				]);
				redirect('dashboard');
			}
			$data['error'] = 'Username atau password salah.';
		}

		$this->load->view('auth/login', $data);
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('login');
	}
}
