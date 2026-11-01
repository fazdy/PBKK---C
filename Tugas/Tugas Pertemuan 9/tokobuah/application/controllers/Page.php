<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contoh controller untuk latihan PBKK-2-2 (Router dan Controller).
 *   /index.php/page           -> Page::index()
 *   /index.php/page/about     -> Page::about()
 *   /index.php/about          -> Page::about()   (lewat $route['about'])
 *   /index.php/page/hello/Budi -> Page::hello('Budi') (segmen ke-3 jadi parameter)
 */
class Page extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
	}

	public function index()
	{
		$data['title'] = 'Beranda';
		$data['products'] = $this->product_model->getAll();
		$this->load->view('page/home', $data);
	}

	public function about()
	{
		$data['title'] = 'Tentang Kami';
		$this->load->view('page/about', $data);
	}

	public function contact()
	{
		$data['title'] = 'Kontak';
		$this->load->view('page/contact', $data);
	}

	public function hello($name = 'Pengunjung')
	{
		$data['title'] = 'Halo';
		$data['name'] = urldecode($name);
		$this->load->view('page/hello', $data);
	}
}
