#!/usr/bin/env python3
"""Bangun HTML Blogger dan pratinjau mandiri dari kode serta bukti pengujian aktual."""
from pathlib import Path
from html import escape
import base64, json

ROOT=Path(__file__).resolve().parents[1]
DOCS=ROOT/'docs'
APP=ROOT/'shopease'
GITHUB='https://github.com/fazdy/PBKK---C/tree/main/Tugas/Tugas%20Pertemuan%2011'
RAW='https://raw.githubusercontent.com/fazdy/PBKK---C/main/Tugas/Tugas%20Pertemuan%2011/docs/screenshots/'
TEST=json.loads((DOCS/'hasil-pengujian.json').read_text())
MYSQL=json.loads((DOCS/'hasil-mysql.json').read_text())
LINT=json.loads((DOCS/'hasil-lint.json').read_text())
BROWSER=json.loads((DOCS/'hasil-browser.json').read_text())
assert MYSQL['failed']==0 and not LINT['failed'] and TEST['failed']==0 and all(x['passed'] for x in BROWSER['assertions']), 'Hasil tes belum lulus seluruhnya.'

CSS='''
.pbkk11-report{--p11-ink:#17243d;--p11-muted:#5e6d85;--p11-blue:#2859d5;--p11-line:#e1e7f0;box-sizing:border-box;max-width:1080px;margin:0 auto;color:var(--p11-ink);font:16px/1.85 -apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;text-align:left;background:#fff;padding:24px}
.pbkk11-report *{box-sizing:border-box}.pbkk11-report a{color:#2554c2;text-decoration:underline;text-underline-offset:3px}.pbkk11-report p{margin:14px 0}.pbkk11-report h2{font-size:27px;line-height:1.35;letter-spacing:-.6px;margin:54px 0 20px;color:var(--p11-ink);font-weight:750}.pbkk11-report h3{font-size:20px;line-height:1.5;margin:30px 0 13px;font-weight:700}.pbkk11-report h4{font-size:17px;margin:25px 0 10px}.pbkk11-report ul,.pbkk11-report ol{padding-left:25px;margin:14px 0}.pbkk11-report li{margin-bottom:8px}.pbkk11-report .p11-cover{background:#17346d;color:#fff;padding:42px;border-radius:18px;margin-bottom:28px}.pbkk11-report .p11-kicker{font-size:11px;letter-spacing:2.4px;text-transform:uppercase;font-weight:700;color:#a9c3ff}.pbkk11-report .p11-title{font-size:38px;line-height:1.16;letter-spacing:-1.2px;font-weight:800;margin:18px 0}.pbkk11-report .p11-cover p{color:#dce7ff;font-size:16px;max-width:700px}.pbkk11-report .p11-author{border-top:1px solid #ffffff35;margin-top:28px;padding-top:20px;font-size:14px;line-height:1.9;color:#e9efff}.pbkk11-report .p11-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:24px 0}.pbkk11-report .p11-stat{padding:20px;background:#f2f6ff;border:1px solid #e2eafc;border-radius:12px;line-height:1.4}.pbkk11-report .p11-stat strong{display:block;font-size:30px;color:#2859d5;letter-spacing:-1px}.pbkk11-report .p11-stat span{font-size:12px;color:#52678b}.pbkk11-report .p11-toc{display:flex;flex-wrap:wrap;gap:8px 18px;border-top:1px solid var(--p11-line);border-bottom:1px solid var(--p11-line);padding:16px 0;font-size:13px}.pbkk11-report .p11-callout{padding:20px 24px;border-left:4px solid #3f6bda;border-radius:0 9px 9px 0;background:#f0f5ff;font-size:14px;margin:24px 0}.pbkk11-report .p11-note{background:#fff8e9;border-left-color:#d99a2a}.pbkk11-report .p11-table-wrap{overflow-x:auto;margin:20px 0;border:1px solid var(--p11-line);border-radius:9px}.pbkk11-report table{border-collapse:collapse;width:100%;font-size:13px;line-height:1.7}.pbkk11-report th{background:#edf2fc;color:#244373;text-align:left;font-weight:700}.pbkk11-report td,.pbkk11-report th{padding:13px 15px;border-bottom:1px solid var(--p11-line);vertical-align:top}.pbkk11-report tr:last-child td{border-bottom:0}.pbkk11-report tbody tr:nth-child(even){background:#fafbfd}.pbkk11-report code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:.85em;background:#f1f4f9;padding:2px 5px;border-radius:4px;overflow-wrap:anywhere}.pbkk11-report pre{background:#12213b;color:#e5eeff;padding:20px;border-radius:10px;font-size:12px;line-height:1.75;overflow:auto;tab-size:4;white-space:pre;margin:18px 0;max-height:650px}.pbkk11-report pre code{color:inherit;background:none;padding:0;white-space:pre;font-size:inherit;overflow-wrap:normal}.pbkk11-report details{border:1px solid var(--p11-line);border-radius:9px;padding:14px 18px;margin:16px 0}.pbkk11-report summary{cursor:pointer;font-size:14px;font-weight:650;color:#254884}.pbkk11-report figure{margin:30px 0 40px;border:1px solid var(--p11-line);padding:10px;background:#f8fafc;border-radius:12px}.pbkk11-report figure img{display:block;max-width:100%;height:auto;margin:0 auto;border-radius:6px;border:1px solid #e8edf4}.pbkk11-report figcaption{padding:14px 12px 7px;color:#52647f;font-size:13px;line-height:1.8}.pbkk11-report figcaption strong{color:#203960}.pbkk11-report .p11-mobile-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}.pbkk11-report .p11-mobile-grid img{max-height:1120px;width:auto}.pbkk11-report .p11-mobile-grid figure{margin-top:12px}.pbkk11-report .p11-end{margin-top:40px;padding-top:22px;border-top:1px solid var(--p11-line);font-size:13px;color:#6d7c93}.pbkk11-report .p11-link-box{border:1px solid #cddaf3;border-radius:10px;padding:20px;font-weight:650;background:#f7faff;overflow-wrap:anywhere}.pbkk11-report .p11-result{color:#237b54;font-weight:700}.pbkk11-report .p11-caption-label{font-size:11px;color:#7b8ba4;letter-spacing:1.2px;text-transform:uppercase}
@media(max-width:650px){.pbkk11-report{padding:14px;font-size:15px}.pbkk11-report .p11-cover{padding:26px 22px}.pbkk11-report .p11-title{font-size:30px}.pbkk11-report h2{font-size:24px}.pbkk11-report .p11-stats{gap:8px}.pbkk11-report .p11-stat{padding:14px 10px}.pbkk11-report .p11-stat strong{font-size:25px}.pbkk11-report .p11-stat span{font-size:11px}.pbkk11-report .p11-mobile-grid{grid-template-columns:1fr}.pbkk11-report .p11-callout{padding:17px}.pbkk11-report pre{font-size:11px;padding:15px}.pbkk11-report .p11-mobile-grid img{max-width:390px;width:100%}}
'''

def code(text):return '<pre><code>'+escape(text.strip())+'</code></pre>'
def table(headers,rows):
 return '<div class="p11-table-wrap"><table><thead><tr>'+''.join('<th>'+escape(h)+'</th>' for h in headers)+'</tr></thead><tbody>'+''.join('<tr>'+''.join('<td>'+cell+'</td>' for cell in row)+'</tr>' for row in rows)+'</tbody></table></div>'
def source(relative,label=None):
 path=APP/relative
 return '<details><summary>'+escape(label or relative)+'</summary>'+code(path.read_text())+'<p><a href="'+GITHUB+'/shopease/'+relative+'">Lihat berkas ini di GitHub</a></p></details>'
def figure(name,title,description,embedded):
 path=DOCS/'screenshots'/name
 assert path.is_file(),name
 url=('data:image/png;base64,'+base64.b64encode(path.read_bytes()).decode()) if embedded else RAW+name
 return '<figure><img src="'+url+'" alt="'+escape(title,quote=True)+'" loading="lazy"><figcaption><strong>'+escape(title)+'.</strong> '+description+'</figcaption></figure>'

screens=[
 ('01-registrasi.png','Gambar 1 — Halaman registrasi','Form menyediakan nama lengkap, email, telepon dan alamat opsional, password, serta konfirmasi password. Tidak terdapat pilihan role admin/operator; akun publik selalu menjadi customer.'),
 ('02-validasi-registrasi.png','Gambar 2 — Validasi konfirmasi password','Saat password dan konfirmasinya berbeda, server merespons HTTP 422 dan menampilkan pesan <em>Konfirmasi password tidak sama</em>. Data nonrahasia dipertahankan, sedangkan kedua field password dikosongkan.'),
 ('03-registrasi-berhasil.png','Gambar 3 — Registrasi berhasil','Akun demonstrasi Faizal dibuat melalui formulir. Aplikasi mengarahkan ke halaman login dan menampilkan pesan keberhasilan. Email <code>faizal.demo@shopease.test</code> adalah email contoh untuk pengujian.'),
 ('04-login-ditolak.png','Gambar 4 — Login dengan password salah','Aplikasi memberikan pesan generik <em>Email atau password salah</em> dengan HTTP 401. Pesan yang sama dipakai untuk email yang tidak ditemukan, sehingga tampilan tidak mengungkap keberadaan akun.'),
 ('05-beranda-customer.png','Gambar 5 — Customer berhasil login','Setelah kredensial benar, customer diarahkan ke beranda. Navigasi menampilkan nama pengguna dan tombol Keluar. Katalog publik dari tugas sebelumnya tetap terintegrasi.'),
 ('06-profil-customer.png','Gambar 6 — Profil customer','Halaman <code>/account</code> membaca identitas dari tabel users sesuai user_id pada session. Informasi yang belum diisi ditampilkan sebagai <em>Belum diisi</em>. Password dan hash tidak ditampilkan.'),
 ('07-akses-ditolak.png','Gambar 7 — Akses lintas role ditolak','Customer mencoba membuka <code>/admin</code> secara langsung. AuthFilter merespons HTTP 403. Perlindungan dilakukan di server sehingga tidak bergantung pada ada atau tidaknya menu admin di browser.'),
 ('08-dashboard-admin.png','Gambar 8 — Dashboard administrator','Admin diarahkan ke <code>/admin</code>. Statistik berasal dari query tabel users: 2 customer, 1 admin, dan 1 operator. Jumlah customer menjadi dua karena formulir registrasi sebelumnya berhasil menambahkan satu akun. Daftar pengguna bersifat baca saja.'),
 ('09-dashboard-operator.png','Gambar 9 — Dashboard operator','Operator diarahkan ke <code>/operator</code> dan melihat identitas serta area miliknya. Operator tidak diberi akses ke dashboard admin. Halaman ini merupakan bukti pemisahan role, belum modul pengelolaan pengiriman.'),
 ('10-logout.png','Gambar 10 — Logout berhasil','Tombol Keluar mengirim POST beserta token CSRF. Session dihancurkan dan browser kembali ke halaman login. Pengujian juga memastikan cookie session lama tidak dapat digunakan untuk membuka halaman privat.'),
 ('11-login-mobile.png','Gambar 11 — Login pada lebar 390 piksel','Tampilan form tetap terbaca pada ukuran mobile 390 × 844. Tombol Lihat/Tutup password bekerja. Pemeriksaan browser memastikan halaman login tidak melebar secara horizontal.'),
 ('12-registrasi-mobile.png','Gambar 12 — Registrasi pada lebar 390 piksel','Field registrasi mengikuti lebar layar. Selain screenshot 390 piksel, pengujian memeriksa lebar 320 piksel tanpa overflow horizontal. Screenshot ini adalah tampilan responsif browser, bukan pengujian pada iPhone fisik.'),
]

def build(embedded=False):
 h=['<style>'+CSS+'</style>','<article class="pbkk11-report">']
 h.append('''<header class="p11-cover"><div class="p11-kicker">Tugas Pertemuan 11 · PBKK (C)</div><div class="p11-title">Sistem Autentikasi ShopEase<br>dengan CodeIgniter 4</div><p>Registrasi, login, session, otorisasi berdasarkan role, dan logout pada aplikasi toko online yang dijalankan menggunakan lingkungan macOS Intel.</p><div class="p11-author"><strong>Faizal Aldy Armiriawan</strong><br>NRP 5025231266 · Kelas PBKK (C)<br>MacBook Pro 2020 · Intel Core i5 · RAM 8 GB · SSD 256 GB</div></header>''')
 h.append(f'<div class="p11-stats"><div class="p11-stat"><strong>{TEST["passed"]+MYSQL["passed"]}/{TEST["total"]+MYSQL["total"]}</strong><span>integrasi SQLite + MySQL lulus</span></div><div class="p11-stat"><strong>{len(BROWSER["assertions"])}/{len(BROWSER["assertions"])}</strong><span>pemeriksaan browser lulus</span></div><div class="p11-stat"><strong>{len(screens)}</strong><span>screenshot hasil aplikasi</span></div></div>')
 h.append('''<nav class="p11-toc" aria-label="Isi laporan"><a href="#p11-pendahuluan">Pendahuluan</a><a href="#p11-persiapan">Persiapan Mac</a><a href="#p11-implementasi">Implementasi</a><a href="#p11-pengujian">Pengujian</a><a href="#p11-screenshot">Screenshot</a><a href="#p11-publikasi">GitHub &amp; Blogspot</a></nav>''')
 h.append('''<h2 id="p11-pendahuluan">1. Pendahuluan dan tujuan tugas</h2><p>ShopEase merupakan studi kasus aplikasi toko online berbasis CodeIgniter 4. Setelah halaman publik dan navigasi dibuat pada Pertemuan 10, Tugas Pertemuan 11 menambahkan fondasi autentikasi agar aplikasi dapat membedakan pengunjung, customer, administrator, dan operator toko.</p><p>Autentikasi menjawab siapa pengguna yang sedang masuk, sedangkan otorisasi menentukan halaman yang boleh diakses pengguna tersebut. Implementasi memisahkan keduanya melalui AuthController, session, dan AuthFilter.</p><p>Tujuan tugas ini adalah membangun formulir registrasi dengan validasi, menyimpan password dalam bentuk hash, memverifikasi login, meregenerasi session ID, melakukan redirect sesuai role, melindungi halaman privat, dan mengakhiri session melalui logout.</p>''')
 h.append('''<h2>2. Analisis materi dan ruang lingkup</h2><p>Acuan utama pengerjaan Tugas Pertemuan 11 adalah <strong>Hands-On Lab Authentication System CodeIgniter 4</strong> di folder Materi Pertemuan 11. Dokumen PRD, HOL umum, studi kasus, dan infografis dipakai untuk memahami konteks pengembangan ShopEase.</p>''')
 h.append(table(['Materi yang ditelaah','Pemanfaatan pada tugas'],[
  ['Hands-On_Lab_Authentication_System_CodeIgniter_4.docx.pdf','Acuan tabel users, model, controller, view, routes, session, filter, dan delapan skenario pengujian wajib.'],
  ['HOL_ShopEase_CodeIgniter4.docx.pdf','Rencana pengembangan ShopEase dan integrasi modul autentikasi serta otorisasi.'],
  ['PRD_ShopEase_CodeIgniter4.docx.pdf','FR-001 registrasi, FR-002 login, FR-003 logout, FR-004 role/access; kebutuhan validasi dan keamanan.'],
  ['Studi_Kasus_ShopEase_CodeIgniter4.docx.pdf','Konteks aktor, pola MVC, dan posisi autentikasi dalam aplikasi toko online.'],
  ['Panduan Autentikasi CodeIgniter 4 ShopEase.png','Rangkuman alur registrasi/login dan susunan komponen autentikasi.'],
  ['Infografik Struktur Website Shopease.png','Pemisahan area publik, administrator, dan operator.'],
  ['Infografis Arsitektur Aplikasi ShopEase.png','Hubungan routing, controller, model, view, session, filter, dan database.'],
  ['Infografis PRD ShopEase CodeIgniter 4-2.png; Infografis ShopEase_ Toko Online Modern-2.png','Gambaran produk, aktor, dan urutan pengembangan modul berikutnya.'],
 ]))
 h.append('''<div class="p11-callout"><strong>Batas implementasi.</strong> Tugas Pertemuan 11 berfokus pada modul autentikasi dari panduan khusus. Penomoran pada roadmap generik dalam dokumen berbeda dari nomor pengumpulan tugas. CRUD katalog, keranjang, checkout, pembayaran, pengiriman, dan laporan penjualan merupakan pengembangan berikutnya; fitur-fitur tersebut tidak diklaim selesai pada laporan ini.</div>''')
 h.append(table(['Hasil yang dibuat','Keterangan'],[
  ['Registrasi dan login','Validasi server, email unik, hashing, verifikasi password, dan pesan kesalahan.'],
  ['Session dan logout','Session ID baru setelah login, cookie HttpOnly/SameSite Lax, POST logout, serta penolakan session lama.'],
  ['Tiga area pengguna','Profil customer, dashboard administrator, dan dashboard operator dengan filter server.'],
  ['Integrasi tampilan','Beranda, katalog, Tentang, dan Kontak dari Pertemuan 10 terhubung dengan menu akun.'],
  ['Dokumentasi','Kode lengkap, migration, seeder, SQL MySQL, README, hasil tes, screenshot, dan HTML Blogspot.'],
 ]))
 h.append('''<h2 id="p11-persiapan">3. Persiapan lingkungan MacBook Intel</h2><p>Perangkat yang digunakan adalah MacBook Pro 2020 Intel Core i5 dengan RAM 8 GB dan SSD 256 GB. Implementasi memakai PHP native dan browser sehingga dapat dikerjakan tanpa menjalankan sistem operasi Windows atau mesin virtual. Editor yang digunakan untuk membuka proyek dapat berupa Visual Studio Code versi macOS Intel.</p>''')
 h.append(table(['Komponen','Konfigurasi dan status'],[
  ['Sistem operasi dan arsitektur','macOS 15.8.3, x86_64 (Intel), diperiksa pada lingkungan pengerjaan.'],
  ['PHP','8.4.12; CI 4.7.6 membutuhkan PHP 8.2 atau lebih baru serta intl dan mbstring.'],
  ['Framework','CodeIgniter 4.7.6, direktori system disertakan tanpa modifikasi.'],
  ['Database yang diuji','SQLite3, file lokal writable/db_shopease.sqlite.'],
  ['Database sesuai materi','MySQL 9.3.0; 47/47 integrasi dan 14/14 browser HTTP lulus pada instance sementara via Unix socket.'],
  ['Antarmuka','Bootstrap 5.3.3 lokal, CSS tambahan, dan JavaScript untuk tombol lihat password.'],
  ['Browser pengujian','Chrome '+escape(BROWSER['browserVersion'])+' melalui Playwright '+escape(BROWSER.get('playwrightVersion',''))+'.'],
 ]))
 h.append('''<h3>3.1 Memeriksa PHP dan ekstensi</h3><p>Perintah berikut dijalankan melalui Terminal. PHP dan ekstensi yang dipakai sudah tersedia pada lingkungan pengerjaan.</p>''')
 h.append(code('''uname -m
php -v
php -m
which php'''))
 h.append('''<p>Jika PHP belum terpasang, instal Homebrew dari situs resminya dan jalankan <code>brew install php</code>. Prefix Homebrew pada Mac Intel umumnya <code>/usr/local</code>. Untuk mode SQLite pastikan ekstensi <code>sqlite3</code> aktif, sedangkan MySQL menggunakan <code>mysqli</code>.</p><p>Distribusi proyek ini menyertakan framework dan aset lokal. Composer tidak diperlukan untuk menjalankan hasil akhir. Perintah <code>composer create-project codeigniter4/appstarter shopease</code> pada materi menjelaskan pembuatan proyek dari nol; untuk tugas ini fondasi ShopEase Pertemuan 10 digunakan kembali agar navigasi publik tetap konsisten. Jangan membuat proyek baru di atas folder hasil yang sudah tersedia.</p>''')
 h.append('''<h3>3.2 Menyiapkan folder proyek</h3><p>Folder <code>Tugas/Tugas Pertemuan 11</code> dibuat mengikuti susunan tugas di repository. Framework berada di subfolder <code>shopease</code>, sedangkan dokumen, SQL, script, dan pengujian dipisahkan agar mudah ditelusuri.</p>''')
 h.append(table(['Direktori/berkas','Peran'],[
  ['shopease/app/Controllers','AuthController, AccountController, serta controller dashboard admin/operator.'],
  ['shopease/app/Models','UserModel untuk tabel users; DemoCatalog untuk data katalog contoh.'],
  ['shopease/app/Filters','AuthFilter dan NoCacheFilter.'],
  ['shopease/app/Views/auth','Registrasi, login, profil, dashboard, validasi, dan halaman 403.'],
  ['shopease/app/Database','Migration CreateUsers dan DemoUserSeeder.'],
  ['shopease/public/assets','Bootstrap, CSS, JavaScript, dan ilustrasi katalog lokal.'],
  ['database/schema-mysql.sql','Skema SQL alternatif untuk MySQL.'],
  ['scripts/setup.sh; scripts/build_report.py','Persiapan database dan pembuatan dua versi laporan HTML.'],
  ['tests; docs','Pengujian, hasil aktual, laporan, dan 12 screenshot.'],
 ]))
 h.append('''<h3>3.3 Persiapan cepat menggunakan SQLite</h3><p>Dari root repository PBKK---C, jalankan:</p>''')
 h.append(code('''cd "Tugas/Tugas Pertemuan 11"
bash scripts/setup.sh sqlite
cd shopease
php spark serve'''))
 h.append('''<p>Script memeriksa PHP, menyalin <code>env</code> menjadi <code>.env</code> jika belum ada, menjalankan migration, dan menambahkan akun demonstrasi. Buka <code>http://localhost:8080/</code> pada browser. Hentikan server dengan Control+C. File <code>.env</code> yang sudah ada tidak ditimpa.</p>''')
 h.append(code((APP/'env').read_text()))
 h.append('<div class="p11-callout"><strong>Metode eksekusi terverifikasi.</strong> Server <code>php spark serve</code> berhasil dijalankan. Screenshot terbaru menggunakan HTTP nyata pada <code>http://127.0.0.1:8082/</code> dengan database '+escape(BROWSER['database'])+'. Alur browser tidak memakai request interception. Pengujian integrasi terpisah menggunakan PHP CGI pada SQLite dan MySQL.</div>')
 h.append('''<h3>3.4 Alternatif MySQL sesuai materi</h3><p>Materi menggunakan database <code>db_shopease</code> pada MySQL. Untuk mengikuti konfigurasi tersebut, jalankan MySQL lokal, buat database kosong, kemudian isi konfigurasi koneksi. SQLite dipilih sebagai jalur demonstrasi yang telah teruji pada perangkat ini, sedangkan jalur MySQL disediakan untuk mengikuti materi.</p>''')
 h.append(code('''brew services start mysql
mysql -u root -p'''))
 h.append(code('''CREATE DATABASE IF NOT EXISTS db_shopease
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'shopease'@'127.0.0.1'
  IDENTIFIED BY 'GantiPasswordDatabaseAnda';
GRANT ALL PRIVILEGES ON db_shopease.* TO 'shopease'@'127.0.0.1';'''))
 h.append('''<p>Jika <code>.env</code> belum ada, salin <code>env.mysql</code>. Jika sudah menggunakan SQLite, edit <code>.env</code> yang ada. Ganti password contoh dengan kredensial lokal yang dibuat sendiri:</p>''')
 h.append(code((APP/'env.mysql').read_text().replace('GANTI_PASSWORD_DATABASE_LOKAL','GantiPasswordDatabaseAnda')))
 h.append(code('''php spark migrate
php spark db:seed DemoUserSeeder
php spark serve'''))
 h.append('''<p>Migration menjadi jalur utama pembuatan tabel. File <code>database/schema-mysql.sql</code> adalah alternatif import manual, sehingga tidak perlu diimpor sebelum migration. Berpindah driver tidak otomatis memindahkan akun dari SQLite ke MySQL. MySQL 9.3.0 berhasil diverifikasi melalui instance sementara dengan Unix socket. Konfigurasi TCP localhost pada komputer pengguna tetap harus memakai kredensial lokal yang benar.</p>''')
 h.append('''<h2 id="p11-implementasi">4. Perancangan tabel users</h2><p>Struktur tabel mengikuti panduan autentikasi khusus, dengan primary key <code>id_user</code>. Ini berbeda dari contoh <code>id</code> pada beberapa dokumen umum. Penggunaan <code>id_user</code> diterapkan secara konsisten pada migration, model, session, dan query profil.</p>''')
 h.append(table(['Kolom','Jenis/logika','Kegunaan'],[
  ['id_user','INT, primary key, auto increment','Identitas pengguna.'],['name','VARCHAR(100), wajib','Nama tampilan pengguna.'],['email','VARCHAR(100), unique, wajib','Identitas login; di-trim dan diubah ke huruf kecil sebelum validasi dan penyimpanan.'],['password','VARCHAR(255), wajib','Hash hasil password_hash, bukan password asli.'],['role','ENUM pada MySQL; VARCHAR(20) pada SQLite','Nilai customer, admin, atau operator. Registrasi publik menetapkan customer.'],['phone','VARCHAR(20), nullable','Nomor telepon opsional.'],['address','TEXT, nullable','Alamat opsional, dibatasi 500 karakter pada validasi aplikasi.'],['created_at; updated_at','DATETIME, timestamp model','Waktu pembuatan dan pembaruan akun.'],
 ]))
 h.append('''<p>Index unik pada email melindungi integritas data di tingkat database. Pemeriksaan <code>is_unique</code> memberikan pesan ramah pengguna, sementara constraint database menangani kemungkinan dua request bersamaan. Migration menyesuaikan tipe role berdasarkan driver.</p>''')
 h.append(source('Database/Migrations/2026-11-15-000001_CreateUsers.php'.replace('Database/','app/Database/',1),'Kode migration CreateUsers'))
 h.append('''<h2>5. Model dan arsitektur MVC</h2><p>UserModel mengatur nama tabel, primary key, daftar field yang boleh disimpan, serta timestamp otomatis. Controller menerima input dan menentukan alur. View menampilkan formulir serta pesan. Filter memeriksa status login dan role sebelum controller halaman privat dipanggil.</p>''')
 h.append(table(['Komponen','Tanggung jawab'],[
 ['Routes.php','Memetakan URL dan method HTTP ke controller; memasang filter role.'],['AuthController','Memvalidasi data, mendaftarkan customer, memverifikasi login, dan logout.'],['UserModel','Menyimpan serta mengambil akun menggunakan model/query builder.'],['Views/auth','Merender input, notifikasi, profil, dan dashboard.'],['AuthFilter','Mengarahkan tamu ke login dan menolak role yang tidak berhak.'],['Session','Menyimpan isLoggedIn, user_id, name, email, serta role setelah login.'],
 ]))
 h.append(source('app/Models/UserModel.php','Kode lengkap UserModel'))
 h.append('''<h2>6. Implementasi registrasi customer</h2><ol><li>Pengguna membuka GET <code>/register</code>.</li><li>Form mengirim POST <code>/register</code> bersama token CSRF.</li><li>Controller membaca hanya input string, menormalkan nama/email, dan menjalankan validasi server.</li><li>Jika tidak valid, server mengembalikan HTTP 422 dan pesan yang sesuai. Password tidak dimasukkan ke nilai lama atau flashdata.</li><li>Jika valid, password di-hash dengan <code>password_hash(..., PASSWORD_DEFAULT)</code>.</li><li>Data disimpan dengan <code>role = customer</code> dan timestamp otomatis.</li><li>Server mengarahkan pengguna ke login dengan HTTP 303 dan pesan registrasi berhasil.</li></ol>''')
 h.append(table(['Input','Validasi'],[
  ['Nama','Wajib, 3–100 karakter.'],['Email','Wajib, format valid, maksimal 100 karakter, unik setelah normalisasi.'],['Password','Minimal 8 karakter, maksimal 72 byte untuk bcrypt, tanpa karakter null.'],['Konfirmasi password','Wajib dan sama dengan password.'],['Telepon','Opsional; 6–20 karakter berupa angka, spasi, +, -, atau tanda kurung.'],['Alamat','Opsional, maksimal 500 karakter.'],['Role','Tidak tersedia pada form dan tidak diambil dari request publik.'],
 ]))
 h.append('''<p>Batas 72 byte diperiksa secara eksplisit agar password multibyte tidak terpotong diam-diam pada bcrypt. Pada error, hanya nama, email, telepon, dan alamat yang dipertahankan di halaman. Semua nilai keluaran di-escape menggunakan <code>esc()</code>.</p>''')
 h.append(source('app/Views/auth/register.php','Kode view registrasi'))
 h.append('''<h2>7. Login, session, dan redirect berdasarkan role</h2><p>Login mencari pengguna berdasarkan email yang telah dinormalisasi, lalu membandingkan password dengan hash menggunakan <code>password_verify()</code>. Email tidak ditemukan maupun password salah menghasilkan pesan yang sama. Setelah autentikasi berhasil, aplikasi menjalankan <code>session()-&gt;regenerate(true)</code> untuk mengganti ID session dan membuang session lama.</p>''')
 h.append(code('''session()->regenerate(true);
session()->set([
    'isLoggedIn' => true,
    'user_id'    => $user['id_user'],
    'name'       => $user['name'],
    'email'      => $user['email'],
    'role'      => $user['role'],
]);'''))
 h.append(table(['Role','Tujuan setelah login','Halaman khusus'],[
  ['Customer','/','Profil pribadi di /account.'],['Admin','/admin','Statistik jumlah akun dan sepuluh pengguna terbaru.'],['Operator','/operator','Identitas operator dan tautan katalog.'],
 ]))
 h.append('''<p>Pembatasan percobaan menggunakan Throttler dengan token bucket: kapasitas lima percobaan untuk kombinasi email/IP dan 30 untuk IP, dengan pengisian kembali selama 60 detik. Jika kapasitas habis, respons HTTP 429 menyertakan Retry-After. Bucket email/IP dihapus setelah login berhasil.</p><p>Cookie session memakai HttpOnly dan SameSite Lax pada lingkungan lokal. Hash diperbarui ketika <code>password_needs_rehash()</code> menyatakan konfigurasi hashing perlu diperbarui. Tampilan login juga menyediakan tombol Lihat/Tutup password yang hanya mengubah jenis input di browser.</p>''')
 h.append(source('app/Controllers/AuthController.php','Kode lengkap AuthController: registrasi, login, dan logout'))
 h.append(source('app/Views/auth/login.php','Kode view login'))
 h.append('''<h2>8. Routing, AuthFilter, dan proteksi CSRF</h2><p>Auto Routing dinonaktifkan sehingga endpoint hanya tersedia melalui rute yang didefinisikan. Filter <code>auth:admin</code> dan <code>auth:operator</code> diterapkan pada grup rute masing-masing. Profil memakai <code>auth:customer</code>.</p>''')
 h.append(source('app/Config/Routes.php','Kode lengkap Routes.php'))
 h.append(table(['Permintaan','Tamu','Customer','Admin','Operator'],[
  ['GET halaman publik','200','200','200','200'],['GET /account','302 ke login','200','403','403'],['GET /admin','302 ke login','403','200','403'],['GET /operator','302 ke login','403','403','200'],['POST /logout + token valid','302 ke login','303 ke login','303 ke login','303 ke login'],['POST tanpa token CSRF','403','403','403','403'],
 ]))
 h.append('''<p>AuthFilter membaca ulang akun dari database pada setiap akses privat. Karena itu perubahan role berlaku pada request berikutnya, dan akun yang dihapus tidak dapat mempertahankan akses hanya melalui session lama. Role yang tidak sesuai mendapatkan halaman 403 dengan tautan kembali ke area akun.</p>''')
 h.append(source('app/Filters/AuthFilter.php','Kode lengkap AuthFilter'))
 h.append('''<p>CSRF diaktifkan secara global dan menggunakan penyimpanan session. Semua form POST menyertakan <code>csrf_field()</code>. NoCacheFilter menambahkan <code>Cache-Control: no-store, private, max-age=0</code> untuk membatasi penyimpanan halaman akun oleh browser. Daftar akun hanya menampilkan field yang diperlukan; kolom password tidak diikutkan pada query dashboard.</p>''')
 h.append(source('app/Config/Filters.php','Konfigurasi filter aplikasi'))
 h.append('''<h2>9. Logout dan akun demonstrasi</h2><p>Logout dilakukan dengan POST yang dilindungi CSRF. Controller menghancurkan session dan mengarahkan ke <code>/login?logout=1</code>. Notifikasi logout ditampilkan dari parameter tetap tersebut sehingga aplikasi tidak mencoba menulis flashdata ke session yang telah dihancurkan. HTTP GET ke <code>/logout</code> tidak terdaftar dan menghasilkan 404.</p>''')
 h.append(code('''public function logout()
{
    session()->destroy();
    return redirect()->to(site_url('login') . '?logout=1');
}'''))
 h.append('''<p>Seeder menyediakan tiga akun lokal untuk demonstrasi. Seeder tidak mereset akun yang sudah ada dan menolak environment production. Tidak ada pilihan role istimewa pada halaman registrasi.</p>''')
 h.append(table(['Role','Email demo','Password demo'],[
  ['Customer','customer@shopease.test','ShopEase!2026'],['Admin','admin@shopease.test','ShopEase!2026'],['Operator','operator@shopease.test','ShopEase!2026'],
 ]))
 h.append('''<p>Email <code>.test</code> adalah data contoh. Akun tambahan pada screenshot dibuat melalui form di database pengujian terpisah, sehingga tidak otomatis tersedia ketika seeder dijalankan pada database baru.</p>''')
 h.append(source('app/Database/Seeds/DemoUserSeeder.php','Kode seeder akun demo'))
 h.append('''<h2 id="p11-pengujian">10. Pengujian dan hasil aktual</h2><p>Pengujian dilakukan pada 15 November 2026. Backend diuji melalui proses PHP CGI yang menerima request GET/POST, cookie, serta input form. Database SQLite maupun MySQL, session, dan cache dibuat pada direktori temporer terpisah agar tidak mengganggu data pengguna. Setelah pengujian selesai, direktori tersebut dibersihkan.</p>''')
 h.append(code('''python3 tests/test_auth.py --output docs/hasil-pengujian.json
php shopease/spark routes
php shopease/spark migrate:status'''))
 h.append('''<h3>10.1 Delapan skenario wajib dari HOL</h3>''')
 h.append(table(['No.','Skenario','Hasil aktual'],[
  ['1','Registrasi user baru','Lulus: akun tersimpan, password hash, redirect ke login.'],['2','Registrasi email yang sama','Lulus: ditolak termasuk variasi huruf besar/spasi.'],['3','Password kurang dari 8 karakter','Lulus: HTTP 422 dan pesan validasi.'],['4','Konfirmasi password berbeda','Lulus: HTTP 422 dan pesan kesalahan.'],['5','Login dengan data benar','Lulus: session aktif dan redirect sesuai role.'],['6','Login dengan password salah','Lulus: HTTP 401 dan pesan generik.'],['7','Logout','Lulus: session dihancurkan; cookie lama tidak memberi akses.'],['8','Halaman privat tanpa login','Lulus: HTTP 302 ke login.'],
 ]))
 h.append('''<h3>10.2 Pemeriksaan tambahan</h3><p>Pengujian tambahan mencakup input array, batas password multibyte, telepon invalid, manipulasi role registrasi, tidak tersimpannya password sebagai teks biasa, escaping nama, regenerasi session ID, CSRF, HTTP 429, akses lintas role, perubahan role, penghapusan akun, dan header no-store.</p>''')
 h.append(f'<p class="p11-result">Hasil SQLite: {TEST["passed"]}/{TEST["total"]}; MySQL: {MYSQL["passed"]}/{MYSQL["total"]}; seluruhnya lulus.</p>')
 h.append('<p>Bukti tersimpan pada <code>docs/hasil-pengujian.json</code>, <code>docs/hasil-mysql.json</code>, <code>docs/hasil-browser.json</code>, dan <code>docs/hasil-lint.json</code>. Tes MySQL menggunakan buffer pool 64 MiB, tanpa service permanen, agar ringan pada RAM 8 GB.</p>')
 h.append('<details><summary>Daftar lengkap 47 pemeriksaan integrasi</summary>'+table(['No.','Pemeriksaan','Status'],[[str(i),escape(r['name']),'LULUS' if r['passed'] else 'GAGAL'] for i,r in enumerate(TEST['results'],1)])+'</details>')
 h.append('''<h3>10.3 Pemeriksaan browser</h3><p>Playwright mengirim interaksi formulir pada Chrome. Browser terhubung langsung ke server HTTP php spark serve pada port 8082. Semua formulir, cookie, redirect, serta aset dilayani aplikasi, tanpa adapter atau mock. Instance MySQL sementara terpisah dari database pengguna.</p>''')
 h.append(table(['Pemeriksaan browser','Hasil'],[[escape(r['name']),'LULUS' if r['passed'] else 'GAGAL'] for r in BROWSER['assertions']]))
 h.append('''<p>Untuk mereproduksi screenshot, instal dependency pengujian (opsional):</p>''')
 h.append(code('''brew install mysql # hanya jika belum terpasang
npm --prefix tests install
CHROME_PATH="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
  python3 tests/test_mysql.py
python3 scripts/build_report.py'''))
 h.append('''<div class="p11-callout"><strong>Batas bukti pengujian.</strong> Hasil di atas mencakup PHP CGI pada SQLite3 dan MySQL 9.3.0, serta Chrome pada server HTTP nyata dengan MySQL. Safari, Firefox, perangkat iPhone fisik, dan deployment produksi belum diuji. Pemeriksaan sintaks terhadap 82 file PHP aplikasi, entrypoint, serta helper uji menghasilkan nol error. MySQL diuji melalui Unix socket, bukan service TCP pengguna.</div>''')
 h.append('''<h2 id="p11-screenshot">11. Screenshot hasil aplikasi dan keterangannya</h2><p>Seluruh gambar berikut diambil dari halaman yang dirender oleh aplikasi selama pengujian. Form, pesan validasi, status login, dashboard, dan pembatasan akses berasal dari proses PHP serta data demonstrasi aktual. Gambar tidak dibuat sebagai mockup.</p>''')
 for item in screens[:10]:h.append(figure(*item,embedded))
 h.append('<div class="p11-mobile-grid">'+''.join(figure(*s,embedded) for s in screens[10:])+'</div>')
 h.append('''<h2>12. Cara menjalankan dan mendemonstrasikan hasil</h2><ol><li>Buka folder repository di VS Code, masuk ke <code>Tugas/Tugas Pertemuan 11</code>, lalu jalankan setup SQLite.</li><li>Jalankan <code>php spark serve</code> dari folder shopease pada Terminal biasa.</li><li>Buka <code>/register</code> dan buat customer dengan email baru.</li><li>Uji password konfirmasi yang berbeda, lalu perbaiki dan kirim ulang.</li><li>Login dengan password salah untuk melihat validasi, lalu login dengan data benar.</li><li>Buka <code>/account</code> dan periksa identitas yang tersimpan.</li><li>Ketik <code>/admin</code> pada address bar ketika masih sebagai customer; halaman 403 seharusnya muncul.</li><li>Logout, kemudian masuk sebagai admin dan operator menggunakan akun demo secara bergantian.</li><li>Setelah logout, coba membuka halaman privat lagi; aplikasi seharusnya meminta login.</li></ol>''')
 h.append('''<h2>13. Kendala dan penyelesaian</h2>''')
 h.append(table(['Kendala','Penanganan'],[
  ['Materi menyebut XAMPP/Laragon, perangkat memakai Mac Intel','Menggunakan PHP CLI native, browser, dan SQLite untuk jalur ringan. Konfigurasi MySQL tetap disediakan.'],
  ['Server HTTP pada progres lama belum terverifikasi','Izin eksekusi lokal tersedia pada sesi lanjutan; server php spark serve berhasil diuji langsung melalui Chrome.'],
  ['Konfigurasi .env demo SQLite sempat terbaca pada tes MySQL','Memisahkan direktori konfigurasi tes; menjalankan ulang 47 pemeriksaan pada MySQL dan 14 pemeriksaan browser HTTP hingga lulus.'],
  ['Redirect POST semula diasumsikan HTTP 302 pada pemeriksaan','Menyesuaikan pemeriksaan dengan perilaku CI4 yang benar, yaitu HTTP 303 untuk Post/Redirect/Get.'],
  ['Navigasi 320 piksel sempat melebar','Mengubah header menjadi flex, mengatur gap, dan menguji ulang hingga tidak ada overflow horizontal.'],
  ['Screenshot Blogspot harus dapat diakses pembaca','Menggunakan URL raw GitHub pada HTML Blogger; menyediakan pratinjau terpisah dengan PNG tertanam untuk dibaca sebelum push.'],
 ]))
 h.append('''<h2 id="p11-publikasi">14. Penyimpanan kode ke GitHub dan publikasi Blogspot</h2><p>Seluruh kode tugas, dokumentasi, dan screenshot ditempatkan dalam <code>Tugas/Tugas Pertemuan 11</code>. File <code>.env</code>, database SQLite, session, cache, log, serta node_modules dikecualikan melalui .gitignore.</p>''')
 h.append('<div class="p11-link-box">Repository: <a href="https://github.com/fazdy/PBKK---C">fazdy/PBKK---C</a><br>Folder tugas: <a href="'+GITHUB+'">Tugas Pertemuan 11 — Autentikasi ShopEase</a></div>')
 h.append('''<p>Perintah berikut dijalankan dari root repository setelah memeriksa perubahan yang akan disertakan:</p>''')
 h.append(code('''git status --short
git add -- README.md "Tugas/Tugas Pertemuan 11"
git diff --cached --stat
git commit -m "Tambah Tugas Pertemuan 11: autentikasi ShopEase CI4"
git push origin main'''))
 h.append('''<p>README root telah mendapat tautan ke tugas ini. Salinan kerja menggunakan main terbaru; perubahan hanya mencakup tugas ini dan tautannya pada README root. Kode, laporan, serta screenshot disiapkan bersama untuk branch main repository; perintah di atas juga dapat dipakai untuk pembaruan berikutnya.</p><p>Untuk Blogspot, buka file <code>docs/Laporan-Blogspot-ShopEase-Autentikasi.html</code> sebagai teks, salin seluruh sumber, buat posting baru, dan tempel melalui <strong>Tampilan HTML</strong>. Gunakan judul <em>Tugas Pertemuan 11 PBKK (C) — Sistem Autentikasi ShopEase dengan CodeIgniter 4</em>, lalu periksa Pratinjau.</p><p>HTML Blogger menunjuk ke 12 gambar pada branch main repository publik. Karena itu gambar harus ter-push sebelum posting diterbitkan. Apabila repository privat, unggah PNG melalui editor Blogger dan ganti URL gambar. Versi <code>Pratinjau-Laporan-ShopEase-Autentikasi.html</code> menanam PNG agar dapat dibaca sekarang tanpa menunggu push.</p>''')
 h.append('''<h2>15. Kesimpulan</h2><p>Modul autentikasi ShopEase berhasil diimplementasikan menggunakan pola MVC CodeIgniter 4. Registrasi menyimpan customer dengan password hash, login membuat session dan mengarahkan pengguna sesuai role, AuthFilter membatasi akses, serta logout mengakhiri session. Hasil integrasi dan pemeriksaan browser menunjukkan alur modul tersebut berjalan pada lingkungan SQLite, MySQL, dan HTTP yang diuji.</p><p>Implementasi ini menjadi fondasi untuk pengembangan CRUD katalog, keranjang, checkout, pembayaran, serta pengiriman sesuai roadmap ShopEase. Tahap lanjutan tetap memerlukan implementasi modul terkait dan pengujian tersendiri.</p>''')
 h.append('''<h2>16. Referensi</h2><ol><li>Materi Pertemuan 11: <em>Hands-On Lab Authentication System CodeIgniter 4</em>, 12 halaman.</li><li>Materi Pertemuan 11: <em>HOL ShopEase CodeIgniter 4</em>, <em>PRD ShopEase CodeIgniter 4</em>, dan <em>Studi Kasus ShopEase CodeIgniter 4</em>.</li><li>Lima infografis materi: panduan autentikasi, struktur website, arsitektur aplikasi, PRD, dan gambaran toko online modern.</li><li><a href="https://codeigniter.com/user_guide/intro/requirements.html">CodeIgniter — Server Requirements</a>.</li><li><a href="https://codeigniter.com/user_guide/database/configuration.html">CodeIgniter — Database Configuration</a>.</li><li><a href="https://codeigniter.com/user_guide/libraries/security.html">CodeIgniter — Security dan CSRF</a>.</li><li><a href="https://codeigniter.com/user_guide/incoming/filters.html">CodeIgniter — Controller Filters</a>.</li><li><a href="https://codeigniter.com/user_guide/dbmgmt/seeds.html">CodeIgniter — Database Seeding</a>.</li><li><a href="https://brew.sh/">Homebrew — Instalasi dan penggunaan pada macOS</a>.</li></ol><p class="p11-end">Faizal Aldy Armiriawan · 5025231266 · PBKK (C)<br>Dokumentasi implementasi dan pengujian: 15 November 2026.</p></article>''')
 return '\n'.join(h)

blog=build(False)
preview=build(True)
(DOCS/'Laporan-Blogspot-ShopEase-Autentikasi.html').write_text('<!-- Tempel seluruh file ini pada Tampilan HTML Blogger. Push screenshot ke GitHub terlebih dahulu. -->\n'+blog+'\n')
(DOCS/'Pratinjau-Laporan-ShopEase-Autentikasi.html').write_text('<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tugas Pertemuan 11 PBKK (C) — Autentikasi ShopEase</title></head><body style="margin:0;background:#eef2f8;padding:24px 0">'+preview+'</body></html>\n')
print('Laporan Blogger dan pratinjau mandiri selesai dibuat.')
