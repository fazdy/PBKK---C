import { chromium } from 'playwright-core';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import os from 'node:os';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const base = process.env.PBKK12_TEST_URL || 'http://127.0.0.1:8080';
if (process.env.PBKK12_TEST_RUN !== '1' || !['127.0.0.1', 'localhost'].includes(new URL(base).hostname)) {
    throw Error('Jalankan pada server lokal dengan database uji khusus dan PBKK12_TEST_RUN=1.');
}
const docs = path.join(root, 'docs');
const shots = path.join(docs, 'screenshots');
await mkdir(shots, { recursive: true });
const before = inspect();
assert.deepEqual([before.categories,before.products,before.product_images],[4,8,8], 'Gunakan database uji baru yang telah di-seed.');
const browser = await chromium.launch({
    executablePath: process.env.CHROME_PATH || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true, args: ['--disable-dev-shm-usage'],
});
const checks = [], captures = [], failures = [];
let page;
function inspect() {
    return JSON.parse(execFileSync('php', [path.join(root, 'tests/inspect.php')], { encoding: 'utf8' }));
}
function pass(id, label) { checks.push({ id, label }); console.log('LULUS ' + id + ' ' + label); }
async function shot(id, label) {
    await page.locator('body').evaluate(async body => {
        await document.fonts.ready;
        await Promise.all([...body.querySelectorAll('img')].map(img => img.decode().catch(() => {})));
    });
    await page.screenshot({ path: path.join(shots, id + '.jpg'), type: 'jpeg', quality: 88, fullPage: true, animations: 'disabled' });
    captures.push({ file: id + '.jpg', label, route: new URL(page.url()).pathname + new URL(page.url()).search, viewport: page.viewportSize() });
}
const target = p => new URL(p, base).href;
const goto = p => page.goto(target(p));
const row = text => page.locator('tbody tr').filter({ hasText: text });
const button = name => page.getByRole('button', { name, exact: true });
const link = name => page.getByRole('link', { name, exact: true });
const textOnPage = async t => { await page.getByText(t, { exact: false }).first().waitFor(); };
const clickPost = async locator => { await Promise.all([page.waitForNavigation(), locator.click()]); };
const session = async () => {
    const c = await browser.newContext({ viewport: { width: 1360, height: 820 }, locale: 'id-ID', timezoneId: 'Asia/Jakarta' });
    const p = await c.newPage();
    p.setDefaultTimeout(7500);
    p.on('dialog', d => d.accept());
    p.on('pageerror', e => failures.push(e.message));
    p.on('response', r => {if (r.status() >= 500) failures.push(r.status() + ' ' + r.url());});
    return p;
};
async function login(role) {
    await goto('/login');
    await page.locator('#email').fill(role + '@shopease.test');
    await page.locator('#password').fill('ShopEase!2026');
    await clickPost(button('Masuk'));
    assert(!new URL(page.url()).pathname.endsWith('/login'));
}
async function fields(name, category) {
    await page.locator('#name').fill(name);
    if (category) await page.locator('#category').selectOption({ label: category });
    await page.locator('#price').fill('289000');
    await page.locator('#stock').fill('0');
    await page.locator('#description').fill('Ransel komuter dengan kompartemen laptop dan saku depan.');
}
const cat = 'Perlengkapan Kampus', product = 'Ransel Kampus Arunika';
const cover = await readFile(path.join(root,'shopease/app/Database/Seeds/images/backpack.webp'));
const detail = await readFile(path.join(root,'tests/detail-ransel.jpg'));
const upload = (name, mimeType, buffer) => ({ name, mimeType, buffer });
try {
    page = await session();
    const guest = await goto('/admin/products');
    assert.equal(new URL(page.url()).pathname,'/login'); assert.equal(guest.status(),200);
    await shot('01-login','Tamu diarahkan ke halaman masuk');
    pass('A01','Tamu diarahkan ke login.');
    await login('admin');
    await goto('/admin/catalog-dashboard');
    const dashboardNumbers = await page.locator('.metric strong').allTextContents();
    assert.deepEqual(dashboardNumbers,['8','4','6','2']);
    await shot('02-dashboard','Ringkasan awal dengan 8 produk dan 4 kategori');
    pass('P04','Dashboard menghitung produk, kategori, tersedia, dan tidak tersedia dari database.');

    await goto('/admin/products');
    assert.equal(await page.locator('tbody tr').count(),5);
    await shot('03-daftar-produk','Daftar produk dan navigasi halaman');
    await link('Berikutnya →').click(); await textOnPage('Halaman 2 dari 2');
    await shot('04-pagination','Halaman kedua dari daftar produk');
    pass('P01a','Pagination memisahkan delapan produk menjadi lima dan tiga baris.');
    assert.equal(await page.locator('tbody tr').count(),3);

    await goto('/admin/categories/create');
    await page.locator('#name').fill(cat); await page.locator('#description').fill('Tas dan perlengkapan kuliah.');
    await shot('05-tambah-kategori','Form tambah kategori praktikum');
    await clickPost(button('Simpan kategori')); await textOnPage('Kategori berhasil ditambahkan.');
    assert.equal(await row(cat).count(),1); pass('HOL01','Kategori baru tersimpan.');
    await row(cat).getByRole('link',{name:'Edit'}).click();
    await page.locator('#description').fill('Ransel dan kebutuhan harian mahasiswa.');
    await shot('06-edit-kategori','Pengubahan deskripsi kategori');
    await clickPost(button('Simpan kategori')); await textOnPage('Ransel dan kebutuhan harian mahasiswa.');
    await shot('07-daftar-kategori','Daftar kategori setelah perubahan');
    pass('A02','Edit kategori tercermin pada daftar.');

    await goto('/admin/products/create');
    await fields(product, null);
    await page.locator('form[enctype]').evaluate(f => { f.noValidate = true; });
    await clickPost(button('Simpan produk')); await textOnPage('Pilih kategori produk.');
    assert.equal(inspect().products,8); await shot('08-validasi-kategori','Validasi server saat kategori dikosongkan');
    pass('HOL03','Produk tanpa kategori ditolak tanpa penambahan baris.');
    await fields(product,cat);
    await page.locator('#images').setInputFiles([upload('ransel-utama.webp','image/webp',cover),upload('detail-ransel.jpg','image/jpeg',detail)]);
    assert.equal(await page.locator('#image-preview img').count(),2);
    await shot('09-preview-upload','Dua gambar tampil sebelum formulir produk disimpan');
    await clickPost(button('Simpan produk')); await textOnPage('Produk berhasil ditambahkan.');
    const created = row(product); assert.equal(await created.count(),1);
    assert.equal((await created.locator('td').allTextContents())[3].trim(),'0');
    assert.equal(inspect().product_images,10);
    await shot('10-produk-tersimpan','Produk baru tersimpan dengan stok nol');
    pass('HOL02','Produk baru tersimpan dan muncul di daftar.'); pass('HOL07','Nilai stok nol diterima.');
    await created.getByRole('link',{name:product}).click();
    assert.equal(await page.locator('.gallery img').count(),2);
    const srcs = await page.locator('.gallery img').evaluateAll(imgs => imgs.map(i => i.src));
    for (const u of srcs) {
        assert.equal((await page.request.get(u)).status(),200);
        assert(existsSync(path.join(root,'shopease/public',new URL(u).pathname)));
    }
    await shot('11-detail-galeri','Detail produk beserta dua berkas unggahan');
    pass('HOL06','Dua file tersimpan di disk dan dapat dibaca melalui HTTP.');
    await link('Edit produk').click();
    await clickPost(button('Jadikan utama')); await textOnPage('Gambar utama diperbarui.');
    assert.equal(await page.locator('.gallery article img').first().getAttribute('src'),srcs[1]);
    await shot('12-gambar-utama','Gambar kedua terpilih sebagai sampul produk');
    await page.locator('#price').fill('279000.50'); await page.locator('#stock').fill('4');
    await shot('13-edit-produk','Form edit menampilkan harga, stok, dan galeri yang telah disimpan');
    await clickPost(button('Simpan produk')); await textOnPage('Produk berhasil diperbarui.');
    assert((await row(product).innerText()).includes('279.000,50')); pass('HOL04','Perubahan harga desimal dan stok tersimpan.');
    await page.locator('#search').fill('Arunika'); await page.locator('#kategori').selectOption({label:cat});
    await clickPost(button('Terapkan')); assert.equal(await page.locator('tbody tr').count(),1); assert.equal(await row(product).count(),1);
    await shot('14-cari-filter','Pencarian nama dan filter kategori secara bersamaan');
    pass('P01b','Gabungan pencarian nama dan filter kategori mengembalikan produk yang tepat.');
    await goto('/catalog?q=Arunika'); await textOnPage(product); assert.equal(await page.locator('.product-art img').getAttribute('src'),srcs[1]);
    await shot('15-katalog-publik','Perubahan sampul dan harga terlihat di katalog pelanggan');
    pass('A03','Katalog publik membaca harga dan sampul terbaru.');
    await goto('/admin/categories');
    await clickPost(row(cat).getByRole('button',{name:'Hapus'})); await textOnPage('Kategori masih memiliki produk.');
    assert.equal(inspect().categories,5); await shot('16-kategori-terpakai','Penghapusan kategori yang masih dipakai ditolak');
    pass('HOL08','Kategori yang dipakai produk tetap tersimpan.');

    await goto('/admin/inventory?stok=low'); await row(product).waitFor();
    assert.equal(await page.locator('tbody tr').count(),3);
    await shot('17-stok-rendah','Produk dengan stok satu hingga lima unit');
    await row(product).locator('[name=stock]').fill('0'); await clickPost(row(product).getByRole('button',{name:'Simpan'})); await textOnPage('Stok berhasil diperbarui.');
    await goto('/admin/inventory?stok=empty'); assert.equal(await row(product).count(),1);
    assert.equal(await page.locator('tbody tr').count(),3); await shot('18-stok-habis','Produk berpindah ke kelompok stok habis setelah diperbarui');
    pass('P03','Perubahan stok memindahkan produk dari kelompok rendah ke kelompok habis.');
    await goto('/admin/products?q=Arunika'); await row(product).getByRole('link',{name:'Edit'}).click();
    await page.locator('#status').selectOption('tidak_tersedia'); await clickPost(button('Simpan produk'));
    await goto('/catalog?q=Arunika'); await textOnPage('Produk belum ditemukan');
    pass('A04','Status tidak tersedia menyembunyikan produk dari katalog publik.');
    await goto('/admin/products?q=Arunika'); await row(product).getByRole('link',{name:'Edit'}).click();
    await page.locator('#images').setInputFiles([upload('catatan.txt','text/plain',Buffer.from('catatan praktikum'))]);
    await page.locator('#price').fill('1'); await clickPost(button('Simpan produk')); await textOnPage('Gunakan gambar JPG');
    assert.equal(inspect().product_images,10); await shot('19-validasi-gambar','Penolakan jenis file yang tidak sesuai');
    await page.reload(); assert.equal(await page.locator('#price').inputValue(),'279000.50');
    pass('A05','Jenis file ditolak dan harga serta galeri tidak berubah.');
    await clickPost(page.locator('.gallery article').first().getByRole('button',{name:'Hapus gambar'}));
    await textOnPage('Gambar dihapus.'); assert.equal(await page.locator('.gallery article').count(),1);
    assert.equal(await page.locator('.gallery article img').getAttribute('src'),srcs[0]);
    assert.equal((await page.request.get(srcs[1])).status(),404); assert.equal(inspect().invalid_primary_groups,0);
    await shot('20-hapus-gambar','Satu gambar tersisa dan otomatis menjadi gambar utama');
    pass('P02','Preview, banyak gambar, ganti sampul, hapus individual, dan pemilihan sampul pengganti.');

    await goto('/admin/products?q=Arunika'); await clickPost(row(product).getByRole('button',{name:'Hapus'})); await textOnPage('Produk dan gambarnya berhasil dihapus.');
    assert.equal(inspect().products,8); assert.equal(inspect().product_images,8);
    assert.equal((await page.request.get(srcs[0])).status(),404);
    await shot('21-hapus-produk','Penghapusan produk beserta galeri selesai'); pass('HOL05','Produk, baris gambar, dan file fisik terhapus.');
    await goto('/admin/categories'); await clickPost(row(cat).getByRole('button',{name:'Hapus'})); await textOnPage('Kategori berhasil dihapus.');
    assert.equal(await row(cat).count(),0); await shot('22-hapus-kategori','Kategori kosong berhasil dihapus'); pass('A06','Kategori kosong dapat dihapus.');
    const csrf = await page.request.post(target('/admin/products/store'),{form:{name:'Contoh'}});
    assert.equal(csrf.status(),403); pass('A07','Permintaan perubahan tanpa token CSRF mendapat HTTP 403.');
    await page.context().close();
    page = await session(); await login('customer');
    const forbidden = await goto('/admin/products'); assert.equal(forbidden.status(),403);
    await shot('23-customer-403','Customer menerima halaman penolakan akses'); pass('HOL09','Customer mendapat HTTP 403 pada halaman admin.');
    await page.context().close();
    page = await session(); await login('operator');
    const allowed = await goto('/admin/products'); assert.equal(allowed.status(),200); await page.locator('.profile').getByText('Operator').waitFor();
    await shot('24-operator','Operator dapat mengakses halaman produk'); pass('HOL10','Operator mendapat HTTP 200 pada halaman produk.');
    await page.setViewportSize({width:390,height:844}); await goto('/admin/catalog-dashboard');
    assert(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth));
    await shot('25-mobile','Ringkasan katalog pada lebar layar 390 piksel'); pass('A08','Dashboard dapat dibaca pada viewport ponsel tanpa overflow halaman.');
    const after=inspect(); assert.equal(after.foreign_keys.length,2); assert.equal(after.invalid_primary_groups,0);
    assert.deepEqual([after.categories,after.products,after.product_images],[4,8,8]); pass('A09','Kedua foreign key terpasang dan data awal kembali utuh setelah pengujian.');
    assert.deepEqual(failures,[]);
    await writeFile(path.join(docs,'verifikasi.json'), JSON.stringify({
        time:new Date().toISOString(), os:os.type()+' '+os.release()+' '+os.arch(), browser:browser.version(),
        php:execFileSync('php',['-r','echo PHP_VERSION;'],{encoding:'utf8'}), base, before, after, checks, captures, errors:failures,
    },null,2)+'\n');
} catch (e) {
    if (page && !page.isClosed()) console.error('Halaman terakhir',page.url(),(await page.locator('body').innerText()).slice(0,2300));
    throw e;
} finally {
    await browser.close();
}
