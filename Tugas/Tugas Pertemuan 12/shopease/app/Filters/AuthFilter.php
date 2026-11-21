<?php
namespace App\Filters;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('url');
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(site_url('login'))->with('error','Silakan login terlebih dahulu.');
        }
        // Baca ulang akun: perubahan role/penghapusan akun berlaku pada request berikutnya.
        $user = (new UserModel())->find($session->get('user_id'));
        if (!$user || !in_array($user['role'], ['customer','admin','operator'], true)) {
            $session->destroy();
            return redirect()->to(site_url('login'));
        }
        $session->set(['name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']]);
        if ($arguments && !in_array($user['role'], $arguments, true)) {
            return service('response')->setStatusCode(403)->setBody(view('auth/forbidden',[
                'title'=>'Akses ditolak | ShopEase', 'page'=>'account',
            ]));
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
