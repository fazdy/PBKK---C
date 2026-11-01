<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kategori extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('kategori_model');
	}

	/** Daftar kategori + form tambah/edit di halaman yang sama. */
	public function index($edit_id = NULL)
	{
		$edit = $edit_id ? $this->kategori_model->get($edit_id) : NULL;

		$this->form_validation->set_rules('nama', 'Nama kategori', 'required|trim|max_length[50]');
		$this->form_validation->set_rules('ikon', 'Ikon', 'required|trim|regex_match[/^bi-[a-z0-9-]+$/]');
		if ($this->form_validation->run())
		{
			$this->kategori_model->simpan(['nama' => $this->input->post('nama', TRUE), 'ikon' => $this->input->post('ikon', TRUE)], $edit ? $edit->id : NULL);
			$this->flash('success', 'Kategori '.$this->input->post('nama').' disimpan.');
			redirect('admin/kategori');
		}

		$this->render('admin/kategori/index', [
			'title'    => 'Kategori',
			'kategori' => $this->kategori_model->get_all(),
			'edit'     => $edit,
		]);
	}

	public function hapus($id = NULL)
	{
		if ($this->input->method() !== 'post') show_404();
		$kategori = $this->kategori_model->get($id) OR show_404();
		if ($this->kategori_model->hapus($id)) $this->flash('success', 'Kategori '.$kategori->nama.' dihapus.');
		else $this->flash('danger', 'Kategori '.$kategori->nama.' masih memiliki produk, jadi tidak bisa dihapus.');
		redirect('admin/kategori');
	}
}
