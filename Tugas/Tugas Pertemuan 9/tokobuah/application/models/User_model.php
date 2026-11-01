<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
	private $_table = 'users';

	public function rules()
	{
		return [
			['field' => 'email',
			'label' => 'Email atau Username',
			'rules' => 'required'],

			['field' => 'password',
			'label' => 'Password',
			'rules' => 'required']
		];
	}

	public function getAll()
	{
		return $this->db->order_by('role', 'ASC')->order_by('full_name', 'ASC')->get($this->_table)->result();
	}

	public function countByRole($role)
	{
		return $this->db->where('role', $role)->count_all_results($this->_table);
	}

	/**
	 * Cek login. Mengembalikan salah satu status:
	 * 'ok', 'not_found', 'wrong_password', 'not_admin', 'inactive'
	 */
	public function doLogin()
	{
		$post = $this->input->post();

		// Pengguna boleh login memakai email ATAU username
		$user = $this->db->group_start()
				->where('email', $post['email'])
				->or_where('username', $post['email'])
			->group_end()
			->get($this->_table)->row();

		if ( ! $user) return 'not_found';
		if ( ! password_verify($post['password'], $user->password)) return 'wrong_password';
		if ($user->role !== 'admin') return 'not_admin';
		if ( ! $user->is_active) return 'inactive';

		// Ganti session id setelah login untuk mencegah session fixation
		$this->session->sess_regenerate(TRUE);
		$this->session->set_userdata('user_logged', [
			'user_id'   => $user->user_id,
			'username'  => $user->username,
			'full_name' => $user->full_name,
			'email'     => $user->email,
			'role'      => $user->role,
		]);
		$this->_updateLastLogin($user->user_id);
		return 'ok';
	}

	public function isNotLogin()
	{
		return $this->session->userdata('user_logged') === NULL;
	}

	public function current()
	{
		return $this->session->userdata('user_logged');
	}

	private function _updateLastLogin($user_id)
	{
		$this->db->set('last_login', 'NOW()', FALSE)
			->where('user_id', $user_id)
			->update($this->_table);
	}
}
