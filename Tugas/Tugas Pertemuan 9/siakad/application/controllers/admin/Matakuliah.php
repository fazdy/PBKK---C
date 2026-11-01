<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Matakuliah extends Auth_Controller
{
	protected $roles = ['admin'];

	public function __construct()
	{
		parent::__construct();
		$this->load->model('matakuliah_model');
	}

	public function index()
	{
		$data['title'] = 'Data Mata Kuliah';
		$data['matakuliah'] = $this->matakuliah_model->get_all();
		$this->render('admin/matakuliah/index', $data);
	}

	public function tambah()
	{
		$this->_rules();
		if ($this->form_validation->run())
		{
			$this->matakuliah_model->insert($this->_input());
			$this->flash('success', 'Mata kuliah '.$this->input->post('nama').' ditambahkan.');
			redirect('admin/matakuliah');
		}
		$this->render('admin/matakuliah/form', ['title' => 'Tambah Mata Kuliah', 'mk' => NULL]);
	}

	public function edit($id = NULL)
	{
		$mk = $this->matakuliah_model->get($id) OR show_404();
		$this->_rules($mk);
		if ($this->form_validation->run())
		{
			$this->matakuliah_model->update($id, $this->_input());
			$this->flash('success', 'Mata kuliah '.$this->input->post('nama').' diperbarui.');
			redirect('admin/matakuliah');
		}
		$this->render('admin/matakuliah/form', ['title' => 'Edit Mata Kuliah', 'mk' => $mk]);
	}

	public function hapus($id = NULL)
	{
		if ($this->input->method() !== 'post') show_404();
		$mk = $this->matakuliah_model->get($id) OR show_404();
		if ($this->matakuliah_model->dipakai_kelas($id))
		{
			$this->flash('danger', 'Mata kuliah '.$mk->nama.' sudah dibuka di kelas, jadi belum bisa dihapus.');
		}
		else
		{
			$this->matakuliah_model->delete($id);
			$this->flash('success', 'Mata kuliah '.$mk->nama.' dihapus.');
		}
		redirect('admin/matakuliah');
	}

	private function _rules($mk = NULL)
	{
		$unik = ( ! $mk || $this->input->post('kode') !== $mk->kode) ? '|is_unique[matakuliah.kode]' : '';
		$this->form_validation->set_rules('kode', 'Kode', 'required|trim|alpha_numeric|max_length[10]'.$unik);
		$this->form_validation->set_rules('nama', 'Nama mata kuliah', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('sks', 'SKS', 'required|is_natural_no_zero|less_than_equal_to[6]');
		$this->form_validation->set_rules('semester', 'Semester', 'required|is_natural_no_zero|less_than_equal_to[8]');
	}

	private function _input()
	{
		return [
			'kode'     => strtoupper($this->input->post('kode', TRUE)),
			'nama'     => $this->input->post('nama', TRUE),
			'sks'      => $this->input->post('sks'),
			'semester' => $this->input->post('semester'),
		];
	}
}
