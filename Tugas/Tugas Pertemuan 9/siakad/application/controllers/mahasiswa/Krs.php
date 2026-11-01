<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Krs extends Auth_Controller
{
	protected $roles = ['mahasiswa'];
	private $mhs;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['mahasiswa_model', 'kelas_model', 'krs_model']);
		$this->mhs = $this->mahasiswa_model->get_by_user($this->user['id']);
	}

	public function index()
	{
		$krs = $this->krs_model->get_by_mahasiswa($this->mhs->id, TA_AKTIF);
		$this->render('mahasiswa/krs/index', [
			'title'     => 'Kartu Rencana Studi',
			'krs'       => $krs,
			'total_sks' => array_sum(array_column($krs, 'sks')),
			'diambil'   => array_column($krs, 'kelas_id'),
			'kelas'     => $this->kelas_model->get_all_lengkap(TA_AKTIF),
		]);
	}

	public function ambil()
	{
		if ($this->input->method() !== 'post') show_404();
		$hasil = $this->krs_model->ambil($this->mhs->id, (int) $this->input->post('kelas_id'));
		if ($hasil === TRUE) $this->flash('success', 'Kelas berhasil ditambahkan ke KRS.');
		else $this->flash('danger', $hasil);
		redirect('mahasiswa/krs');
	}

	public function batal()
	{
		if ($this->input->method() !== 'post') show_404();
		if ($this->krs_model->batal($this->mhs->id, (int) $this->input->post('krs_id')))
		{
			$this->flash('success', 'Kelas dibatalkan dari KRS.');
		}
		else
		{
			$this->flash('danger', 'Kelas tidak dapat dibatalkan (sudah dinilai atau bukan milik Anda).');
		}
		redirect('mahasiswa/krs');
	}
}
