<?php
namespace App\Controllers;

use CodeIgniter\Controller;

abstract class BaseController extends Controller
{
    protected $helpers = ['url', 'form', 'text', 'management'];

    protected function queryString(string $key, int $length): string
    {
        $value = $this->request->getGet($key);
        return is_string($value) ? mb_substr(trim($value), 0, $length) : '';
    }

    protected function formStrings(array $keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            $value = $this->request->getPost($key);
            $data[$key] = is_string($value) ? trim($value) : '';
        }
        return $data;
    }
}
