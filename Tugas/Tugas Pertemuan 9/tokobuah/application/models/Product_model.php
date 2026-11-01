<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{
	private $_table = 'products';
	private $_upload_path = './upload/product/';

	public $product_id;
	public $name;
	public $price;
	public $image = 'default.jpg';
	public $description;

	/** Pesan error terakhir dari library Upload (kosong jika tidak ada). */
	public $upload_error = '';

	public function rules()
	{
		return [
			['field' => 'name',
			'label' => 'Nama',
			'rules' => 'required|max_length[255]'],

			['field' => 'price',
			'label' => 'Harga',
			'rules' => 'required|is_natural'],

			['field' => 'description',
			'label' => 'Deskripsi',
			'rules' => 'required']
		];
	}

	public function getAll()
	{
		return $this->db->order_by('name', 'ASC')->get($this->_table)->result();
	}

	public function getById($id)
	{
		return $this->db->get_where($this->_table, ['product_id' => $id])->row();
	}

	public function count()
	{
		return $this->db->count_all($this->_table);
	}

	/** Ringkasan untuk halaman overview: rata-rata, termurah, termahal. */
	public function getStats()
	{
		return $this->db->select('AVG(price) AS avg_price, MIN(price) AS min_price, MAX(price) AS max_price')
			->get($this->_table)->row();
	}

	/** Simpan produk baru. Mengembalikan FALSE jika upload foto gagal. */
	public function save()
	{
		$post = $this->input->post();
		$this->product_id = uniqid();
		$this->name = $post['name'];
		$this->price = $post['price'];
		$this->description = $post['description'];

		$this->image = 'default.jpg';
		if ( ! empty($_FILES['image']['name']))
		{
			$this->image = $this->_uploadImage();
			if ($this->image === FALSE) return FALSE;
		}

		return $this->db->insert($this->_table, $this->_row());
	}

	/** Perbarui produk. Foto lama dipertahankan jika tidak memilih foto baru. */
	public function update()
	{
		$post = $this->input->post();
		$this->product_id = $post['id'];
		$this->name = $post['name'];
		$this->price = $post['price'];
		$this->description = $post['description'];

		$this->image = $post['old_image'];
		if ( ! empty($_FILES['image']['name']))
		{
			$new_image = $this->_uploadImage();
			if ($new_image === FALSE) return FALSE;

			// Ekstensi bisa berbeda (mis. .png -> .jpg), jadi file lama dihapus.
			if ($post['old_image'] !== 'default.jpg' && $post['old_image'] !== $new_image)
			{
				@unlink($this->_upload_path.basename($post['old_image']));
			}
			$this->image = $new_image;
		}

		return $this->db->update($this->_table, $this->_row(), ['product_id' => $this->product_id]);
	}

	public function delete($id)
	{
		$this->_deleteImage($id);
		return $this->db->delete($this->_table, ['product_id' => $id]);
	}

	private function _row()
	{
		return [
			'product_id'  => $this->product_id,
			'name'        => $this->name,
			'price'       => $this->price,
			'image'       => $this->image,
			'description' => $this->description,
		];
	}

	private function _uploadImage()
	{
		$config['upload_path']   = $this->_upload_path;
		$config['allowed_types'] = 'gif|jpg|jpeg|png';
		$config['file_name']     = $this->product_id;
		$config['overwrite']     = TRUE;
		$config['max_size']      = 1024; // 1 MB
		$config['file_ext_tolower'] = TRUE;

		$this->load->library('upload', $config);

		if ($this->upload->do_upload('image'))
		{
			return $this->upload->data('file_name');
		}

		$this->upload_error = $this->upload->display_errors('', '');
		return FALSE;
	}

	private function _deleteImage($id)
	{
		$product = $this->getById($id);
		if ($product && $product->image !== 'default.jpg')
		{
			$filename = pathinfo($product->image, PATHINFO_FILENAME);
			array_map('unlink', glob(FCPATH."upload/product/$filename.*"));
		}
	}
}
