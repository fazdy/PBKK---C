#!/bin/sh
set -eu
cd "$(dirname "$0")/../shopease"
mode="${1:-mysql}"
case "$mode" in
  mysql) config=env; driver=MySQLi ;;
  sqlite) config=env.sqlite; driver=SQLite3 ;;
  *) echo "Gunakan: bash scripts/setup.sh mysql atau sqlite"; exit 2 ;;
esac
if [ ! -f .env ]; then
    cp "$config" .env
fi
configured=$(sed -n 's/^database\.default\.DBDriver[[:space:]]*=[[:space:]]*//p' .env | tr -d " '\r\"")
if [ "$configured" != "$driver" ]; then
    echo "Mode yang diminta $driver, tetapi .env berisi $configured. Sesuaikan .env sebelum melanjutkan." >&2
    exit 1
fi
mkdir -p writable/cache writable/logs writable/session writable/uploads writable/debugbar public/uploads/products
run_spark() {
    result=$(php spark "$@" 2>&1) || { echo "$result" >&2; return 1; }
    echo "$result"
    if echo "$result" | grep -Eq '^\[.*(Exception|Error)\]'; then return 1; fi
}
run_spark migrate
run_spark db:seed DemoUserSeeder
run_spark db:seed ProductDemoSeeder
