<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends Admin_Controller
{
	public function index()
	{
		$data['users'] = $this->user_model->getAll();
		$this->load->view('admin/user/list', $data);
	}
}
