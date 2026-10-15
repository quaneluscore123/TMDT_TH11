<?php
// Kiem thu xac nhan don VNPay qua ReturnUrl (che do cho hosting chan IPN, bat bang NHOM25_VNPAY_XAC_NHAN_QUA_RETURN).
// Gia lap khach quay ve tu VNPay voi chu ky that/gia, doi chieu trang thai don va ton kho.
// Chay (trong 01-cua-hang): docker compose run --rm wpcli wp eval-file /scripts/tests/kiem-tra-vnpay-qua-return.php
// Don thu duoc xoa va ton kho tra lai nhu cu khi chay xong.

$secret = getenv('VNP_HASH_SECRET');
if (!$secret) {
    exit("Thieu VNP_HASH_SECRET trong .env\n");
}
if (!defined('NHOM25_VNPAY_XAC_NHAN_QUA_RETURN')) {
    define('NHOM25_VNPAY_XAC_NHAN_QUA_RETURN', true); // bat che do giong tren hosting
}
WC()->frontend_includes();
if (!WC()->session) {
    WC()->session = new WC_Session_Handler();
    WC()->session->init();
}
$product = wc_get_products(['limit' => 1, 'status' => 'publish', 'orderby' => 'ID', 'order' => 'ASC'])[0];
$stock0 = (int) $product->get_stock_quantity();
printf("San pham thu: #%d %s, ton kho ban dau %d\n\n", $product->get_id(), $product->get_name(), $stock0);

function n25r_order($product)
{
    $o = wc_create_order();
    $o->add_product($product, 1);
    $o->set_payment_method('woo_vnpay');
    $o->set_billing_email('kiem-thu@example.com');
    $o->calculate_totals();
    $o->update_status('pending');
    return $o;
}

// Khach quay ve: goi dung ham ReturnUrl that, chan lenh chuyen huong de doc ket qua
function n25r_return($order, $secret, $overrides = [], $sign_with = null)
{
    $p = [
        'vnp_Amount' => (string) round($order->get_total() * 100),
        'vnp_BankCode' => 'NCB',
        'vnp_OrderInfo' => 'DH' . $order->get_id(),
        'vnp_ResponseCode' => '00',
        'vnp_TmnCode' => getenv('VNP_TMN_CODE'),
        'vnp_TransactionNo' => (string) (15000000 + $order->get_id()),
        'vnp_TransactionStatus' => '00',
        'vnp_TxnRef' => $order->get_id() . '-' . time(),
    ];
    $p = array_merge($p, $overrides);
    $p['vnp_SecureHash'] = Nhom25_VNPay_IPN::sign($p, $sign_with ?? $secret);
    $_GET = array_merge(['action' => 'vnpay_response'], $p);
    $chan = function ($loc) { throw new RuntimeException($loc); };
    add_filter('wp_redirect', $chan, 1);
    try {
        (new VNPAYResponse())->checkResponse();
    } catch (RuntimeException $e) {
        // da chuyen huong
    }
    remove_filter('wp_redirect', $chan, 1);
    return $p;
}

$rows = [];
$check = function ($name, $order, $want_status, $want_stock) use (&$rows, $product) {
    $order = wc_get_order($order->get_id());
    $stock = (int) wc_get_product($product->get_id())->get_stock_quantity();
    $ok = $order->get_status() === $want_status && $stock === $want_stock;
    $rows[] = [$ok ? 'DAT' : 'KHONG DAT', $name, $order->get_status(), $stock];
};

$o1 = n25r_order($product);
n25r_return($o1, $secret, [], 'khoa-cua-ke-gian');
$check('1. Ke gian sua duong dan, ky bang khoa sai', $o1, 'pending', $stock0);

n25r_return($o1, $secret, ['vnp_Amount' => '100']);
$check('2. Sai so tien', $o1, 'pending', $stock0);

$o3 = n25r_order($product);
n25r_return($o3, $secret, ['vnp_ResponseCode' => '24', 'vnp_TransactionStatus' => '02']);
$check('3. Khach huy thanh toan (ma 24)', $o3, 'failed', $stock0);

$o4 = n25r_order($product);
$p4 = n25r_return($o4, $secret);
$check('4. Thanh toan thanh cong', $o4, 'processing', $stock0 - 1);

$_GET = $p4;
n25r_return($o4, $secret, ['vnp_TxnRef' => $p4['vnp_TxnRef']]);
$check('5. Khach tai lai trang quay ve (lan 2)', $o4, 'processing', $stock0 - 1);

printf("%-10s %-44s %-12s %s\n", 'Ket qua', 'Truong hop', 'Trang thai', 'Ton kho');
foreach ($rows as $r) {
    printf("%-10s %-44s %-12s %d\n", $r[0], $r[1], $r[2], $r[3]);
}

// Don dep: xoa don thu, tra ton kho
foreach ([$o1, $o3, $o4] as $o) {
    wc_get_order($o->get_id())->delete(true);
}
wc_update_product_stock($product, $stock0, 'set');
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->prefix}nhom25_events WHERE value IS NOT NULL AND order_id IS NOT NULL AND order_id NOT IN (SELECT ID FROM {$wpdb->posts})");
printf("\nDa xoa don thu, ton kho tra ve %d.\n", (int) wc_get_product($product->get_id())->get_stock_quantity());
