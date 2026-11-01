<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Katalog extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model(['produk_model', 'kategori_model']);
		$this->load->library('pagination');
	}

	/**
	 * /produk, /produk/2, /kategori/elektronik, /kategori/elektronik/2
	 * Query string: ?q=kata+kunci&urut=murah
	 */
	public function index($slug = 'semua', $halaman = 1)
	{
		$kategori = NULL;
		if ($slug !== 'semua')
		{
			$kategori = $this->kategori_model->by_slug($slug) OR show_404();
		}

		$filter = [
			'kategori_id' => $kategori ? $kategori->id : NULL,
			'q'           => trim((string) $this->input->get('q', TRUE)),
			'urut'        => $this->input->get('urut') ?: 'terbaru',
		];
		$total = $this->produk_model->count_katalog($filter);
		$halaman = max(1, (int) $halaman);

		// Library Pagination CodeIgniter dengan markup Bootstrap 5
		$this->pagination->initialize([
			'base_url'             => site_url($kategori ? 'kategori/'.$kategori->slug : 'produk'),
			'total_rows'           => $total,
			'per_page'             => PER_HALAMAN,
			'use_page_numbers'     => TRUE,
			'reuse_query_string'   => TRUE,
			'uri_segment'          => $kategori ? 3 : 2,
			'full_tag_open'        => '<ul class="pagination justify-content-center">',
			'full_tag_close'       => '</ul>',
			'num_tag_open'         => '<li class="page-item">', 'num_tag_close' => '</li>',
			'cur_tag_open'         => '<li class="page-item active"><span class="page-link">', 'cur_tag_close' => '</span></li>',
			'prev_tag_open'        => '<li class="page-item">', 'prev_tag_close' => '</li>',
			'next_tag_open'        => '<li class="page-item">', 'next_tag_close' => '</li>',
			'first_link'           => FALSE, 'last_link' => FALSE,
			'prev_link'            => '&lsaquo;', 'next_link' => '&rsaquo;',
			'attributes'           => ['class' => 'page-link'],
		]);

		$this->render('katalog/index', [
			'title'      => $kategori ? $kategori->nama : 'Semua Produk',
			'kategori'   => $kategori,
			'filter'     => $filter,
			'total'      => $total,
			'produk'     => $this->produk_model->katalog($filter, PER_HALAMAN, ($halaman - 1) * PER_HALAMAN),
			'pagination' => $this->pagination->create_links(),
		]);
	}

	public function detail($slug = NULL)
	{
		$produk = $this->produk_model->by_slug($slug) OR show_404();
		$this->render('katalog/detail', [
			'title'   => $produk->nama,
			'produk'  => $produk,
			'terkait' => $this->produk_model->terkait($produk),
		]);
	}
}
