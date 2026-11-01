<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Satu URL dashboard, isinya berbeda sesuai role pengguna. */
class Dashboard extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model(['mahasiswa_model', 'dosen_model', 'matakuliah_model', 'kelas_model', 'krs_model', 'prodi_model']);
	}

	public function index()
	{
		$method = '_'.$this->user['role'];
		$this->$method();
	}

	private function _admin()
	{
		$data = [
			'title'            => 'Dashboard',
			'jumlah_mahasiswa' => $this->mahasiswa_model->count(),
			'jumlah_dosen'     => $this->dosen_model->count(),
			'jumlah_mk'        => $this->matakuliah_model->count(),
			'jumlah_kelas'     => $this->kelas_model->count(['tahun_ajaran' => TA_AKTIF]),
			'per_prodi'        => $this->prodi_model->jumlah_mahasiswa(),
			'kelas'            => $this->kelas_model->get_all_lengkap(TA_AKTIF),
		];
		$this->render('dashboard/admin', $data);
	}

	private function _dosen()
	{
		$dosen = $this->dosen_model->get_by_user($this->user['id']);
		$kelas = array_filter($this->kelas_model->get_by_dosen($dosen->id), function ($k) { return $k->tahun_ajaran === TA_AKTIF; });
		$data = [
			'title'         => 'Dashboard',
			'dosen'         => $dosen,
			'kelas'         => $kelas,
			'bimbingan'     => $this->mahasiswa_model->jumlah_bimbingan($dosen->id),
			'belum_dinilai' => $this->krs_model->belum_dinilai_dosen($dosen->id),
		];
		$this->render('dashboard/dosen', $data);
	}

	private function _mahasiswa()
	{
		$mhs = $this->mahasiswa_model->get_by_user($this->user['id']);
		$khs = $this->krs_model->khs($mhs->id);
		$data = [
			'title'   => 'Dashboard',
			'mhs'     => $this->mahasiswa_model->get_lengkap($mhs->id),
			'khs'     => $khs,
			'krs'     => $this->krs_model->get_by_mahasiswa($mhs->id, TA_AKTIF),
		];
		$this->render('dashboard/mahasiswa', $data);
	}
}
