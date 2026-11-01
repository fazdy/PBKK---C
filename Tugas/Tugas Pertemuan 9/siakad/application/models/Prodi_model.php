<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prodi_model extends MY_Model
{
	protected $table = 'prodi';

	/** Jumlah mahasiswa per prodi, untuk grafik dashboard. */
	public function jumlah_mahasiswa()
	{
		return $this->db->select('prodi.nama, COUNT(mahasiswa.id) AS total')
			->from($this->table)
			->join('mahasiswa', 'mahasiswa.prodi_id = prodi.id', 'left')
			->group_by('prodi.id')
			->order_by('prodi.id')
			->get()->result();
	}
}
