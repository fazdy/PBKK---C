#!/usr/bin/env python3
"""Jalankan MySQL sementara tanpa mengubah service/database pengguna.
Membutuhkan mysqld, mysql, PHP, Node dan dependensi Playwright.
"""
from pathlib import Path
import datetime, json, os, shutil, subprocess, tempfile, time

TASK=Path(__file__).resolve().parents[1]

def main():
    mysqld=shutil.which('mysqld')
    if not mysqld:
        candidate=Path('/usr/local/opt/mysql/bin/mysqld')
        if candidate.exists(): mysqld=str(candidate)
    mysql=shutil.which('mysql')
    if not mysqld or not mysql: raise SystemExit('Pasang MySQL: brew install mysql; pastikan mysqld dan mysql tersedia.')
    with tempfile.TemporaryDirectory(prefix='se11-',dir='/tmp') as tmp:
        root=Path(tmp); data=root/'data'; data.mkdir(); socket=str(root/'mysql.sock')
        base=[mysqld,'--no-defaults',f'--datadir={data}']
        init=subprocess.run(base+['--initialize-insecure'],capture_output=True,text=True)
        if init.returncode: raise RuntimeError(init.stdout+init.stderr)
        log=(root/'mysql.log').open('w')
        server=subprocess.Popen(base+[f'--socket={socket}',f'--pid-file={root}/mysql.pid','--skip-networking','--mysqlx=OFF','--innodb-buffer-pool-size=64M','--max-connections=10'],stdout=log,stderr=log)
        client=[mysql,'--no-defaults','--protocol=SOCKET',f'--socket={socket}','-u','root','--batch','--skip-column-names']
        try:
            for _ in range(120):
                if server.poll() is not None: raise RuntimeError((root/'mysql.log').read_text())
                ready=subprocess.run(client+['-e','SELECT 1'],capture_output=True)
                if ready.returncode==0: break
                time.sleep(.25)
            else: raise RuntimeError('MySQL sementara belum siap.')
            version=subprocess.check_output(client+['-e','SELECT VERSION()'],text=True).strip()
            subprocess.run(client+['-e','CREATE DATABASE db_shopease_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE db_shopease_browser CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'],check=True)
            env={**os.environ,'SHOPEASE_TEST_MYSQL_SOCKET':socket}
            subprocess.run(['python3','tests/test_auth.py','--output','docs/hasil-mysql.json'],cwd=TASK,env=env,check=True)
            subprocess.run(['node','tests/capture.cjs'],cwd=TASK,env=env,check=True)
            result=json.loads((TASK/'docs/hasil-mysql.json').read_text())
            result['mysql_version']=version
            result['server']='isolated mysqld, Unix socket, networking disabled, 64 MiB InnoDB buffer pool'
            (TASK/'docs/hasil-mysql.json').write_text(json.dumps(result,ensure_ascii=False,indent=2)+'\n')
            print('MySQL '+version+': integrasi dan browser HTTP selesai.')
        finally:
            server.terminate()
            try: server.wait(timeout=20)
            except subprocess.TimeoutExpired: server.kill();server.wait()
            log.close()

if __name__=='__main__': main()
