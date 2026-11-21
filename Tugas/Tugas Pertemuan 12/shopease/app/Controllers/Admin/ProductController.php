<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ProductCatalog;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\ProductImageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use DomainException;

class ProductController extends BaseController
{
    public function dashboard()
    {
        $model = new ProductModel();
        return view('admin/dashboard', [
            'title' => 'Ringkasan katalog', 'stats' => $model->stats(),
            'categories' => (new CategoryModel())->withCounts(),
            'attention' => $model->catalog()->where('stock <=', ProductModel::LOW_STOCK)
                ->orderBy('stock')->orderBy('products.id_product')->findAll(5),
        ]);
    }
    public function index()
    {
        $q = $this->queryString('q', 150);
        $category = $this->queryString('kategori', 120);
        $page = filter_var($this->request->getGet('page'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
        $model = new ProductModel();
        $model->catalog($q, $category)->orderBy('products.id_product', 'DESC');
        $total = $model->countAllResults(false);
        $pages = max(1, (int) ceil($total / ProductModel::PER_PAGE));
        $page = min($page, $pages);
        return view('admin/products/index', [
            'title' => 'Daftar produk', 'products' => $model->findAll(ProductModel::PER_PAGE, ($page - 1) * ProductModel::PER_PAGE),
            'categories' => (new CategoryModel())->orderBy('name')->findAll(),
            'total' => $total, 'pageNum' => $page, 'pages' => $pages, 'q' => $q, 'category' => $category,
        ]);
    }
    private function product(int $id): array
    {
        return (new ProductModel())->catalog()->where('products.id_product', $id)->first() ?? throw PageNotFoundException::forPageNotFound();
    }
    public function show(int $id)
    {
        return view('admin/products/show', ['title' => 'Detail produk', 'product' => $this->product($id), 'images' => (new ProductImageModel())->forProduct($id)]);
    }
    public function create()
    {
        return view('admin/products/create', ['title' => 'Tambah produk', 'product' => [], 'images' => [], 'categories' => (new CategoryModel())->orderBy('name')->findAll()]);
    }
    public function edit(int $id)
    {
        return view('admin/products/edit', ['title' => 'Edit produk', 'product' => $this->product($id), 'images' => (new ProductImageModel())->forProduct($id), 'categories' => (new CategoryModel())->orderBy('name')->findAll()]);
    }
    public function store()
    {
        return $this->save(null);
    }
    public function update(int $id)
    {
        $this->product($id);
        return $this->save($id);
    }
    private function save(?int $id)
    {
        $data = $this->formStrings(['name', 'id_category', 'price', 'stock', 'status', 'description']);
        $rules = [
            'name' => ['label' => 'Nama produk', 'rules' => 'required|min_length[3]|max_length[150]', 'errors' => ['required' => 'Isi nama produk.', 'min_length' => 'Nama produk minimal 3 karakter.', 'max_length' => 'Nama produk maksimal 150 karakter.']],
            'id_category' => ['rules' => 'required|is_natural_no_zero|is_not_unique[categories.id_category]', 'errors' => ['required' => 'Pilih kategori produk.', 'is_natural_no_zero' => 'Pilih kategori yang tersedia.', 'is_not_unique' => 'Kategori tidak ditemukan.']],
            'price' => ['rules' => 'required|regex_match[/^\d{1,10}(\.\d{1,2})?$/]', 'errors' => ['required' => 'Isi harga produk.', 'regex_match' => 'Harga harus nonnegatif, maksimal 10 digit dan 2 angka desimal.']],
            'stock' => ['rules' => 'required|is_natural|less_than_equal_to[2147483647]', 'errors' => ['required' => 'Isi jumlah stok.', 'is_natural' => 'Stok harus bilangan bulat nonnegatif.', 'less_than_equal_to' => 'Jumlah stok terlalu besar.']],
            'status' => ['rules' => 'required|in_list[tersedia,tidak_tersedia]', 'errors' => ['required' => 'Pilih status produk.', 'in_list' => 'Status produk tidak dikenali.']],
            'description' => ['rules' => 'permit_empty|max_length[4000]', 'errors' => ['max_length' => 'Deskripsi maksimal 4.000 karakter.']],
        ];
        $back = site_url('admin/products/' . ($id ? "edit/$id" : 'create'));
        if (!$this->validateData($data, $rules)) {
            return redirect()->to($back)->withInput()->with('errors', $this->validator->getErrors());
        }
        try {
            (new ProductCatalog())->save($id, $data, $this->request->getFileMultiple('images') ?? []);
        } catch (DomainException $e) {
            return redirect()->to($back)->withInput()->with('error', $e->getMessage());
        } catch (DatabaseException|HTTPException $e) {
            log_message('error', 'Penyimpanan produk gagal: {message}', ['message' => $e->getMessage()]);
            return redirect()->to($back)->withInput()->with('error', 'Produk belum tersimpan. Periksa kategori dan izin direktori unggahan, lalu ulangi.');
        }
        return redirect()->to(site_url('admin/products'))->with('success', $id ? 'Produk berhasil diperbarui.' : 'Produk berhasil ditambahkan.');
    }
    public function delete(int $id)
    {
        (new ProductCatalog())->delete($id);
        return redirect()->to(site_url('admin/products'))->with('success', 'Produk dan gambarnya berhasil dihapus.');
    }
    public function primary(int $id, int $imageId)
    {
        (new ProductCatalog())->changeImage($id, $imageId, false);
        return redirect()->to(site_url("admin/products/edit/$id"))->with('success', 'Gambar utama diperbarui.');
    }
    public function removeImage(int $id, int $imageId)
    {
        (new ProductCatalog())->changeImage($id, $imageId, true);
        return redirect()->to(site_url("admin/products/edit/$id"))->with('success', 'Gambar dihapus.');
    }
    public function inventory()
    {
        $filter = $this->queryString('stok', 10);
        if (!in_array($filter, ['low', 'empty'], true)) {
            $filter = '';
        }
        $model = new ProductModel();
        return view('admin/inventory', ['title' => 'Inventori', 'filter' => $filter, 'stats' => $model->stats(),
            'products' => $model->catalog('', '', $filter)->orderBy('stock')->orderBy('products.name')->findAll()]);
    }
    public function stock(int $id)
    {
        $this->product($id);
        $data = $this->formStrings(['stock', 'previous', 'stok']);
        $back = site_url('admin/inventory') . (in_array($data['stok'], ['low', 'empty'], true) ? '?stok=' . $data['stok'] : '');
        if (!$this->validateData($data, ['stock' => 'required|is_natural|less_than_equal_to[2147483647]', 'previous' => 'required|is_natural'])) {
            return redirect()->to($back)->with('error', 'Masukkan jumlah stok yang valid.');
        }
        $model = new ProductModel();
        $model->where('id_product', $id)->where('stock', $data['previous'])->set(['stock' => (int) $data['stock']])->update();
        $current = $model->find($id);
        if (!$current || (int) $current['stock'] !== (int) $data['stock']) {
            return redirect()->to($back)->with('error', 'Stok telah berubah. Periksa jumlah terbaru dan ulangi.');
        }
        return redirect()->to($back)->with('success', 'Stok berhasil diperbarui.');
    }
}
