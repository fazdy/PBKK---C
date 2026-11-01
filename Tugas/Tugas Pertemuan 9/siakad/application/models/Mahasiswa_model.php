<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mahasiswa_model extends MY_Model
{
	protected $table = 'mahasiswa';
	protected $order_by = 'nrp';

	public function get_all_lengkap()
	{
		return $this->db->select('mahasiswa.*, prodi.nama AS prodi, dosen.nama AS dosen_wali')
			->from($this->table)
			->join('prodi', 'prodi.id = mahasiswa.prodi_id')
			->join('dosen', 'dosen.id = mahasiswa.dosen_wali_id', 'left')
			->order_by('mahasiswa.nrp')
			->get()->result();
	}

	public function get_lengkap($id)
	{
		return $this->db->select('mahasiswa.*, prodi.nama AS prodi, prodi.jenjang, dosen.nama AS dosen_wali')
			->from($this->table)
			->join('prodi', 'prodi.id = mahasiswa.prodi_id')
			->join('dosen', 'dosen.id = mahasiswa.dosen_wali_id', 'left')
			->where('mahasiswa.id', $id)
			->get()->row();
	}

	public function get_by_user($user_id)
	{
		return $this->db->get_where($this->table, ['user_id' => $user_id])->row();
	}

	/** Tambah mahasiswa sekaligus akun login. Password awal = NRP. */
	public function create($data)
	{
		$this->db->trans_start();
		$data['user_id'] = $this->user_model->create($data['nrp'], $data['nama'], 'mahasiswa', $data['nrp']);
		$id = $this->insert($data);
		$this->db->trans_complete();
		return $this->db->trans_status() ? $id : FALSE;
	}

	public function update_lengkap($mhs, $data)
	{
		$this->db->trans_start();
		$this->update($mhs->id, $data);
		$this->user_model->update($mhs->user_id, ['username' => $data['nrp'], 'nama' => $data['nama']]);
		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/** Akun dihapus; data mahasiswa dan KRS ikut terhapus (ON DELETE CASCADE). */
	public function delete_lengkap($mhs)
	{
		return $this->user_model->delete($mhs->user_id);
	}

	public function jumlah_bimbingan($dosen_id)
	{
		return $this->count(['dosen_wali_id' => $dosen_id]);
	}
}
