<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nilai extends Auth_Controller
{
	protected $roles = ['dosen'];
	private $dosen;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['dosen_model', 'kelas_model', 'krs_model']);
		$this->dosen = $this->dosen_model->get_by_user($this->user['id']);
	}

	public function index()
	{
		$data['title'] = 'Kelas yang Saya Ampu';
		$data['kelas'] = $this->kelas_model->get_by_dosen($this->dosen->id);
		$this->render('dosen/nilai/index', $data);
	}

	public function input($kelas_id = NULL)
	{
		$kelas = $this->kelas_model->get_lengkap($kelas_id);
		// Dosen hanya boleh mengisi nilai kelasnya sendiri
		if ( ! $kelas || $kelas->dosen_id != $this->dosen->id) show_404();

		if ($this->input->method() === 'post')
		{
			$nilai = (array) $this->input->post('nilai');
			$salah = [];
			foreach ($nilai as $krs_id => $angka)
			{
				$angka = trim($angka);
				if ($angka !== '' && ( ! is_numeric($angka) || $angka < 0 || $angka > 100))
				{
					$salah[] = $angka;
					continue;
				}
				$this->krs_model->set_nilai($krs_id, $kelas_id, $angka);
			}
			if ($salah) $this->flash('warning', 'Sebagian nilai diabaikan karena bukan angka 0-100: '.implode(', ', $salah));
			else $this->flash('success', 'Nilai kelas '.$kelas->matakuliah.' '.$kelas->nama_kelas.' berhasil disimpan.');
			redirect('dosen/nilai/input/'.$kelas_id);
		}

		$this->render('dosen/nilai/input', [
			'title'   => 'Input Nilai',
			'kelas'   => $kelas,
			'peserta' => $this->kelas_model->peserta($kelas_id),
		]);
	}
}
