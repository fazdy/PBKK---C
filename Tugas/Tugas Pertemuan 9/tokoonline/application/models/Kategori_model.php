<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kategori_model extends CI_Model
{
	private $table = 'kategori';

	public function get_all()
	{
		return $this->db->select('kategori.*, COUNT(produk.id) AS jumlah_produk')
			->from($this->table)
			->join('produk', 'produk.kategori_id = kategori.id AND produk.aktif = 1', 'left')
			->group_by('kategori.id')
			->order_by('kategori.id')
			->get()->result();
	}

	public function get($id)
	{
		return $this->db->get_where($this->table, ['id' => $id])->row();
	}

	public function by_slug($slug)
	{
		return $this->db->get_where($this->table, ['slug' => $slug])->row();
	}

	public function dropdown()
	{
		$options = ['' => '-- Pilih kategori --'];
		foreach ($this->db->order_by('nama')->get($this->table)->result() as $k) $options[$k->id] = $k->nama;
		return $options;
	}

	public function simpan($data, $id = NULL)
	{
		$data['slug'] = url_title($data['nama'], '-', TRUE);
		if ($id) return $this->db->update($this->table, $data, ['id' => $id]);
		return $this->db->insert($this->table, $data);
	}

	public function hapus($id)
	{
		if ($this->db->where('kategori_id', $id)->count_all_results('produk') > 0) return FALSE;
		return $this->db->delete($this->table, ['id' => $id]);
	}
}
