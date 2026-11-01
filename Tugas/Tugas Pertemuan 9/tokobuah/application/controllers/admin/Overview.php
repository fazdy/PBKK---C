<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Overview extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
	}

	public function index()
	{
		$data['product_count']  = $this->product_model->count();
		$data['stats']          = $this->product_model->getStats();
		$data['admin_count']    = $this->user_model->countByRole('admin');
		$data['customer_count'] = $this->user_model->countByRole('customer');
		$data['products']       = $this->product_model->getAll();

		// load view admin/overview.php
		$this->load->view('admin/overview', $data);
	}
}
