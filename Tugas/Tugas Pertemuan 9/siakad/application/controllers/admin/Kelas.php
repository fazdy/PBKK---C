<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kelas extends Auth_Controller
{
	protected $roles = ['admin'];

	/** Pesan kesalahan jadwal (di luar aturan form_validation). */
	private $data_error = NULL;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['kelas_model', 'matakuliah_model', 'dosen_model']);
	}

	public function index()
	{
		$ta = $this->input->get('ta') ?: TA_AKTIF;
		$data['title'] = 'Kelas & Jadwal';
		$data['ta'] = $ta;
		$data['daftar_ta'] = $this->kelas_model->daftar_tahun_ajaran();
		$data['kelas'] = $this->kelas_model->get_all_lengkap($ta);
		$this->render('admin/kelas/index', $data);
	}

	public function tambah()
	{
		$this->_rules();
		if ($this->form_validation->run())
		{
			if ($this->_cek_bentrok())
			{
				$this->kelas_model->insert($this->_input());
				$this->flash('success', 'Kelas baru berhasil dibuka.');
				redirect('admin/kelas');
			}
		}
		$this->_form('Buka Kelas Baru', NULL);
	}

	public function edit($id = NULL)
	{
		$kelas = $this->kelas_model->get($id) OR show_404();
		$this->_rules();
		if ($this->form_validation->run())
		{
			if ($this->_cek_bentrok($id))
			{
				$this->kelas_model->update($id, $this->_input());
				$this->flash('success', 'Data kelas diperbarui.');
				redirect('admin/kelas?ta='.urlencode($this->input->post('tahun_ajaran')));
			}
		}
		$this->_form('Edit Kelas', $kelas);
	}

	public function peserta($id = NULL)
	{
		$kelas = $this->kelas_model->get_lengkap($id) OR show_404();
		$this->render('admin/kelas/peserta', [
			'title'   => 'Peserta Kelas',
			'kelas'   => $kelas,
			'peserta' => $this->kelas_model->peserta($id),
		]);
	}

	public function hapus($id = NULL)
	{
		if ($this->input->method() !== 'post') show_404();
		$kelas = $this->kelas_model->get_lengkap($id) OR show_404();
		if ($kelas->peserta > 0)
		{
			$this->flash('danger', 'Kelas '.$kelas->matakuliah.' '.$kelas->nama_kelas.' sudah memiliki '.$kelas->peserta.' peserta, jadi tidak bisa dihapus.');
		}
		else
		{
			$this->kelas_model->delete($id);
			$this->flash('success', 'Kelas '.$kelas->matakuliah.' '.$kelas->nama_kelas.' dihapus.');
		}
		redirect('admin/kelas');
	}

	private function _rules()
	{
		$this->form_validation->set_rules('matakuliah_id', 'Mata kuliah', 'required');
		$this->form_validation->set_rules('dosen_id', 'Dosen pengampu', 'required');
		$this->form_validation->set_rules('nama_kelas', 'Nama kelas', 'required|trim|alpha|max_length[5]');
		$this->form_validation->set_rules('tahun_ajaran', 'Tahun ajaran', 'required|trim');
		$this->form_validation->set_rules('hari', 'Hari', 'required|in_list['.implode(',', daftar_hari()).']');
		$this->form_validation->set_rules('jam_mulai', 'Jam mulai', 'required');
		$this->form_validation->set_rules('jam_selesai', 'Jam selesai', 'required');
		$this->form_validation->set_rules('ruang', 'Ruang', 'required|trim|max_length[20]');
		$this->form_validation->set_rules('kuota', 'Kuota', 'required|is_natural_no_zero|less_than_equal_to[100]');
	}

	/** Validasi tambahan: jam selesai > jam mulai, ruang/dosen tidak bentrok. */
	private function _cek_bentrok($kecuali_id = NULL)
	{
		$data = $this->_input();
		if ($data['jam_selesai'] <= $data['jam_mulai'])
		{
			$this->data_error = 'Jam selesai harus lebih besar dari jam mulai.';
			return FALSE;
		}
		$bentrok = $this->kelas_model->bentrok($data, $kecuali_id);
		if ($bentrok)
		{
			$lain = $this->kelas_model->get_lengkap($bentrok->id);
			$this->data_error = 'Jadwal bentrok dengan '.$lain->matakuliah.' '.$lain->nama_kelas.' ('.jadwal($lain).', ruang '.$lain->ruang.', '.$lain->dosen.').';
			return FALSE;
		}
		return TRUE;
	}

	private function _input()
	{
		return [
			'matakuliah_id' => $this->input->post('matakuliah_id'),
			'dosen_id'      => $this->input->post('dosen_id'),
			'nama_kelas'    => strtoupper($this->input->post('nama_kelas', TRUE)),
			'tahun_ajaran'  => $this->input->post('tahun_ajaran', TRUE),
			'hari'          => $this->input->post('hari'),
			'jam_mulai'     => substr($this->input->post('jam_mulai'), 0, 5).':00',
			'jam_selesai'   => substr($this->input->post('jam_selesai'), 0, 5).':00',
			'ruang'         => strtoupper($this->input->post('ruang', TRUE)),
			'kuota'         => $this->input->post('kuota'),
		];
	}

	private function _form($title, $kelas)
	{
		$this->render('admin/kelas/form', [
			'title'      => $title,
			'kelas'      => $kelas,
			'error'      => $this->data_error,
			'matakuliah' => $this->matakuliah_model->dropdown_lengkap(),
			'dosen'      => $this->dosen_model->dropdown('nama', '-- Pilih dosen --'),
		]);
	}
}
