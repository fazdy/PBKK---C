<?php
namespace App\Controllers;
use App\Models\UserModel;
class AccountController extends BaseController
{
    public function index(): string
    {
        return view('auth/account',[
            'title'=>'Akun saya | ShopEase','page'=>'account',
            'user'=>(new UserModel())->find(session('user_id')),
        ]);
    }
}
