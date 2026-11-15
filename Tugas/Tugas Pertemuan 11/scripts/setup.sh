#!/usr/bin/env bash
set -euo pipefail
TASK_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$TASK_DIR/shopease"
MODE="${1:-sqlite}"
if [[ "$MODE" != sqlite && "$MODE" != mysql ]]; then
  echo 'Pemakaian: bash scripts/setup.sh [sqlite|mysql]' >&2; exit 1
fi
php -r 'if (PHP_VERSION_ID < 80200) {fwrite(STDERR,"Perlu PHP >= 8.2\n");exit(1);} foreach (["intl","mbstring"] as $ext) {if (!extension_loaded($ext)) {fwrite(STDERR,"Ekstensi belum aktif: $ext\n");exit(1);}}'
if [[ "$MODE" == sqlite ]]; then
  php -r 'if (!extension_loaded("sqlite3")) {fwrite(STDERR,"Perlu ekstensi sqlite3\n");exit(1);}'
else
  php -r 'if (!extension_loaded("mysqli")) {fwrite(STDERR,"Perlu ekstensi mysqli\n");exit(1);}'
fi
if [[ ! -f .env ]]; then
  if [[ "$MODE" == sqlite ]]; then cp env .env; else
    cp env.mysql .env
    echo 'Template MySQL disalin ke shopease/.env. Isi kredensial dan buat database, lalu jalankan perintah ini lagi.'
    exit 0
  fi
else
  echo 'Memakai .env yang sudah ada; konfigurasi tidak ditimpa.'
fi
php spark migrate
php spark db:seed DemoUserSeeder
echo 'Selesai. Jalankan: cd shopease && php spark serve'
echo 'Browser: http://localhost:8080/'
