<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up()
    {
        $role = $this->db->DBDriver === 'MySQLi'
            ? ['type'=>'ENUM','constraint'=>['customer','admin','operator'],'default'=>'customer']
            : ['type'=>'VARCHAR','constraint'=>20,'default'=>'customer'];
        $this->forge->addField([
            'id_user'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'name'=>['type'=>'VARCHAR','constraint'=>100],
            'email'=>['type'=>'VARCHAR','constraint'=>100],
            'password'=>['type'=>'VARCHAR','constraint'=>255],
            'role'=>$role,
            'phone'=>['type'=>'VARCHAR','constraint'=>20,'null'=>true],
            'address'=>['type'=>'TEXT','null'=>true],
            'created_at'=>['type'=>'DATETIME','null'=>true],
            'updated_at'=>['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id_user', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users');
    }
    public function down() { $this->forge->dropTable('users'); }
}
