<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DemoCatalog;

final class HomeController extends BaseController
{
    public function index(): string
    {
        $products = DemoCatalog::PRODUCTS;

        return view('frontend/home', [
            'title' => 'ShopEase | Toko Online',
            'page' => 'home',
            'categories' => DemoCatalog::CATEGORIES,
            'featured' => $products['aura-x1'],
            'newProducts' => array_filter($products, static fn (array $product): bool => $product['collection'] === 'new'),
            'popularProducts' => array_filter($products, static fn (array $product): bool => $product['collection'] === 'popular'),
        ]);
    }

    public function catalog(): string
    {
        $query = $this->request->getGet('q');
        $query = is_string($query) && mb_check_encoding($query, 'UTF-8')
            ? mb_substr(trim($query), 0, 80, 'UTF-8')
            : '';

        $categories = DemoCatalog::CATEGORIES;
        $category = $this->request->getGet('kategori');
        $category = is_string($category) && isset($categories[$category]) ? $category : '';

        $products = array_filter(DemoCatalog::PRODUCTS, static function (array $product) use ($query, $category): bool {
            $matchesCategory = $category === '' || $product['category'] === $category;
            $searchText = $product['name'] . ' ' . $product['description'];

            return $matchesCategory && ($query === '' || mb_stripos($searchText, $query, 0, 'UTF-8') !== false);
        });

        return view('frontend/catalog', [
            'title' => 'Katalog | ShopEase',
            'page' => 'catalog',
            'categories' => $categories,
            'products' => $products,
            'query' => $query,
            'category' => $category,
        ]);
    }

    public function about(): string
    {
        return view('frontend/about', [
            'title' => 'Tentang | ShopEase',
            'page' => 'about',
        ]);
    }

    public function contact(): string
    {
        return view('frontend/contact', [
            'title' => 'Kontak | ShopEase',
            'page' => 'contact',
            'email' => 'halo@shopease.example',
        ]);
    }
}
