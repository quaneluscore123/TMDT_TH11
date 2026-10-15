#!/bin/bash
# TH13 buoc 13.1: kiem chung plugin do luong bang mot khach moi (khong dang nhap) tu link UTM.
# Ky vong: session 1 · view_item 1 · add_to_cart 1 · begin_checkout 1 · purchase 1 (tai lai trang cam on 2 lan van chi 1 don).
# Chay trong thu muc 01-cua-hang:  bash tests/kiem-tra-su-kien.sh
set -euo pipefail

H=https://butchixanh.local
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130"
J=$(mktemp)
c() { curl -sk -c "$J" -b "$J" -A "$UA" "$@"; }

echo "### Thoi diem: $(date '+%Y-%m-%d %H:%M:%S')"
c -o /dev/null "$H/?utm_source=thu-nghiem&utm_medium=kiem-chung&utm_campaign=b13"
PAGE=$(c "$H/shop/")
URL=$(echo "$PAGE" | grep -o 'href="https://butchixanh.local/product/[^"]*"' | head -1 | cut -d'"' -f2)
PID=$(echo "$PAGE" | grep -o 'data-product_id="[0-9]*"' | head -1 | grep -o '[0-9]*')
c -o /dev/null "$URL"                                   # view_item
c -o /dev/null "$H/?add-to-cart=$PID"                   # add_to_cart
c -o /dev/null "$H/checkout/"                           # begin_checkout
SID=$(grep n25_sid "$J" | awk '{print $7}')
# Dat don COD cho chinh phien nay roi mo trang cam on HAI lan
RECEIVED=$(MSYS_NO_PATHCONV=1 docker compose run --rm -T wpcli wp eval "\$o=wc_create_order(); \$o->add_product(wc_get_product($PID),1); \$o->set_payment_method('cod'); \$o->set_billing_email('kiem-thu@example.com'); \$o->calculate_totals(); \$o->update_status('processing'); echo \$o->get_checkout_order_received_url();" 2>/dev/null | tail -1)
c -o /dev/null "$RECEIVED"
c -o /dev/null "$RECEIVED"
rm -f "$J"
echo "Phien thu: $SID · san pham #$PID"
MSYS_NO_PATHCONV=1 docker compose exec -T db sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" -t -e \"SELECT event, COUNT(*) AS so_lan, src, medium, campaign FROM shop.wp_nhom25_events WHERE sid='$SID' GROUP BY event, src, medium, campaign ORDER BY FIELD(event,'session','view_item','add_to_cart','begin_checkout','purchase')\"" 2>/dev/null
