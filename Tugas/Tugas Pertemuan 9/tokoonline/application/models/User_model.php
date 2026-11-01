<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
	private $table = 'users';

	public function login($email, $password)
	{
		$user = $this->db->get_where($this->table, ['email' => $email])->row();
		return ($user && password_verify($password, $user->password)) ? $user : NULL;
	}

	public function register($data)
	{
		$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
		$data['role'] = 'customer';
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function get($id)
	{
		return $this->db->get_where($this->table, ['id' => $id])->row();
	}

	/** Daftar pelanggan beserta jumlah pesanan & total belanja (pesanan tidak batal). */
	public function pelanggan()
	{
		return $this->db->select("users.*, COUNT(pesanan.id) AS jumlah_pesanan,
				COALESCE(SUM(CASE WHEN pesanan.status <> 'dibatalkan' THEN pesanan.total END), 0) AS total_belanja", FALSE)
			->from($this->table)
			->join('pesanan', 'pesanan.user_id = users.id', 'left')
			->where('users.role', 'customer')
			->group_by('users.id')
			->order_by('total_belanja', 'DESC')
			->get()->result();
	}
}
