<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductManagement extends Migration
{
    public function up()
    {
        $id = ['type' => 'INT', 'auto_increment' => true];
        $text = ['type' => 'TEXT', 'null' => true];
        $time = ['type' => 'DATETIME', 'null' => true];
        $this->forge->addField([
            'id_category' => $id, 'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 120], 'description' => $text,
            'created_at' => $time, 'updated_at' => $time,
        ]);
        $this->forge->addPrimaryKey('id_category');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('categories', true);

        $this->forge->addField([
            'id_product' => $id, 'id_category' => ['type' => 'INT'],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 180], 'description' => $text,
            'price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'stock' => ['type' => 'INT', 'default' => 0],
            'status' => ['type' => 'ENUM', 'constraint' => ['tersedia', 'tidak_tersedia'], 'default' => 'tersedia'],
            'created_at' => $time, 'updated_at' => $time,
        ]);
        $this->forge->addPrimaryKey('id_product');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('id_category');
        $this->forge->addKey('stock');
        $this->forge->addForeignKey('id_category', 'categories', 'id_category', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('products', true);

        $this->forge->addField([
            'id_image' => $id, 'id_product' => ['type' => 'INT'],
            'image' => ['type' => 'VARCHAR', 'constraint' => 255],
            'is_primary' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => $time,
        ]);
        $this->forge->addPrimaryKey('id_image');
        $this->forge->addUniqueKey('image');
        $this->forge->addKey(['id_product', 'is_primary']);
        $this->forge->addForeignKey('id_product', 'products', 'id_product', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_images', true);
    }

    public function down()
    {
        $this->forge->dropTable('product_images');
        $this->forge->dropTable('products');
        $this->forge->dropTable('categories');
    }
}
