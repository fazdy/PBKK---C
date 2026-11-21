<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;

class CategoryController extends BaseController
{
    public function index()
    {
        return view('admin/categories/index', ['title' => 'Kategori', 'categories' => (new CategoryModel())->withCounts()]);
    }
    public function create()
    {
        return view('admin/categories/create', ['title' => 'Tambah kategori', 'category' => []]);
    }
    private function category(int $id): array
    {
        return (new CategoryModel())->find($id) ?? throw PageNotFoundException::forPageNotFound();
    }
    public function edit(int $id)
    {
        return view('admin/categories/edit', ['title' => 'Edit kategori', 'category' => $this->category($id)]);
    }
    public function store()
    {
        return $this->save(null);
    }
    public function update(int $id)
    {
        $this->category($id);
        return $this->save($id);
    }
    private function save(?int $id)
    {
        $data = $this->formStrings(['name', 'description']);
        if (!$this->validateData($data, ['name' => 'required|min_length[3]|max_length[100]', 'description' => 'permit_empty|max_length[4000]'])) {
            return redirect()->to(site_url('admin/categories/' . ($id ? "edit/$id" : 'create')))
                ->withInput()->with('error', 'Nama kategori harus 3–100 karakter. Deskripsi maksimal 4.000 karakter.');
        }
        $model = new CategoryModel();
        if ($id === null) {
            $model->insert($data + ['slug' => pm_slug($data['name'])]);
        } else {
            $model->update($id, $data);
        }
        return redirect()->to(site_url('admin/categories'))->with('success', $id ? 'Kategori berhasil diperbarui.' : 'Kategori berhasil ditambahkan.');
    }
    public function delete(int $id)
    {
        $this->category($id);
        $back = redirect()->to(site_url('admin/categories'));
        if ((new ProductModel())->where('id_category', $id)->countAllResults() > 0) {
            return $back->with('error', 'Kategori masih memiliki produk. Pindahkan produknya terlebih dahulu.');
        }
        try {
            (new CategoryModel())->delete($id);
        } catch (DatabaseException $e) {
            if ((new ProductModel())->where('id_category', $id)->countAllResults() === 0) {
                throw $e;
            }
            return $back->with('error', 'Kategori masih memiliki produk.');
        }
        return $back->with('success', 'Kategori berhasil dihapus.');
    }
}
