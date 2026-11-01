<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller dasar SIAKAD.
 * - MY_Controller   : menyimpan data user yang sedang login dan fungsi render() template.
 * - Auth_Controller : wajib login; jika $roles diisi, hanya role tersebut yang boleh masuk.
 */
class MY_Controller extends CI_Controller
{
	/** @var array|null data user dari session */
	protected $user;

	public function __construct()
	{
		parent::__construct();
		$this->user = $this->session->userdata('user');
	}

	/** Tampilkan view di dalam template SB Admin 2 (header + sidebar + footer). */
	protected function render($view, $data = [])
	{
		$data['user'] = $this->user;
		$data['title'] = isset($data['title']) ? $data['title'] : 'SIAKAD';
		$this->load->view('templates/header', $data);
		$this->load->view($view, $data);
		$this->load->view('templates/footer', $data);
	}

	protected function flash($type, $message)
	{
		$this->session->set_flashdata('alert', ['type' => $type, 'message' => $message]);
	}
}

class Auth_Controller extends MY_Controller
{
	/** Role yang boleh mengakses controller ini. Kosong = semua role yang sudah login. */
	protected $roles = [];

	public function __construct()
	{
		parent::__construct();

		if ( ! $this->user)
		{
			$this->flash('warning', 'Sesi berakhir atau Anda belum login. Silakan login terlebih dahulu.');
			redirect('login');
		}

		if ($this->roles && ! in_array($this->user['role'], $this->roles, TRUE))
		{
			$this->output->set_status_header(403);
			$this->render('errors/akses/403', ['title' => 'Akses Ditolak', 'roles' => $this->roles]);
			$this->output->_display();
			exit;
		}
	}
}
