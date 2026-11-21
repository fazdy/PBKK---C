<?php
namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id_category';
    protected $allowedFields = ['name', 'slug', 'description'];
    protected $useTimestamps = true;

    public function withCounts(): array
    {
        return $this->select('categories.*, COUNT(products.id_product) AS product_count')
            ->join('products', 'products.id_category = categories.id_category', 'left')
            ->groupBy('categories.id_category')->orderBy('categories.name')->findAll();
    }
}
