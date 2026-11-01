<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Khs extends Auth_Controller
{
	protected $roles = ['mahasiswa'];

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['mahasiswa_model', 'krs_model']);
	}

	public function index()
	{
		$mhs = $this->mahasiswa_model->get_by_user($this->user['id']);
		$this->render('mahasiswa/khs/index', [
			'title' => 'Kartu Hasil Studi',
			'mhs'   => $this->mahasiswa_model->get_lengkap($mhs->id),
			'khs'   => $this->krs_model->khs($mhs->id),
		]);
	}
}
