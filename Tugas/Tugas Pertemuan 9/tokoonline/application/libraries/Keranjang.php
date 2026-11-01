<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Library keranjang belanja sederhana berbasis session.
 * Isi session hanya [produk_id => qty]; data produk (nama, harga, stok)
 * selalu dibaca ulang dari database supaya harga & stok selalu terbaru.
 */
class Keranjang
{
	protected $CI;
	protected $key = 'keranjang';

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	protected function raw()
	{
		$isi = $this->CI->session->userdata($this->key);
		return is_array($isi) ? $isi : [];
	}

	protected function simpan($isi)
	{
		$this->CI->session->set_userdata($this->key, $isi);
	}

	public function tambah($produk_id, $qty = 1)
	{
		$isi = $this->raw();
		$isi[$produk_id] = (isset($isi[$produk_id]) ? $isi[$produk_id] : 0) + max(1, (int) $qty);
		$this->simpan($isi);
	}

	public function ubah($produk_id, $qty)
	{
		$isi = $this->raw();
		if ((int) $qty <= 0) unset($isi[$produk_id]);
		else $isi[$produk_id] = (int) $qty;
		$this->simpan($isi);
	}

	public function hapus($produk_id)
	{
		$this->ubah($produk_id, 0);
	}

	public function kosongkan()
	{
		$this->CI->session->unset_userdata($this->key);
	}

	/** Jumlah barang (total qty) untuk badge di navbar. */
	public function jumlah()
	{
		return array_sum($this->raw());
	}

	/**
	 * Item keranjang lengkap dengan data produk. Qty dipotong otomatis
	 * jika melebihi stok, produk nonaktif/habis dikeluarkan dari keranjang.
	 */
	public function items()
	{
		$isi = $this->raw();
		if ( ! $isi) return [];

		$produk = $this->CI->db->where_in('id', array_keys($isi))->where('aktif', 1)->get('produk')->result();
		$items = [];
		$bersih = [];
		foreach ($produk as $p)
		{
			$qty = min($isi[$p->id], $p->stok);
			if ($qty <= 0) continue;
			$p->qty = $qty;
			$p->subtotal = $qty * $p->harga;
			$items[] = $p;
			$bersih[$p->id] = $qty;
		}
		if ($bersih !== $isi) $this->simpan($bersih);
		return $items;
	}

	public function subtotal($items = NULL)
	{
		return array_sum(array_column($items === NULL ? $this->items() : $items, 'subtotal'));
	}
}
