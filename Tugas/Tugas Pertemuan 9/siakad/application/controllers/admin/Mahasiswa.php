<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mahasiswa extends Auth_Controller
{
	protected $roles = ['admin'];

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['mahasiswa_model', 'prodi_model', 'dosen_model']);
	}

	public function index()
	{
		$data['title'] = 'Data Mahasiswa';
		$data['mahasiswa'] = $this->mahasiswa_model->get_all_lengkap();
		$this->render('admin/mahasiswa/index', $data);
	}

	public function tambah()
	{
		$this->_rules();
		if ($this->form_validation->run())
		{
			$this->mahasiswa_model->create($this->_input());
			$this->flash('success', 'Mahasiswa '.$this->input->post('nama').' ditambahkan. Password awal = NRP.');
			redirect('admin/mahasiswa');
		}
		$this->_form('Tambah Mahasiswa', NULL);
	}

	public function edit($id = NULL)
	{
		$mhs = $this->mahasiswa_model->get($id) OR show_404();
		$this->_rules($mhs);
		if ($this->form_validation->run())
		{
			$this->mahasiswa_model->update_lengkap($mhs, $this->_input());
			$this->flash('success', 'Data mahasiswa '.$this->input->post('nama').' diperbarui.');
			redirect('admin/mahasiswa');
		}
		$this->_form('Edit Mahasiswa', $mhs);
	}

	public function hapus($id = NULL)
	{
		if ($this->input->method() !== 'post') show_404();
		$mhs = $this->mahasiswa_model->get($id) OR show_404();
		$this->mahasiswa_model->delete_lengkap($mhs);
		$this->flash('success', 'Mahasiswa '.$mhs->nama.' beserta KRS-nya dihapus.');
		redirect('admin/mahasiswa');
	}

	private function _rules($mhs = NULL)
	{
		// is_unique hanya dicek bila NRP diubah, agar edit tanpa ganti NRP tetap lolos
		$unik = ( ! $mhs || $this->input->post('nrp') !== $mhs->nrp) ? '|is_unique[mahasiswa.nrp]|is_unique[users.username]' : '';
		$this->form_validation->set_rules('nrp', 'NRP', 'required|trim|exact_length[10]|numeric'.$unik);
		$this->form_validation->set_rules('nama', 'Nama', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
		$this->form_validation->set_rules('angkatan', 'Angkatan', 'required|integer|greater_than[1999]|less_than[2100]');
		$this->form_validation->set_rules('prodi_id', 'Program studi', 'required');
	}

	private function _input()
	{
		return [
			'nrp'           => $this->input->post('nrp', TRUE),
			'nama'          => $this->input->post('nama', TRUE),
			'email'         => $this->input->post('email', TRUE),
			'angkatan'      => $this->input->post('angkatan'),
			'prodi_id'      => $this->input->post('prodi_id'),
			'dosen_wali_id' => $this->input->post('dosen_wali_id') ?: NULL,
		];
	}

	private function _form($title, $mhs)
	{
		$this->render('admin/mahasiswa/form', [
			'title' => $title,
			'mhs'   => $mhs,
			'prodi' => $this->prodi_model->dropdown('nama', '-- Pilih program studi --'),
			'dosen' => $this->dosen_model->dropdown('nama', '-- Tanpa dosen wali --'),
		]);
	}
}
