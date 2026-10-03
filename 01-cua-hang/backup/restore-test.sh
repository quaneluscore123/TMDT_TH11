#!/bin/bash
# Thu phuc hoi ban sao luu vao CSDL tam "shop_restore_test" va so sanh voi CSDL dang chay.
# Dung: bash restore-test.sh ./bak-YYYYmmdd-HHMMSS
set -euo pipefail
export MSYS_NO_PATHCONV=1
B=${1:?Can duong dan thu muc sao luu}
(cd "$B" && sha256sum -c SHA256SUMS)
M="docker exec -i 01-cua-hang-db-1 mysql -uroot -proot123"
$M -e "DROP DATABASE IF EXISTS shop_restore_test; CREATE DATABASE shop_restore_test CHARACTER SET utf8mb4;"
$M shop_restore_test < "$B/shop.sql"
Q="SELECT 'san pham', COUNT(*) FROM wp_posts WHERE post_type='product' AND post_status='publish'
UNION ALL SELECT 'don hang', COUNT(*) FROM wp_posts WHERE post_type='shop_order'
UNION ALL SELECT 'trang', COUNT(*) FROM wp_posts WHERE post_type='page'
UNION ALL SELECT 'nguoi dung', COUNT(*) FROM wp_users
UNION ALL SELECT 'tuy chon', COUNT(*) FROM wp_options
UNION ALL SELECT 'tong ton kho', COALESCE(SUM(meta_value),0) FROM wp_postmeta WHERE meta_key='_stock'
UNION ALL SELECT 'checksum posts', SUM(CRC32(CONCAT(ID,post_title,post_status))) FROM wp_posts WHERE post_type IN ('product','page')"
echo "--- Ban phuc hoi (shop_restore_test) ---"; $M -N shop_restore_test -e "$Q" > /tmp/r1.txt; cat /tmp/r1.txt
echo "--- CSDL dang chay (shop) ---";           $M -N shop -e "$Q" > /tmp/r2.txt; cat /tmp/r2.txt
mkdir -p /tmp/rt && tar -xzf "$B/wp-content.tgz" -C /tmp/rt && echo "So file uploads giai nen: $(find /tmp/rt/wp-content/uploads -type f | wc -l) / dang chay: $(find ../wp-content/uploads -type f | wc -l)"
if diff /tmp/r1.txt /tmp/r2.txt >/dev/null; then echo "KET QUA: PHUC HOI KHOP 100%"; else echo "KET QUA: CO SAI KHAC (xem tren)"; fi
$M -e "DROP DATABASE shop_restore_test;"
