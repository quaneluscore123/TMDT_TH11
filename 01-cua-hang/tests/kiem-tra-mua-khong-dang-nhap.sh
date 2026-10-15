#!/bin/bash
# Kiem tra khach vang lai mua hang KHONG can dang nhap / tao tai khoan (COD), qua Store API ma trang thanh toan dung.
# Chay tu thu muc 01-cua-hang:  bash tests/kiem-tra-mua-khong-dang-nhap.sh [https://ten-mien]
# Mac dinh thu tren may (butchixanh.local). Don thu se bi xoa va tra lai ton kho o cuoi (chi lam duoc tren may).
set -uo pipefail
SITE="${1:-https://butchixanh.local}"
SP=11   # Bút bi gel 0.5mm
J=$(mktemp); H=$(mktemp)
trap 'rm -f "$J" "$H"' EXIT
api() { curl -sk -c "$J" -b "$J" -D "$H" -H "Content-Type: application/json" -H "Nonce: ${NONCE:-}" "$@"; }

echo "### Thoi diem: $(date '+%Y-%m-%d %H:%M:%S') · $SITE"
NONCE=""
api "$SITE/wp-json/wc/store/v1/cart" -o /dev/null
NONCE=$(grep -i '^nonce:' "$H" | tail -1 | awk '{print $2}' | tr -d '\r')
echo "1. Mo gio hang khi chua dang nhap: $([ -n "$NONCE" ] && echo 'DAT (co ma phien gio hang)' || echo 'KHONG DAT')"

api -X POST "$SITE/wp-json/wc/store/v1/cart/add-item" -d "{\"id\":$SP,\"quantity\":1}" -o /dev/null -w "2. Them san pham #$SP vao gio: HTTP %{http_code}\n"
NONCE=$(grep -i '^nonce:' "$H" | tail -1 | awk '{print $2}' | tr -d '\r')

DIA_CHI='{"first_name":"Khach","last_name":"Vang Lai","address_1":"1 Trang Tien","city":"Hoan Kiem","state":"HN","postcode":"100000","country":"VN","email":"khach.thu@example.com","phone":"0900000000"}'
KQ=$(api -X POST "$SITE/wp-json/wc/store/v1/checkout" \
    -d "{\"billing_address\":$DIA_CHI,\"shipping_address\":$DIA_CHI,\"payment_method\":\"cod\",\"create_account\":false}")
DON=$(echo "$KQ" | grep -oE '"order_id":[0-9]+' | head -1 | cut -d: -f2)
TT=$(echo "$KQ" | grep -oE '"status":"[a-z-]+"' | head -1 | cut -d'"' -f4)
if [ -n "$DON" ]; then
    echo "3. Dat don COD khong dang nhap: DAT - don #$DON, trang thai $TT"
else
    echo "3. Dat don COD khong dang nhap: KHONG DAT"; echo "$KQ" | head -c 400; echo; exit 1
fi

if [[ "$SITE" == *butchixanh.local* ]]; then
    MSYS_NO_PATHCONV=1 docker compose run --rm -T wpcli wp eval "
        \$o = wc_get_order($DON);
        echo '4. Kiem tra don: khach la ' . (\$o->get_customer_id() ? 'tai khoan #' . \$o->get_customer_id() : 'khach vang lai (khong tao tai khoan)') . ', email ' . \$o->get_billing_email() . PHP_EOL;
        foreach (\$o->get_items() as \$it) { \$p = \$it->get_product(); if (\$p && \$p->managing_stock() && \$o->get_data_store()->get_stock_reduced(\$o->get_id())) { wc_update_product_stock(\$p, \$it->get_quantity(), 'increase'); } }
        global \$wpdb; \$wpdb->delete(\$wpdb->prefix . 'nhom25_events', ['order_id' => $DON]);
        \$o->delete(true);
        echo '5. Da xoa don thu, tra lai ton kho.' . PHP_EOL;" 2>/dev/null
fi
