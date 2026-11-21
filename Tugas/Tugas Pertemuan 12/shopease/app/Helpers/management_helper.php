<?php
function pm_rupiah($amount): string
{
    $amount = (float) $amount;
    return 'Rp ' . number_format($amount, round($amount, 2) == floor($amount) ? 0 : 2, ',', '.');
}
function pm_cover(?string $file): string
{
    return base_url($file ? 'uploads/products/' . rawurlencode($file) : 'assets/img/brand.svg');
}
function pm_quantity(int $stock): string
{
    if ($stock === 0) {
        return 'Habis';
    }
    return $stock <= \App\Models\ProductModel::LOW_STOCK ? 'Rendah' : 'Cukup';
}
function pm_slug(string $name): string
{
    $base = url_title(convert_accented_characters($name), '-', true);
    return ($base ?: 'item') . '-' . bin2hex(random_bytes(5));
}
