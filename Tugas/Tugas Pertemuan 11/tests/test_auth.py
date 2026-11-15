#!/usr/bin/env python3
"""Uji integrasi CI4 melalui PHP CGI. Tidak perlu server/port atau paket Python tambahan."""
from pathlib import Path
import argparse, datetime, html, http.cookies, json, os, re, shutil, sqlite3, subprocess, tempfile
from urllib.parse import urlencode, urlsplit

TASK = Path(__file__).resolve().parents[1]
APP = TASK / 'shopease'
MYSQL_SOCKET = os.environ.get('SHOPEASE_TEST_MYSQL_SOCKET')
DB_ENV = {'database.default.DBDriver':'MySQLi' if MYSQL_SOCKET else 'SQLite3',
          'database.default.database':'db_shopease_test' if MYSQL_SOCKET else 'db_shopease.sqlite'}
if MYSQL_SOCKET:
    DB_ENV.update({'database.default.hostname':MYSQL_SOCKET, 'database.default.username':'root', 'database.default.password':''})

class MySQLResult(list):
    def fetchone(self): return self[0] if self else None

class MySQLDatabase:
    def execute(self,sql,params=()):
        proc=subprocess.run(['php',str(TASK/'tests/mysql_query.php')],input=json.dumps({'sql':sql,'params':params}),
                            text=True,capture_output=True,env={**os.environ,**DB_ENV})
        if proc.returncode: raise RuntimeError(proc.stdout+proc.stderr)
        return MySQLResult(json.loads(proc.stdout))
    def commit(self): pass  # mysqli autocommit
    def close(self): pass


class Client:
    def __init__(self, writable, ip='127.0.0.1'):
        self.writable, self.ip, self.cookies = writable, ip, {}
    def request(self, path, data=None):
        payload = urlencode(data or {}).encode() if data is not None else b''
        env = os.environ.copy()
        env.update({
            'SHOPEASE_WRITABLE_DIR':str(self.writable), 'CI_ENVIRONMENT':'development',
            **DB_ENV,
            'app.baseURL':'http://localhost:8080/', 'REDIRECT_STATUS':'200',
            'GATEWAY_INTERFACE':'CGI/1.1','REQUEST_METHOD':'POST' if data is not None else 'GET',
            'SCRIPT_FILENAME':str(APP/'public/index.php'),'SCRIPT_NAME':'/index.php',
            'REQUEST_URI':path,'QUERY_STRING':urlsplit(path).query,
            'SERVER_NAME':'localhost','SERVER_PORT':'8080','SERVER_PROTOCOL':'HTTP/1.1',
            'HTTP_HOST':'localhost:8080','REMOTE_ADDR':self.ip,
            'CONTENT_TYPE':'application/x-www-form-urlencoded','CONTENT_LENGTH':str(len(payload)),
            'HTTP_COOKIE':'; '.join(f'{k}={v}' for k,v in self.cookies.items()),
        })
        proc = subprocess.run(['php-cgi'],input=payload,env=env,cwd=APP,capture_output=True,check=False)
        raw_headers, sep, body = proc.stdout.partition(b'\r\n\r\n')
        assert sep, proc.stdout.decode(errors='replace')[:1000]
        headers = {}
        for line in raw_headers.decode().splitlines():
            if ': ' not in line: continue
            key,value=line.split(': ',1);headers[key.lower()]=value
            if key.lower()=='set-cookie':
                jar=http.cookies.SimpleCookie();jar.load(value)
                for name,morsel in jar.items(): self.cookies[name]=morsel.value
        return {'status':int(headers.get('status','200').split()[0]),'headers':headers,'body':body.decode()}
    def form(self,path,data):
        page=self.request(path)
        token=re.search(r'name="shopease_csrf" value="([^"]+)"',page['body'])
        assert token, f'Token tidak ditemukan pada {path}: {page["status"]}'
        return self.request(path,{**data,'shopease_csrf':token.group(1)})
    def login(self,role='customer',password='ShopEase!2026'):
        return self.form('/login',{'email':role+'@shopease.test','password':password})
    def follow(self,response):
        url=response['headers']['location'];parts=urlsplit(url)
        return self.request(parts.path+('?' + parts.query if parts.query else ''))
    def logout(self,from_path):
        page=self.request(from_path)
        token=re.search(r'name="shopease_csrf" value="([^"]+)"',page['body']).group(1)
        return self.request('/logout',{'shopease_csrf':token})

def run(output=None):
    results=[]
    def check(name,ok):
        results.append({'name':name,'passed':bool(ok)})
        print(('PASS' if ok else 'FAIL')+'  '+name,flush=True)
    with tempfile.TemporaryDirectory(prefix='shopease11-tests-') as tmp:
        writable=Path(tmp)
        for name in ['cache','logs','session','uploads','debugbar']:(writable/name).mkdir()
        env={**os.environ,'SHOPEASE_WRITABLE_DIR':str(writable),'CI_ENVIRONMENT':'development',
             **DB_ENV}
        for command in [['php','spark','migrate'],['php','spark','db:seed','DemoUserSeeder']]:
            proc=subprocess.run(command,cwd=APP,env=env,capture_output=True,text=True)
            if proc.returncode: raise RuntimeError(proc.stdout+proc.stderr)
        db=MySQLDatabase() if MYSQL_SOCKET else sqlite3.connect(writable/'db_shopease.sqlite')
        if not MYSQL_SOCKET: db.row_factory=sqlite3.Row
        c=Client(writable)
        for path in ['/','/catalog','/about','/contact','/register','/login']:
            check('GET '+path+' merespons 200',c.request(path)['status']==200)
        check('URL tidak terdaftar merespons 404',c.request('/tidak-ada')['status']==404)
        guest=c.request('/admin')
        check('Tamu diarahkan ke login',guest['status']==302 and guest['headers'].get('location','').endswith('/login'))
        base={'name':'Customer Pengujian','email':'student@shopease.test','password':'Test!2026','password_confirm':'Test!2026','phone':'081234567890','address':'Alamat contoh'}
        r=c.form('/register',{**base,'password':'123','password_confirm':'123'})
        check('Password pendek ditolak',r['status']==422 and 'Password minimal 8 karakter.' in r['body'])
        r=c.form('/register',{**base,'password_confirm':'berbeda123'})
        check('Konfirmasi password berbeda ditolak',r['status']==422 and 'Konfirmasi password tidak sama.' in r['body'])
        r=c.form('/register',{**base,'email':'bukan-email'})
        check('Format email invalid ditolak',r['status']==422 and 'Format email tidak valid.' in r['body'])
        r=c.form('/register',{**base,'phone':'abcde'})
        check('Telepon invalid ditolak',r['status']==422 and 'Nomor telepon' in r['body'])
        r=c.form('/register',{**base,'password':'é'*40,'password_confirm':'é'*40})
        check('Batas password 72 byte ditegakkan',r['status']==422 and '72 byte' in r['body'])
        r=c.form('/register',{**base,'name':'<script>alert(1)</script>','email':' STUDENT@SHOPEASE.TEST ','role':'admin'})
        check('Registrasi valid berhasil dan redirect login',r['status']==303 and r['headers'].get('location','').endswith('/login'))
        row=db.execute('SELECT * FROM users WHERE email=?',('student@shopease.test',)).fetchone()
        check('Normalisasi email sebelum simpan',row is not None)
        check('Manipulasi role registrasi tetap customer',row is not None and row['role']=='customer')
        check('Password tersimpan sebagai hash',row is not None and row['password']!='Test!2026' and row['password'].startswith('$2y$'))
        check('Timestamp registrasi tersimpan',row is not None and bool(row['created_at']) and bool(row['updated_at']))
        r=c.form('/register',{**base,'email':' STUDENT@SHOPEASE.TEST '})
        check('Email duplikat dengan huruf besar/spasi ditolak',r['status']==422 and 'Email sudah terdaftar.' in r['body'])
        check('Password tidak diisi kembali di HTML', 'value="Test!2026"' not in r['body'])
        malformed={k:v for k,v in base.items() if k!='name'};malformed['name[]']='A'
        check('Input array tidak menyebabkan error 500',c.form('/register',malformed)['status']==422)
        check('POST tanpa CSRF ditolak',c.request('/login',{'email':'customer@shopease.test','password':'ShopEase!2026'})['status']==403)
        r=c.login(password='salah12345')
        check('Password salah ditolak',r['status']==401 and 'Email atau password salah.' in r['body'])
        wrong=c.form('/login',{'email':'unknown@shopease.test','password':'salah12345'})
        check('Email tidak dikenal mendapat pesan generik sama',wrong['status']==401 and 'Email atau password salah.' in wrong['body'])
        old_cookie=c.cookies.copy()
        r=c.login()
        check('Login customer diarahkan ke beranda',r['status']==303 and r['headers'].get('location')=='http://localhost:8080/')
        check('Session ID berubah setelah login',c.cookies.get('shopease11_session')!=old_cookie.get('shopease11_session'))
        check('Profil customer dapat dibuka',c.request('/account')['status']==200)
        check('Customer ditolak masuk admin',c.request('/admin')['status']==403)
        check('Customer ditolak masuk operator',c.request('/operator')['status']==403)
        check('GET logout tidak mengakhiri sesi',c.request('/logout')['status']==404 and c.request('/account')['status']==200)
        r=c.logout('/account');check('Logout POST berhasil',r['status']==303 and '?logout=1' in r['headers'].get('location',''))
        check('Akses setelah logout diarahkan ke login',c.request('/account')['status']==302)
        stale=Client(writable);stale.cookies=old_cookie
        check('Session sebelum login tidak dapat dipakai ulang',stale.request('/account')['status']==302)
        admin=Client(writable,ip='127.0.0.2');r=admin.login('admin')
        check('Login admin diarahkan ke dashboard',r['status']==303 and r['headers'].get('location','').endswith('/admin'))
        view=admin.request('/admin')
        check('Dashboard admin menampilkan data nyata users',view['status']==200 and 'student@shopease.test' in view['body'])
        check('Nama pengguna di-escape untuk mencegah XSS','<script>alert(1)</script>' not in view['body'] and '&lt;script&gt;alert(1)&lt;/script&gt;' in view['body'])
        check('Halaman akun tidak di-cache','no-store' in view['headers'].get('cache-control',''))
        check('Admin tidak otomatis mendapat role operator',admin.request('/operator')['status']==403)
        old_admin=admin.cookies.copy();admin.logout('/admin')
        stale.cookies=old_admin
        check('Session admin setelah logout tidak dapat dipakai ulang',stale.request('/admin')['status']==302)
        operator=Client(writable,ip='127.0.0.3');r=operator.login('operator')
        check('Login operator diarahkan ke dashboard',r['status']==303 and r['headers'].get('location','').endswith('/operator'))
        check('Dashboard operator dapat dibuka',operator.request('/operator')['status']==200)
        check('Operator ditolak masuk admin',operator.request('/admin')['status']==403)
        check('Operator ditolak masuk profil customer',operator.request('/account')['status']==403)
        db.execute("UPDATE users SET role='customer' WHERE email='operator@shopease.test'");db.commit()
        check('Perubahan role berlaku pada request berikutnya',operator.request('/operator')['status']==403)
        db.execute("DELETE FROM users WHERE email='operator@shopease.test'");db.commit()
        check('Akun dihapus kehilangan akses sesi',operator.request('/account')['status']==302)
        rate=Client(writable,ip='127.0.0.4')
        responses=[rate.login(password='salah12345') for _ in range(6)]
        check('Login berulang dibatasi dengan HTTP 429',responses[-1]['status']==429 and 'retry-after' in responses[-1]['headers'])
        check('Database memiliki index email unik',bool(db.execute("SHOW INDEX FROM users WHERE Column_name='email' AND Non_unique=0")) if MYSQL_SOCKET else any(r[2] for r in db.execute("PRAGMA index_list('users')")))
        db.close()
    failed=sum(not r['passed'] for r in results)
    report={'executed_at':datetime.datetime.now(datetime.timezone.utc).isoformat(),'mode':'PHP CGI integration / '+DB_ENV['database.default.DBDriver']+' / isolated temporary writable','php':subprocess.check_output(['php','-r','echo PHP_VERSION;'],text=True),'total':len(results),'passed':len(results)-failed,'failed':failed,'results':results}
    if output: Path(output).write_text(json.dumps(report,ensure_ascii=False,indent=2)+'\n')
    print(f'\n{report["passed"]}/{report["total"]} pengujian lulus.')
    return 1 if failed else 0

if __name__=='__main__':
    parser=argparse.ArgumentParser();parser.add_argument('--output');args=parser.parse_args()
    if not shutil.which('php-cgi'):raise SystemExit('php-cgi diperlukan. Homebrew PHP menyediakannya.')
    raise SystemExit(run(args.output))
