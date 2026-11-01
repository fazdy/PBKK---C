<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dosen extends Auth_Controller
{
	protected $roles = ['admin'];

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['dosen_model', 'prodi_model']);
	}

	public function index()
	{
		$data['title'] = 'Data Dosen';
		$data['dosen'] = $this->dosen_model->get_all_lengkap();
		$this->render('admin/dosen/index', $data);
	}

	public function tambah()
	{
		$this->_rules();
		$this->form_validation->set_rules('password', 'Password awal', 'required|min_length[6]');
		if ($this->form_validation->run())
		{
			$this->dosen_model->create($this->_input(), $this->input->post('password'));
			$this->flash('success', 'Dosen '.$this->input->post('nama').' ditambahkan.');
			redirect('admin/dosen');
		}
		$this->_form('Tambah Dosen', NULL);
	}

	public function edit($id = NULL)
	{
		$dosen = $this->dosen_model->get($id) OR show_404();
		$this->_rules($dosen);
		if ($this->form_validation->run())
		{
			$this->dosen_model->update_lengkap($dosen, $this->_input());
			$this->flash('success', 'Data dosen '.$this->input->post('nama').' diperbarui.');
			redirect('admin/dosen');
		}
		$this->_form('Edit Dosen', $dosen);
	}

	public function hapus($id = NULL)
	{
		if ($this->input->method() !== 'post') show_404();
		$dosen = $this->dosen_model->get($id) OR show_404();

		if ($this->db->where('dosen_id', $id)->count_all_results('kelas') > 0)
		{
			$this->flash('danger', 'Dosen '.$dosen->nama.' masih mengampu kelas, jadi belum bisa dihapus.');
			redirect('admin/dosen');
		}
		$this->dosen_model->delete_lengkap($dosen);
		$this->flash('success', 'Dosen '.$dosen->nama.' dihapus.');
		redirect('admin/dosen');
	}

	private function _rules($dosen = NULL)
	{
		$unik = ( ! $dosen || $this->input->post('nip') !== $dosen->nip) ? '|is_unique[dosen.nip]|is_unique[users.username]' : '';
		$this->form_validation->set_rules('nip', 'NIP', 'required|trim|exact_length[18]|numeric'.$unik);
		$this->form_validation->set_rules('nama', 'Nama', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
		$this->form_validation->set_rules('prodi_id', 'Program studi', 'required');
	}

	private function _input()
	{
		return [
			'nip'      => $this->input->post('nip', TRUE),
			'nama'     => $this->input->post('nama', TRUE),
			'email'    => $this->input->post('email', TRUE),
			'prodi_id' => $this->input->post('prodi_id'),
		];
	}

	private function _form($title, $dosen)
	{
		$this->render('admin/dosen/form', [
			'title' => $title,
			'dosen' => $dosen,
			'prodi' => $this->prodi_model->dropdown('nama', '-- Pilih program studi --'),
		]);
	}
}
