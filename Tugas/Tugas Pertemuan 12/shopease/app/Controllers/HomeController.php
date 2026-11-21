<?php
namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;

class HomeController extends BaseController
{
    public function index(): string
    {
        $products = (new ProductModel())->catalog()->where('status', 'tersedia')->orderBy('products.id_product', 'DESC')->findAll(4);
        return view('frontend/home', [
            'title' => 'ShopEase | Toko Online', 'page' => 'home',
            'categories' => (new CategoryModel())->orderBy('name')->findAll(),
            'featured' => $products[0] ?? null, 'newProducts' => $products,
        ]);
    }
    public function catalog(): string
    {
        $query = $this->queryString('q', 150);
        $category = $this->queryString('kategori', 120);
        $categories = array_column((new CategoryModel())->orderBy('name')->findAll(), null, 'slug');
        if (!isset($categories[$category])) {
            $category = '';
        }
        return view('frontend/catalog', ['title' => 'Katalog | ShopEase', 'page' => 'catalog',
            'products' => (new ProductModel())->catalog($query, $category)->where('status', 'tersedia')->orderBy('products.id_product', 'DESC')->findAll(),
            'query' => $query, 'category' => $category, 'categories' => $categories]);
    }
    public function about(): string
    {
        return view('frontend/about', ['title' => 'Tentang | ShopEase', 'page' => 'about']);
    }
    public function contact(): string
    {
        return view('frontend/contact', ['title' => 'Kontak | ShopEase', 'page' => 'contact', 'email' => 'halo@shopease.example']);
    }
}
