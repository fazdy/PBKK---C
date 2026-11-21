<?php
namespace App\Libraries;

use App\Models\ProductImageModel;
use App\Models\ProductModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\PageNotFoundException;
use DomainException;
use Throwable;

class ProductCatalog
{
    public const MAX_IMAGES = 6;
    public const MAX_BYTES = 2097152;
    private const TYPES = ['image/jpeg' => ['jpg', 'jpeg'], 'image/png' => ['png'], 'image/webp' => ['webp']];
    private BaseConnection $db;
    private ProductModel $products;
    private ProductImageModel $images;

    public function __construct()
    {
        $this->db = db_connect();
        $this->products = new ProductModel($this->db);
        $this->images = new ProductImageModel($this->db);
    }

    private function transaction(callable $work)
    {
        $this->db->transException(true);
        $this->db->transBegin();
        try {
            $result = $work();
            if (!$this->db->transStatus() || !$this->db->transCommit()) {
                throw new \RuntimeException('Transaksi katalog gagal.');
            }
            return $result;
        } catch (Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    // Kunci satu baris induk sebelum membaca atau mengubah galeri produk.
    private function lockProduct(int $id): void
    {
        if ($this->db->DBDriver === 'MySQLi') {
            $row = $this->db->query('SELECT id_product FROM products WHERE id_product = ? FOR UPDATE', [$id])->getRowArray();
        } else {
            $this->db->query('UPDATE products SET stock = stock WHERE id_product = ?', [$id]);
            $row = $this->products->find($id);
        }
        if (!$row) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    private function validatedFiles(array $files): array
    {
        $valid = [];
        foreach ($files as $file) {
            if (!$file instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
                throw new DomainException('Struktur unggahan tidak valid.');
            }
            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if (!$file->isValid() || $file->getSize() > self::MAX_BYTES) {
                throw new DomainException('Setiap gambar harus berhasil diunggah dan berukuran maksimal 2 MiB.');
            }
            $mime = $file->getMimeType();
            $info = @getimagesize($file->getTempName());
            $ext = strtolower($file->getClientExtension());
            if (!isset(self::TYPES[$mime]) || !in_array($ext, self::TYPES[$mime], true) || ($info['mime'] ?? null) !== $mime) {
                throw new DomainException('Gunakan gambar JPG, PNG, atau WebP dengan ekstensi yang cocok.');
            }
            $valid[] = [$file, self::TYPES[$mime][0]];
        }
        return $valid;
    }

    public function save(?int $id, array $data, array $uploads): int
    {
        $files = $this->validatedFiles($uploads);
        $written = [];
        try {
            return $this->transaction(function () use ($id, $data, $files, &$written): int {
                if ($id === null) {
                    $data['slug'] = pm_slug($data['name']);
                    $id = (int) $this->products->insert($data);
                } else {
                    $this->lockProduct($id);
                    $this->products->update($id, $data);
                }
                $count = $this->images->where('id_product', $id)->countAllResults();
                if ($count + count($files) > self::MAX_IMAGES) {
                    throw new DomainException('Satu produk dapat memiliki maksimal 6 gambar.');
                }
                foreach ($files as [$file, $extension]) {
                    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
                    $file->move(FCPATH . 'uploads/products', $filename);
                    $written[] = $filename;
                    $this->images->insert([
                        'id_product' => $id, 'image' => $filename, 'is_primary' => $count++ === 0 ? 1 : 0,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                return $id;
            });
        } catch (Throwable $e) {
            $this->removeFiles($written);
            throw $e;
        }
    }

    public function changeImage(int $id, int $imageId, bool $remove): void
    {
        $discard = $this->transaction(function () use ($id, $imageId, $remove) {
            $this->lockProduct($id);
            $image = $this->images->where('id_product', $id)->find($imageId);
            if (!$image) {
                throw PageNotFoundException::forPageNotFound();
            }
            if (!$remove) {
                $this->images->where('id_product', $id)->set(['is_primary' => 0])->update();
                $this->images->update($imageId, ['is_primary' => 1]);
                return null;
            }
            $this->images->delete($imageId);
            if ((int) $image['is_primary'] === 1) {
                $next = $this->images->where('id_product', $id)->orderBy('id_image')->first();
                if ($next) {
                    $this->images->update($next['id_image'], ['is_primary' => 1]);
                }
            }
            return $image['image'];
        });
        if ($discard !== null) {
            $this->removeFiles([$discard]);
        }
    }

    public function delete(int $id): void
    {
        $discard = $this->transaction(function () use ($id) {
            $this->lockProduct($id);
            $images = $this->images->forProduct($id);
            $this->products->delete($id);
            return array_column($images, 'image');
        });
        $this->removeFiles($discard);
    }

    private function removeFiles(array $names): void
    {
        foreach ($names as $name) {
            $path = FCPATH . 'uploads/products/' . basename($name);
            if (is_file($path) && !@unlink($path)) {
                log_message('error', 'Gagal membersihkan file produk {file}', ['file' => basename($name)]);
            }
        }
    }
}
