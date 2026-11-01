<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pelanggan extends Admin_Controller
{
	public function index()
	{
		$this->render('admin/pelanggan/index', ['title' => 'Pelanggan', 'pelanggan' => $this->user_model->pelanggan()]);
	}
}
