<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model
{
	protected $table = 'users';

	/** Mengembalikan baris user jika username & password cocok, selain itu NULL. */
	public function login($username, $password)
	{
		$user = $this->db->get_where($this->table, ['username' => $username])->row();
		if ( ! $user || ! password_verify($password, $user->password)) return NULL;

		$this->db->set('last_login', 'NOW()', FALSE)->where('id', $user->id)->update($this->table);
		return $user;
	}

	public function create($username, $nama, $role, $password)
	{
		return $this->insert([
			'username' => $username,
			'nama'     => $nama,
			'role'     => $role,
			'password' => password_hash($password, PASSWORD_DEFAULT),
		]);
	}

	public function change_password($id, $old, $new)
	{
		$user = $this->get($id);
		if ( ! $user || ! password_verify($old, $user->password)) return FALSE;
		return $this->update($id, ['password' => password_hash($new, PASSWORD_DEFAULT)]);
	}
}
