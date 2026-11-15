<?php
namespace Config;
use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';
    // SQLite memudahkan demo lokal; env.mysql mengganti konfigurasi ke MySQL.
    public array $default = [
        'DSN' => '', 'hostname' => '127.0.0.1', 'username' => '', 'password' => '',
        'database' => 'db_shopease.sqlite', 'DBDriver' => 'SQLite3', 'DBPrefix' => '',
        'pConnect' => false, 'DBDebug' => true, 'charset' => 'utf8mb4',
        'DBCollat' => 'utf8mb4_unicode_ci', 'swapPre' => '', 'encrypt' => false,
        'compress' => false, 'strictOn' => true, 'failover' => [], 'port' => 3306,
        'foreignKeys' => true, 'busyTimeout' => 5000,
        'dateFormat' => ['date'=>'Y-m-d','datetime'=>'Y-m-d H:i:s','time'=>'H:i:s'],
    ];
}
