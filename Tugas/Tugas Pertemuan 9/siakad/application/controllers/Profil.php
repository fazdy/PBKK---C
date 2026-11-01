<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil extends Auth_Controller
{
	public function index()
	{
		$this->form_validation->set_rules('password_lama', 'Password lama', 'required');
		$this->form_validation->set_rules('password_baru', 'Password baru', 'required|min_length[6]');
		$this->form_validation->set_rules('konfirmasi', 'Konfirmasi password', 'required|matches[password_baru]');

		if ($this->form_validation->run())
		{
			$ok = $this->user_model->change_password($this->user['id'], $this->input->post('password_lama'), $this->input->post('password_baru'));
			if ($ok)
			{
				$this->flash('success', 'Password berhasil diganti.');
				redirect('profil');
			}
			$this->flash('danger', 'Password lama tidak sesuai.');
			redirect('profil');
		}

		$data['title'] = 'Profil Saya';
		$data['akun'] = $this->user_model->get($this->user['id']);
		$this->render('profil/index', $data);
	}
}
