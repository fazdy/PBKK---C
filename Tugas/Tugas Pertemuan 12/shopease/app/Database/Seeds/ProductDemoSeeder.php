<?php
namespace App\Database\Seeds;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\ProductImageModel;
use CodeIgniter\Database\Seeder;

class ProductDemoSeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT === 'production') {
            throw new \RuntimeException('Seeder hanya untuk praktikum lokal.');
        }
        $categories = new CategoryModel();
        $products = new ProductModel();
        $images = new ProductImageModel();
        $categoryIds = [];
        $definitions = [
            'fashion' => ['Fashion', 'Sepatu, tas, dan pelengkap busana.'],
            'elektronik' => ['Elektronik', 'Perangkat audio dan aksesori meja kerja.'],
            'olahraga' => ['Olahraga', 'Perlengkapan untuk aktivitas fisik.'],
            'gaya-hidup' => ['Gaya Hidup', 'Perlengkapan sehari-hari dan rumah.'],
        ];
        $items = [
            ['Sepatu Sneakers', 'sepatu-sneakers', 'fashion', 'Sneakers kasual bersol empuk.', 299000, 15, 'tersedia', 'sneakers'],
            ['Headphone Bluetooth', 'headphone-bluetooth', 'elektronik', 'Headphone nirkabel dengan bantalan lembut.', 350000, 10, 'tersedia', 'headphone'],
            ['Tumbler Trail 500', 'tumbler-trail', 'olahraga', 'Botol minum untuk latihan dan perjalanan.', 149000, 3, 'tersedia', 'tumbler'],
            ['Ransel Atlas', 'ransel-atlas', 'fashion', 'Ransel harian dengan kompartemen laptop.', 249000, 0, 'tidak_tersedia', 'backpack'],
            ['Keyboard Nova 68', 'keyboard-nova', 'elektronik', 'Keyboard ringkas untuk meja kerja.', 379000, 4, 'tersedia', 'keyboard'],
            ['Tas Kanvas Daily', 'tas-kanvas', 'fashion', 'Tas jinjing kanvas dengan pegangan lebar.', 119000, 12, 'tersedia', 'totebag'],
            ['Lampu Meja Luma', 'lampu-luma', 'gaya-hidup', 'Lampu baca untuk meja belajar.', 189000, 0, 'tidak_tersedia', 'lamp'],
            ['Jam Tangan Vela', 'jam-vela', 'gaya-hidup', 'Jam tangan analog bertali ungu.', 219000, 7, 'tersedia', 'watch'],
        ];
        foreach ($definitions as $slug => [$name, $description]) {
            $row = $categories->where('slug', $slug)->first();
            $categoryIds[$slug] = $row['id_category'] ?? $categories->insert(compact('name', 'slug', 'description'));
        }
        foreach ($items as [$name, $slug, $category, $description, $price, $stock, $status, $art]) {
            if ($products->where('slug', $slug)->first()) {
                continue;
            }
            $filename = bin2hex(random_bytes(16)) . '.webp';
            $destination = FCPATH . 'uploads/products/' . $filename;
            $this->db->transException(true)->transBegin();
            try {
                $id = $products->insert(compact('name', 'slug', 'description', 'price', 'stock', 'status') + ['id_category' => $categoryIds[$category]]);
                if (!copy(APPPATH . "Database/Seeds/images/$art.webp", $destination)) {
                    throw new \RuntimeException('Gagal menyalin ilustrasi awal.');
                }
                $images->insert(['id_product' => $id, 'image' => $filename, 'is_primary' => 1, 'created_at' => date('Y-m-d H:i:s')]);
                $this->db->transCommit();
            } catch (\Throwable $e) {
                $this->db->transRollback();
                if (is_file($destination)) {
                    unlink($destination);
                }
                throw $e;
            }
        }
    }
}
