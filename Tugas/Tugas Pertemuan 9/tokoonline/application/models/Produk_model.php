<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produk_model extends CI_Model
{
	private $table = 'produk';
	private $upload_path = './upload/produk/';
	public $upload_error = '';

	/** Filter katalog: kategori_id, q (kata kunci), urut. Hanya produk aktif. */
	private function _filter($filter)
	{
		$this->db->from($this->table)
			->join('kategori', 'kategori.id = produk.kategori_id')
			->where('produk.aktif', 1);
		if ( ! empty($filter['kategori_id'])) $this->db->where('produk.kategori_id', $filter['kategori_id']);
		if ( ! empty($filter['q']))
		{
			$this->db->group_start()->like('produk.nama', $filter['q'])->or_like('produk.deskripsi', $filter['q'])->group_end();
		}
	}

	public function katalog($filter, $limit, $offset)
	{
		$urutan = [
			'terbaru' => ['produk.id', 'DESC'],
			'murah'   => ['produk.harga', 'ASC'],
			'mahal'   => ['produk.harga', 'DESC'],
			'nama'    => ['produk.nama', 'ASC'],
		];
		$urut = isset($urutan[$filter['urut']]) ? $urutan[$filter['urut']] : $urutan['terbaru'];

		$this->_filter($filter);
		return $this->db->select('produk.*, kategori.nama AS kategori')
			->order_by('produk.stok > 0', 'DESC', FALSE)
			->order_by($urut[0], $urut[1])
			->limit($limit, $offset)
			->get()->result();
	}

	public function count_katalog($filter)
	{
		$this->_filter($filter);
		return $this->db->count_all_results();
	}

	public function terbaru($limit = 4)
	{
		return $this->db->select('produk.*, kategori.nama AS kategori')->from($this->table)
			->join('kategori', 'kategori.id = produk.kategori_id')
			->where(['produk.aktif' => 1, 'produk.stok >' => 0])
			->order_by('produk.id', 'DESC')->limit($limit)->get()->result();
	}

	public function by_slug($slug)
	{
		return $this->db->select('produk.*, kategori.nama AS kategori, kategori.slug AS kategori_slug')->from($this->table)
			->join('kategori', 'kategori.id = produk.kategori_id')
			->where(['produk.slug' => $slug, 'produk.aktif' => 1])->get()->row();
	}

	public function terkait($produk, $limit = 4)
	{
		return $this->db->where(['kategori_id' => $produk->kategori_id, 'id !=' => $produk->id, 'aktif' => 1])
			->order_by('RAND()', '', FALSE)->limit($limit)->get($this->table)->result();
	}

	/* ---------- Admin ---------- */

	public function get_all_admin()
	{
		return $this->db->select('produk.*, kategori.nama AS kategori,
				(SELECT COALESCE(SUM(d.qty),0) FROM pesanan_detail d JOIN pesanan p ON p.id = d.pesanan_id
				 WHERE d.produk_id = produk.id AND p.status <> \'dibatalkan\') AS terjual', FALSE)
			->from($this->table)->join('kategori', 'kategori.id = produk.kategori_id')
			->order_by('produk.id', 'DESC')->get()->result();
	}

	public function get($id)
	{
		return $this->db->get_where($this->table, ['id' => $id])->row();
	}

	/** Simpan produk baru / perubahan. Mengembalikan FALSE jika upload gambar gagal. */
	public function simpan($data, $produk = NULL)
	{
		$data['slug'] = $this->_slug_unik($data['nama'], $produk ? $produk->id : NULL);

		if ( ! empty($_FILES['gambar']['name']))
		{
			$gambar = $this->_upload($data['slug']);
			if ($gambar === FALSE) return FALSE;
			if ($produk && $produk->gambar !== 'default.png' && $produk->gambar !== $gambar)
			{
				@unlink($this->upload_path.basename($produk->gambar));
			}
			$data['gambar'] = $gambar;
		}

		if ($produk) return $this->db->update($this->table, $data, ['id' => $produk->id]);
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
	 * Produk yang sudah pernah dipesan tidak dihapus permanen, cukup dinonaktifkan,
	 * supaya riwayat pesanan tetap utuh.
	 */
	public function hapus($produk)
	{
		if ($this->db->where('produk_id', $produk->id)->count_all_results('pesanan_detail') > 0)
		{
			$this->db->update($this->table, ['aktif' => 0], ['id' => $produk->id]);
			return 'nonaktif';
		}
		if ($produk->gambar !== 'default.png') @unlink($this->upload_path.basename($produk->gambar));
		$this->db->delete($this->table, ['id' => $produk->id]);
		return 'hapus';
	}

	public function stok_menipis($batas = 5)
	{
		return $this->db->where(['aktif' => 1, 'stok <=' => $batas])->order_by('stok')->get($this->table)->result();
	}

	private function _slug_unik($nama, $kecuali_id = NULL)
	{
		$dasar = url_title(convert_accented_characters($nama), '-', TRUE);
		$slug = $dasar;
		for ($i = 2; ; $i++)
		{
			$this->db->where('slug', $slug);
			if ($kecuali_id) $this->db->where('id !=', $kecuali_id);
			if ($this->db->count_all_results($this->table) === 0) return $slug;
			$slug = $dasar.'-'.$i;
		}
	}

	private function _upload($nama_file)
	{
		$this->load->library('upload', [
			'upload_path'      => $this->upload_path,
			'allowed_types'    => 'jpg|jpeg|png|webp',
			'max_size'         => 2048,
			'file_name'        => $nama_file,
			'overwrite'        => TRUE,
			'file_ext_tolower' => TRUE,
		]);
		if ( ! $this->upload->do_upload('gambar'))
		{
			$this->upload_error = $this->upload->display_errors('', '');
			return FALSE;
		}

		// Perkecil gambar besar menjadi maksimal 800x800 px memakai library Image Manipulation
		$file = $this->upload->data();
		if ($file['image_width'] > 800 || $file['image_height'] > 800)
		{
			$this->load->library('image_lib', [
				'image_library'  => 'gd2',
				'source_image'   => $file['full_path'],
				'maintain_ratio' => TRUE,
				'width'          => 800,
				'height'         => 800,
			]);
			$this->image_lib->resize();
		}
		return $file['file_name'];
	}
}
