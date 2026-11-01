<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model dasar berisi operasi CRUD umum. Model lain cukup mengisi $table.
 */
class MY_Model extends CI_Model
{
	protected $table;
	protected $primary_key = 'id';
	protected $order_by = 'id';

	public function get_all()
	{
		return $this->db->order_by($this->order_by)->get($this->table)->result();
	}

	public function get($id)
	{
		return $this->db->get_where($this->table, [$this->primary_key => $id])->row();
	}

	public function insert($data)
	{
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function update($id, $data)
	{
		return $this->db->update($this->table, $data, [$this->primary_key => $id]);
	}

	public function delete($id)
	{
		return $this->db->delete($this->table, [$this->primary_key => $id]);
	}

	public function count($where = [])
	{
		return $this->db->where($where)->count_all_results($this->table);
	}

	/** Data untuk form_dropdown(): [id => label] */
	public function dropdown($label = 'nama', $placeholder = '-- Pilih --')
	{
		$options = ['' => $placeholder];
		foreach ($this->get_all() as $row)
		{
			$options[$row->{$this->primary_key}] = $row->$label;
		}
		return $options;
	}
}
