<?php

declare(strict_types=1);

namespace App\Models;

final class DemoCatalog
{
    public const CATEGORIES = [
        'elektronik' => ['name' => 'Elektronik', 'description' => 'Audio dan gadget', 'icon' => 'headphones', 'tone' => 'blue'],
        'fesyen' => ['name' => 'Fesyen', 'description' => 'Gaya sehari-hari', 'icon' => 'shirt', 'tone' => 'sand'],
        'rumah-tangga' => ['name' => 'Rumah Tangga', 'description' => 'Pelengkap rumah', 'icon' => 'house', 'tone' => 'mint'],
        'aksesori' => ['name' => 'Aksesori', 'description' => 'Teman beraktivitas', 'icon' => 'watch', 'tone' => 'lavender'],
    ];

    public const PRODUCTS = [
        'aura-x1' => [
            'name' => 'Headphone Aura X1', 'category' => 'elektronik', 'collection' => 'new',
            'price' => 499000, 'image' => 'headphone.svg', 'tone' => 'blue',
            'description' => 'Headphone nirkabel dengan bantalan lembut.',
        ],
        'pace' => [
            'name' => 'Sneakers Pace', 'category' => 'fesyen', 'collection' => 'new',
            'price' => 329000, 'image' => 'sneakers.svg', 'tone' => 'sand',
            'description' => 'Sepatu kasual dengan sol empuk untuk aktivitas harian.',
        ],
        'luma' => [
            'name' => 'Lampu Meja Luma', 'category' => 'rumah-tangga', 'collection' => 'new',
            'price' => 189000, 'image' => 'lamp.svg', 'tone' => 'mint',
            'description' => 'Lampu baca berwarna hijau sage dengan cahaya hangat.',
        ],
        'daily' => [
            'name' => 'Tas Kanvas Daily', 'category' => 'aksesori', 'collection' => 'new',
            'price' => 119000, 'image' => 'totebag.svg', 'tone' => 'lavender',
            'description' => 'Tas jinjing kanvas untuk buku dan barang sehari-hari.',
        ],
        'nova-68' => [
            'name' => 'Keyboard Nova 68', 'category' => 'elektronik', 'collection' => 'popular',
            'price' => 379000, 'image' => 'keyboard.svg', 'tone' => 'blue',
            'description' => 'Keyboard ringkas dengan 68 tombol dan aksen biru.',
        ],
        'atlas' => [
            'name' => 'Ransel Atlas', 'category' => 'fesyen', 'collection' => 'popular',
            'price' => 249000, 'image' => 'backpack.svg', 'tone' => 'sand',
            'description' => 'Ransel kasual dengan kompartemen laptop.',
        ],
        'terra-500' => [
            'name' => 'Tumbler Terra 500', 'category' => 'rumah-tangga', 'collection' => 'popular',
            'price' => 149000, 'image' => 'tumbler.svg', 'tone' => 'mint',
            'description' => 'Botol minum berbahan stainless steel, kapasitas 500 ml.',
        ],
        'vela' => [
            'name' => 'Jam Tangan Vela', 'category' => 'aksesori', 'collection' => 'popular',
            'price' => 219000, 'image' => 'watch.svg', 'tone' => 'lavender',
            'description' => 'Jam tangan analog dengan tali warna ungu gelap.',
        ],
    ];
}
