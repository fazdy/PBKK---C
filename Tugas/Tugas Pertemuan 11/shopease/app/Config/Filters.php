<?php
namespace Config;
use CodeIgniter\Config\Filters as BaseFilters;
class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf' => \CodeIgniter\Filters\CSRF::class,
        'invalidchars' => \CodeIgniter\Filters\InvalidChars::class,
        'secureheaders' => \CodeIgniter\Filters\SecureHeaders::class,
        'auth' => \App\Filters\AuthFilter::class,
        'nocache' => \App\Filters\NoCacheFilter::class,
    ];
    // Halaman akun tidak disimpan dalam page cache.
    public array $required = ['before'=>[], 'after'=>[]];
    public array $globals = [
        'before'=>['invalidchars','csrf'],
        'after'=>['secureheaders','nocache'],
    ];
    public array $methods = [];
    public array $filters = [];
}
