/* Browser end-to-end terhadap server HTTP PHP nyata, tanpa request interception. */
const fs = require('node:fs/promises');
const path = require('node:path');
const os = require('node:os');
const {spawn, execFile} = require('node:child_process');
const {promisify} = require('node:util');
const run = promisify(execFile);

async function main(playwright) {
  const task = path.resolve(__dirname, '..');
  const app = path.join(task,'shopease');
  const out = path.join(task,'docs','screenshots');
  const writable = await fs.mkdtemp(path.join(os.tmpdir(),'shopease11-capture-'));
  for (const dir of ['cache','logs','session','uploads','debugbar']) await fs.mkdir(path.join(writable,dir));
  const env = {...process.env,SHOPEASE_WRITABLE_DIR:writable,CI_ENVIRONMENT:'development',
    'database.default.DBDriver':process.env.SHOPEASE_TEST_MYSQL_SOCKET?'MySQLi':'SQLite3',
    'database.default.hostname':process.env.SHOPEASE_TEST_MYSQL_SOCKET||'127.0.0.1',
    'database.default.username':process.env.SHOPEASE_TEST_MYSQL_SOCKET?'root':'',
    'database.default.password':'',
    'database.default.database':process.env.SHOPEASE_TEST_MYSQL_SOCKET?'db_shopease_browser':'db_shopease.sqlite',
    'app.baseURL':'http://127.0.0.1:8082/'};
  let browser, server;
  const evidence = {mode:'Chrome + real HTTP php spark serve / '+env['database.default.DBDriver']+' / isolated database', screenshots:[], assertions:[], redirects:[], httpErrors:[], consoleErrors:[]};
  try {
    // Gagal sebelum pengujian bila port digunakan proses lain.
    await new Promise((resolve,reject)=>{
      const probe=require('node:net').createServer();
      probe.once('error',reject);
      probe.listen(8082,'127.0.0.1',()=>probe.close(resolve));
    });
    for (const args of [['spark','migrate'],['spark','db:seed','DemoUserSeeder']]) await run('php',args,{cwd:app,env});
    const launch = {headless:true};
    if (process.env.CHROME_PATH) launch.executablePath=process.env.CHROME_PATH;
    browser = await playwright.chromium.launch(launch);
    evidence.browserVersion = browser.version();
    const context = await browser.newContext({viewport:{width:1440,height:1000},deviceScaleFactor:1});
    server = spawn('php',['spark','serve','--host','127.0.0.1','--port','8082'],{cwd:app,env,detached:true,stdio:['ignore','pipe','pipe']});
    let serverLog='';
    server.stdout.on('data',x=>serverLog+=x);server.stderr.on('data',x=>serverLog+=x);
    await new Promise((resolve,reject)=>{server.once('spawn',resolve);server.once('error',reject);});
    let ready=false;
    for(let i=0;i<60;i++) {
      if(server.exitCode!==null)throw new Error(serverLog);
      try {const response=await fetch('http://127.0.0.1:8082/login');if(response.status===200){ready=true;break;}}catch{}
      await new Promise(resolve=>setTimeout(resolve,250));
    }
    if(!ready)throw new Error('Server HTTP belum siap: '+serverLog);
    evidence.phpVersion=(await run('php',['-r','echo PHP_VERSION;'])).stdout;
    evidence.playwrightVersion=require('playwright/package.json').version;
    evidence.database=env['database.default.DBDriver'];
    context.on('response',response=>{
      if(response.status()>=500)evidence.httpErrors.push({url:response.url(),status:response.status()});
      if(response.status()>=300 && response.status()<400)evidence.redirects.push({from:response.url(),status:response.status()});
    });
    let page=await context.newPage();
    page.on('pageerror',e=>evidence.consoleErrors.push(e.message));
    const assert=async(name,value)=>{evidence.assertions.push({name,passed:!!value});if(!value)throw new Error(name);};
    const shot=async(name)=>{await page.screenshot({path:path.join(out,name),fullPage:true});evidence.screenshots.push({name,url:page.url(),viewport:page.viewportSize()});};
    const navigateAfter=async(click)=>Promise.all([page.waitForNavigation(),click()]);
    const submit=async(action)=>navigateAfter(()=>page.locator(`form[action$="/${action}"] button[type="submit"]`).click());
    const login=async(email)=>{await page.goto('http://127.0.0.1:8082/login');await page.locator('#email').fill(email);await page.locator('#password').fill('ShopEase!2026');await submit('login');};
    const logout=async()=>navigateAfter(()=>page.locator('form[action$="/logout"] button').click());
    await page.goto('http://127.0.0.1:8082/register'); await shot('01-registrasi.png');
    await page.locator('#name').fill('Faizal Aldy Armiriawan');await page.locator('#email').fill('faizal.demo@shopease.test');
    await page.locator('#password').fill('ShopEase!2026');await page.locator('#password_confirm').fill('Berbeda!2026');
    await submit('register');await assert('Konfirmasi berbeda menampilkan validasi',await page.getByText('Konfirmasi password tidak sama.',{exact:true}).isVisible());await shot('02-validasi-registrasi.png');
    await page.locator('#password').fill('ShopEase!2026');await page.locator('#password_confirm').fill('ShopEase!2026');await submit('register');
    await assert('Registrasi redirect ke login',page.url().endsWith('/login'));await shot('03-registrasi-berhasil.png');
    await page.locator('#email').fill('faizal.demo@shopease.test');await page.locator('#password').fill('Salah!2026');await submit('login');
    await assert('Login salah ditolak',await page.getByText('Email atau password salah.',{exact:true}).isVisible());await shot('04-login-ditolak.png');
    await page.locator('#password').fill('ShopEase!2026');await submit('login');
    await assert('Customer masuk ke beranda',page.url()==='http://127.0.0.1:8082/');await shot('05-beranda-customer.png');
    await page.goto('http://127.0.0.1:8082/account');await assert('Nama tampil di profil',await page.locator('dd').filter({hasText:'Faizal Aldy Armiriawan'}).isVisible());await shot('06-profil-customer.png');
    const denied=await page.goto('http://127.0.0.1:8082/admin');await assert('Customer ditolak dari admin dengan 403',denied.status()===403);await shot('07-akses-ditolak.png');
    await logout();await shot('10-logout.png');
    await login('admin@shopease.test');await assert('Admin menuju dashboard',page.url().endsWith('/admin'));await shot('08-dashboard-admin.png');await logout();
    await login('operator@shopease.test');await assert('Operator menuju dashboard',page.url().endsWith('/operator'));await shot('09-dashboard-operator.png');await logout();
    await page.setViewportSize({width:390,height:844});await page.goto('http://127.0.0.1:8082/login');
    await page.locator('#password').fill('Contoh!2026');await page.locator('.toggle-password').click();
    await assert('Tombol lihat password berfungsi',await page.locator('#password').getAttribute('type')==='text');await page.locator('.toggle-password').click();await page.locator('#password').fill('');
    await assert('Login mobile tanpa overflow horizontal',await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth));await shot('11-login-mobile.png');
    await page.goto('http://127.0.0.1:8082/register');await assert('Registrasi mobile tanpa overflow horizontal',await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth));await shot('12-registrasi-mobile.png');
    await page.setViewportSize({width:320,height:740});await page.goto('http://127.0.0.1:8082/register');await assert('Registrasi 320 px tanpa overflow horizontal',await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth));
    await assert('Tidak ada kesalahan JavaScript',evidence.consoleErrors.length===0);await assert('Tidak ada respons HTTP 500',evidence.httpErrors.length===0);
    evidence.executedAt=new Date().toISOString();
    await fs.writeFile(path.join(task,'docs','hasil-browser.json'),JSON.stringify(evidence,null,2)+'\n');
    return evidence;
  } finally {
    if(browser)await browser.close();
    if(server && server.pid){try{process.kill(-server.pid,'SIGTERM');}catch{}}
    await fs.rm(writable,{recursive:true,force:true});
  }
}
module.exports={main};
if(require.main===module)main(require('playwright')).then(r=>console.log(JSON.stringify(r,null,2))).catch(e=>{console.error(e);process.exitCode=1;});
