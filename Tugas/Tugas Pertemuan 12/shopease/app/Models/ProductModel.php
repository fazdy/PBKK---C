<?php
namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    public const LOW_STOCK = 5;
    public const PER_PAGE = 5;
    protected $table = 'products';
    protected $primaryKey = 'id_product';
    protected $allowedFields = ['id_category', 'name', 'slug', 'description', 'price', 'stock', 'status'];
    protected $useTimestamps = true;

    public function catalog(string $q = '', string $category = '', string $inventory = ''): self
    {
        $this->select('products.*, categories.name AS category_name, categories.slug AS category_slug, product_images.image AS cover')
            ->join('categories', 'categories.id_category = products.id_category')
            ->join('product_images', 'product_images.id_product = products.id_product AND product_images.is_primary = 1', 'left');
        if ($q !== '') {
            $this->like('products.name', $q);
        }
        if ($category !== '') {
            $this->where('categories.slug', $category);
        }
        if ($inventory === 'low') {
            $this->where('stock >=', 1)->where('stock <=', self::LOW_STOCK);
        } elseif ($inventory === 'empty') {
            $this->where('stock', 0);
        }
        return $this;
    }

    public function stats(): array
    {
        return $this->select("COUNT(*) AS total, COUNT(CASE WHEN status = 'tersedia' THEN 1 END) AS active,
            COUNT(CASE WHEN status = 'tidak_tersedia' THEN 1 END) AS inactive,
            COUNT(CASE WHEN stock BETWEEN 1 AND " . self::LOW_STOCK . " THEN 1 END) AS low,
            COUNT(CASE WHEN stock = 0 THEN 1 END) AS empty_stock", false)->first();
    }
}
