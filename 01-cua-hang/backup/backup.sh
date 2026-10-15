#!/bin/bash
# Sao luu CSDL + wp-content. Chay trong thu muc 01-cua-hang/backup:  bash backup.sh
set -euo pipefail
# Doc mat khau tu ../.env (khong ghi cung trong ma nguon)
set -a; . ../.env; set +a
export MSYS_NO_PATHCONV=1
TS=$(date +%Y%m%d-%H%M%S)
OUT=./bak-$TS
mkdir -p "$OUT"
docker exec 01-cua-hang-db-1 mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers shop > "$OUT/shop.sql"
tar -czf "$OUT/wp-content.tgz" -C .. wp-content/uploads wp-content/mu-plugins
gzip -c "$OUT/shop.sql" > "$OUT/Nhom25_backup.sql.gz"   # ten tep nop buoi 15 (KHONG dua len git)
(cd "$OUT" && sha256sum shop.sql wp-content.tgz Nhom25_backup.sql.gz > SHA256SUMS)
echo "Da sao luu vao $OUT"; ls -la "$OUT"
