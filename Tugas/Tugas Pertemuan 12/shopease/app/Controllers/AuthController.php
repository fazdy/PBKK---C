<?php
namespace App\Controllers;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    private function input(string $key): string
    {
        $value = $this->request->getPost($key);
        return is_string($value) && mb_check_encoding($value, 'UTF-8') ? $value : '';
    }
    private function destination(string $role): string
    {
        return ['admin'=>'admin','operator'=>'operator','customer'=>''][$role] ?? '';
    }
    private function form(string $name, array $data = [], int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setBody(view('auth/'.$name, array_merge([
            'title'=>($name === 'login' ? 'Masuk' : 'Daftar').' | ShopEase',
            'page'=>$name, 'errors'=>[], 'values'=>[],
        ], $data)));
    }
    public function register()
    {
        if (session()->get('isLoggedIn')) { return redirect()->to(site_url($this->destination(session('role')))); }
        return $this->form('register');
    }
    public function login()
    {
        if (session()->get('isLoggedIn')) { return redirect()->to(site_url($this->destination(session('role')))); }
        return $this->form('login');
    }
    public function store()
    {
        if (session()->get('isLoggedIn')) { return redirect()->to(site_url($this->destination(session('role')))); }
        $data = [
            'name'=>trim($this->input('name')), 'email'=>strtolower(trim($this->input('email'))),
            'phone'=>trim($this->input('phone')), 'address'=>trim($this->input('address')),
            'password'=>$this->input('password'), 'password_confirm'=>$this->input('password_confirm'),
        ];
        $values = array_intersect_key($data, array_flip(['name','email','phone','address']));
        $rules = [
            'name'=>['rules'=>'required|min_length[3]|max_length[100]', 'errors'=>[
                'required'=>'Nama wajib diisi.','min_length'=>'Nama minimal 3 karakter.','max_length'=>'Nama maksimal 100 karakter.']],
            'email'=>['rules'=>'required|valid_email|max_length[100]|is_unique[users.email]', 'errors'=>[
                'required'=>'Email wajib diisi.','valid_email'=>'Format email tidak valid.',
                'max_length'=>'Email maksimal 100 karakter.','is_unique'=>'Email sudah terdaftar.']],
            'password'=>['rules'=>'required|min_length[8]|max_length[72]', 'errors'=>[
                'required'=>'Password wajib diisi.','min_length'=>'Password minimal 8 karakter.','max_length'=>'Password terlalu panjang.']],
            'password_confirm'=>['rules'=>'required|matches[password]', 'errors'=>[
                'required'=>'Konfirmasi password wajib diisi.','matches'=>'Konfirmasi password tidak sama.']],
            'phone'=>['rules'=>'permit_empty|regex_match[/^[0-9+() -]{6,20}$/]', 'errors'=>[
                'regex_match'=>'Nomor telepon harus 6–20 karakter: angka, spasi, +, -, atau tanda kurung.']],
            'address'=>['rules'=>'permit_empty|max_length[500]', 'errors'=>['max_length'=>'Alamat maksimal 500 karakter.']],
        ];
        $valid = $this->validateData($data,$rules);
        $errors = $this->validator->getErrors();
        // Bcrypt PASSWORD_DEFAULT dibatasi 72 byte; hindari pemotongan diam-diam.
        if (strlen($data['password']) > 72 || str_contains($data['password'], "\0")) {
            $errors['password'] = 'Password maksimal 72 byte dan tidak boleh mengandung karakter null.';
        }
        if (!$valid || $errors) { return $this->form('register',compact('values','errors'),422); }
        $model = new UserModel();
        try {
            $saved = $model->insert([
                'name'=>$data['name'],'email'=>$data['email'],
                'password'=>password_hash($data['password'],PASSWORD_DEFAULT),
                'role'=>'customer', // Tidak mengambil role dari formulir/request.
                'phone'=>$data['phone'] ?: null, 'address'=>$data['address'] ?: null,
            ]);
            if ($saved === false) { throw new \RuntimeException('Insert user gagal.'); }
        } catch (\Throwable $e) {
            // Index unik menangani dua registrasi email yang sama secara bersamaan.
            if ($model->where('email',$data['email'])->first()) {
                return $this->form('register',['values'=>$values,'errors'=>['email'=>'Email sudah terdaftar.']],422);
            }
            log_message('error','Registrasi gagal: {message}', ['message'=>$e->getMessage()]);
            return $this->form('register',['values'=>$values,'errors'=>['database'=>'Registrasi belum dapat disimpan. Coba lagi.']],500);
        }
        return redirect()->to(site_url('login'))->with('success','Registrasi berhasil. Silakan login.');
    }
    public function authenticate()
    {
        if (session()->get('isLoggedIn')) { return redirect()->to(site_url($this->destination(session('role')))); }
        $email = strtolower(trim($this->input('email')));
        $password = $this->input('password');
        $values = ['email'=>$email];
        if (!$this->validateData(compact('email','password'),[
            'email'=>['rules'=>'required|valid_email|max_length[100]','errors'=>[
                'required'=>'Email wajib diisi.','valid_email'=>'Format email tidak valid.','max_length'=>'Email terlalu panjang.']],
            'password'=>['rules'=>'required','errors'=>['required'=>'Password wajib diisi.']],
        ])) { return $this->form('login',['values'=>$values,'errors'=>$this->validator->getErrors()],422); }
        $throttle = service('throttler');
        $ipKey = 'login_ip_'.hash('sha256',$this->request->getIPAddress());
        $accountKey = 'login_account_'.hash('sha256',$email.'|'.$this->request->getIPAddress());
        // Token bucket: 5 percobaan per kombinasi akun/IP; terisi ulang selama 60 detik.
        if (!$throttle->check($ipKey,30,60) || !$throttle->check($accountKey,5,60)) {
            $wait = max(1,$throttle->getTokenTime());
            $this->response->setHeader('Retry-After',(string)$wait);
            return $this->form('login',['values'=>$values,'errors'=>['login'=>"Terlalu banyak percobaan. Coba lagi dalam {$wait} detik."]],429);
        }
        $model = new UserModel();
        $user = $model->where('email',$email)->first();
        // Hash dummy menghindari jalur tanpa password_verify untuk email yang tidak ditemukan.
        $hash = $user['password'] ?? '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $matches = strlen($password) <= 72 && !str_contains($password,"\0") && password_verify($password,$hash);
        if (!$user || !$matches || !in_array($user['role'],['customer','admin','operator'],true)) {
            return $this->form('login',['values'=>$values,'errors'=>['login'=>'Email atau password salah.']],401);
        }
        if (password_needs_rehash($user['password'],PASSWORD_DEFAULT)) {
            $model->update($user['id_user'],['password'=>password_hash($password,PASSWORD_DEFAULT)]);
        }
        $throttle->remove($accountKey);
        session()->regenerate(true);
        session()->set([
            'isLoggedIn'=>true,'user_id'=>$user['id_user'],'name'=>$user['name'],
            'email'=>$user['email'],'role'=>$user['role'],
        ]);
        return redirect()->to(site_url($this->destination($user['role'])))->with('success','Login berhasil. Selamat datang di ShopEase.');
    }
    public function logout()
    {
        session()->destroy();
        // Jangan menulis flashdata ke session yang sudah dihancurkan.
        return redirect()->to(site_url('login').'?logout=1');
    }
}
