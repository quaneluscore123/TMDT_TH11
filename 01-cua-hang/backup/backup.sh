#!/bin/bash
# Sao luu CSDL + wp-content. Chay trong thu muc 01-cua-hang/backup:  bash backup.sh
set -euo pipefail
export MSYS_NO_PATHCONV=1
TS=$(date +%Y%m%d-%H%M%S)
OUT=./bak-$TS
mkdir -p "$OUT"
docker exec 01-cua-hang-db-1 mysqldump -uroot -proot123 --single-transaction --routines --triggers shop > "$OUT/shop.sql"
tar -czf "$OUT/wp-content.tgz" -C .. wp-content/uploads wp-content/mu-plugins
(cd "$OUT" && sha256sum shop.sql wp-content.tgz > SHA256SUMS)
echo "Da sao luu vao $OUT"; ls -la "$OUT"
