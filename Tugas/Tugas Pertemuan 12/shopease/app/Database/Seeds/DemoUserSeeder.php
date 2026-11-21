<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class DemoUserSeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT === 'production') {
            throw new \RuntimeException('Akun demo hanya boleh dibuat di development/testing.');
        }
        $model = new UserModel();
        foreach (['customer'=>'Customer Demo', 'admin'=>'Administrator Demo', 'operator'=>'Operator Demo'] as $role=>$name) {
            $email = $role . '@shopease.test';
            if ($model->where('email',$email)->first()) { continue; }
            $model->insert([
                'name'=>$name, 'email'=>$email,
                'password'=>password_hash('ShopEase!2026', PASSWORD_DEFAULT),
                'role'=>$role, 'phone'=>null, 'address'=>'Alamat contoh, Surabaya',
            ]);
        }
        echo "Tiga akun demo siap. Seeder tidak mengubah akun yang sudah ada.\n";
    }
}
