<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\UserModel;
class DashboardController extends BaseController
{
    public function index(): string
    {
        $model = new UserModel();
        return view('auth/dashboard',[
            'title'=>'Dashboard Administrator | ShopEase', 'page'=>'dashboard', 'area'=>'admin',
            'counts'=>[
                'customer'=>$model->where('role','customer')->countAllResults(),
                'admin'=>$model->where('role','admin')->countAllResults(),
                'operator'=>$model->where('role','operator')->countAllResults(),
            ],
            'users'=>$model->select('name,email,role,created_at')->orderBy('id_user','DESC')->findAll(10),
        ]);
    }
}
