<?php
namespace App\Models;

use CodeIgniter\Model;

class ProductImageModel extends Model
{
    protected $table = 'product_images';
    protected $primaryKey = 'id_image';
    protected $allowedFields = ['id_product', 'image', 'is_primary', 'created_at'];

    public function forProduct(int $id): array
    {
        return $this->where('id_product', $id)->orderBy('is_primary', 'DESC')->orderBy('id_image')->findAll();
    }
}
