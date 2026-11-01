<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends MY_Controller
{
	public function index()
	{
		$items = $this->keranjang->items();
		$this->render('cart/index', [
			'title'    => 'Keranjang Belanja',
			'items'    => $items,
			'subtotal' => $this->keranjang->subtotal($items),
		]);
	}

	public function tambah()
	{
		if ($this->input->method() !== 'post') show_404();
		$produk = $this->db->get_where('produk', ['id' => (int) $this->input->post('produk_id'), 'aktif' => 1])->row();
		if ( ! $produk || $produk->stok < 1)
		{
			$this->flash('danger', 'Maaf, produk tidak tersedia atau stok habis.');
			redirect($this->agent_back());
		}
		$this->keranjang->tambah($produk->id, $this->input->post('qty') ?: 1);
		$this->flash('success', $produk->nama.' ditambahkan ke keranjang.');
		redirect($this->input->post('langsung_beli') ? 'keranjang' : $this->agent_back());
	}

	public function ubah()
	{
		if ($this->input->method() !== 'post') show_404();
		foreach ((array) $this->input->post('qty') as $id => $qty) $this->keranjang->ubah((int) $id, (int) $qty);
		// items() otomatis memotong qty yang melebihi stok
		$this->keranjang->items();
		$this->flash('success', 'Keranjang diperbarui.');
		redirect('keranjang');
	}

	public function hapus()
	{
		if ($this->input->method() !== 'post') show_404();
		$this->keranjang->hapus((int) $this->input->post('produk_id'));
		$this->flash('success', 'Produk dihapus dari keranjang.');
		redirect('keranjang');
	}

	/** Kembali ke halaman sebelumnya (hanya jika masih di situs ini). */
	private function agent_back()
	{
		$ref = (string) $this->input->server('HTTP_REFERER');
		return strpos($ref, base_url()) === 0 ? $ref : 'produk';
	}
}
