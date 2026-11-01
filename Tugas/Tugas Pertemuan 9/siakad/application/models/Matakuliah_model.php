<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Matakuliah_model extends MY_Model
{
	protected $table = 'matakuliah';
	protected $order_by = 'semester, kode';

	public function dropdown_lengkap()
	{
		$options = ['' => '-- Pilih mata kuliah --'];
		foreach ($this->get_all() as $mk)
		{
			$options[$mk->id] = $mk->kode.' - '.$mk->nama.' ('.$mk->sks.' SKS)';
		}
		return $options;
	}

	public function dipakai_kelas($id)
	{
		return $this->db->where('matakuliah_id', $id)->count_all_results('kelas') > 0;
	}
}
