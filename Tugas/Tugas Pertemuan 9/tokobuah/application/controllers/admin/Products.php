<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$data['products'] = $this->product_model->getAll();
		$this->load->view('admin/product/list', $data);
	}

	public function add()
	{
		$product = $this->product_model;
		$validation = $this->form_validation;
		$validation->set_rules($product->rules());

		if ($validation->run())
		{
			if ($product->save())
			{
				$this->session->set_flashdata('success', 'Produk "'.$product->name.'" berhasil disimpan.');
				redirect(site_url('admin/products'));
			}
			$data['upload_error'] = $product->upload_error;
		}

		$this->load->view('admin/product/new_form', isset($data) ? $data : []);
	}

	public function edit($id = NULL)
	{
		if ( ! isset($id)) redirect(site_url('admin/products'));

		$product = $this->product_model;
		$validation = $this->form_validation;
		$validation->set_rules($product->rules());

		if ($validation->run())
		{
			if ($product->update())
			{
				$this->session->set_flashdata('success', 'Perubahan produk "'.$product->name.'" berhasil disimpan.');
				redirect(site_url('admin/products'));
			}
			$data['upload_error'] = $product->upload_error;
		}

		$data['product'] = $product->getById($id);
		if ( ! $data['product']) show_404();

		$this->load->view('admin/product/edit_form', $data);
	}

	/** Hapus hanya lewat POST (tombol di modal) agar terlindungi token CSRF. */
	public function delete($id = NULL)
	{
		if ( ! isset($id) || $this->input->method() !== 'post') show_404();

		$product = $this->product_model->getById($id);
		if ( ! $product) show_404();

		if ($this->product_model->delete($id))
		{
			$this->session->set_flashdata('success', 'Produk "'.$product->name.'" berhasil dihapus.');
		}
		redirect(site_url('admin/products'));
	}
}
