<?php
namespace App\Controllers\Operator;
use App\Controllers\BaseController;
use App\Models\UserModel;
class DashboardController extends BaseController
{
    public function index(): string
    {
        $model = new UserModel();
        return view('auth/dashboard',[
            'title'=>'Dashboard Operator | ShopEase', 'page'=>'dashboard', 'area'=>'operator',
            'counts'=>[
                'customer'=>$model->where('role','customer')->countAllResults(),
                'admin'=>$model->where('role','admin')->countAllResults(),
                'operator'=>$model->where('role','operator')->countAllResults(),
            ],
            'users'=>[],
        ]);
    }
}
