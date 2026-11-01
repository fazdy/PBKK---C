<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dosen_model extends MY_Model
{
	protected $table = 'dosen';
	protected $order_by = 'nama';

	public function get_all_lengkap()
	{
		return $this->db->select('dosen.*, prodi.nama AS prodi, users.last_login,
				(SELECT COUNT(*) FROM kelas WHERE kelas.dosen_id = dosen.id AND kelas.tahun_ajaran = '.$this->db->escape(TA_AKTIF).') AS jumlah_kelas')
			->from($this->table)
			->join('prodi', 'prodi.id = dosen.prodi_id')
			->join('users', 'users.id = dosen.user_id')
			->order_by('dosen.nama')
			->get()->result();
	}

	public function get_by_user($user_id)
	{
		return $this->db->get_where($this->table, ['user_id' => $user_id])->row();
	}

	/** Tambah dosen sekaligus akun login (username = NIP). */
	public function create($data, $password)
	{
		$this->db->trans_start();
		$data['user_id'] = $this->user_model->create($data['nip'], $data['nama'], 'dosen', $password);
		$id = $this->insert($data);
		$this->db->trans_complete();
		return $this->db->trans_status() ? $id : FALSE;
	}

	public function update_lengkap($dosen, $data)
	{
		$this->db->trans_start();
		$this->update($dosen->id, $data);
		$this->user_model->update($dosen->user_id, ['username' => $data['nip'], 'nama' => $data['nama']]);
		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/** Akun user dihapus; baris dosen ikut terhapus (ON DELETE CASCADE). */
	public function delete_lengkap($dosen)
	{
		return $this->user_model->delete($dosen->user_id);
	}
}
