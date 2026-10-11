<?php
// Kiem thu xu ly IPN VNPay (ly thuyet buoi 4 - diem cong; thuc hanh buoi 12 truong hop 5 va 6).
// Tao don thu, gia lap thong bao VNPay voi chu ky that va chu ky gia, doi chieu trang thai don va ton kho.
// Chay (trong 01-cua-hang): docker compose run --rm wpcli wp eval-file /scripts/tests/kiem-tra-ipn-vnpay.php
// Don thu duoc xoa va ton kho tra lai nhu cu khi chay xong.

$secret = getenv('VNP_HASH_SECRET');
if (!$secret) {
    exit("Thieu VNP_HASH_SECRET trong .env\n");
}
$product = wc_get_products(['limit' => 1, 'status' => 'publish', 'orderby' => 'ID', 'order' => 'ASC'])[0];
$stock0 = (int) $product->get_stock_quantity();
printf("San pham thu: #%d %s, ton kho ban dau %d\n\n", $product->get_id(), $product->get_name(), $stock0);

function nhom25_new_order($product)
{
    $o = wc_create_order();
    $o->add_product($product, 1);
    $o->set_payment_method('woo_vnpay');
    $o->set_billing_email('kiem-thu@example.com');
    $o->calculate_totals();
    $o->update_status('pending');
    return $o;
}

function nhom25_ipn($order, $secret, $overrides = [], $sign_with = null)
{
    $p = [
        'vnp_Amount' => (string) round($order->get_total() * 100),
        'vnp_BankCode' => 'NCB',
        'vnp_OrderInfo' => 'DH' . $order->get_id(),
        'vnp_ResponseCode' => '00',
        'vnp_TmnCode' => getenv('VNP_TMN_CODE'),
        'vnp_TransactionNo' => (string) (14000000 + $order->get_id()),
        'vnp_TransactionStatus' => '00',
        'vnp_TxnRef' => $order->get_id() . '-' . time(),
    ];
    $p = array_merge($p, $overrides);
    $p['vnp_SecureHash'] = Nhom25_VNPay_IPN::sign($p, $sign_with ?? $secret);
    return Nhom25_VNPay_IPN::process($p, $secret);
}

$rows = [];
$check = function ($name, $got, $want_code, $order, $want_status, $want_stock) use (&$rows, $product) {
    $order = wc_get_order($order->get_id());
    $stock = (int) wc_get_product($product->get_id())->get_stock_quantity();
    $ok = $got[0] === $want_code && $order->get_status() === $want_status && $stock === $want_stock;
    $rows[] = [$ok ? 'DAT' : 'KHONG DAT', $name, "{$got[0]} {$got[1]}", $order->get_status(), $stock];
};

// 1. Thong bao gia mao: ke gian tu ky bang khoa sai
$o1 = nhom25_new_order($product);
$check('1. Chu ky gia mao (khoa sai)', nhom25_ipn($o1, $secret, [], 'khoa-cua-ke-gian'), '97', $o1, 'pending', $stock0);

// 2. Chu ky dung nhung bi sua so tien (vi du ap ma giam gia sau khi cap phep)
$check('2. Sai so tien', nhom25_ipn($o1, $secret, ['vnp_Amount' => '100']), '04', $o1, 'pending', $stock0);

// 3. Ngan hang tu choi (the bi khoa)
$o3 = nhom25_new_order($product);
$check('3. Ngan hang tu choi (ma 11)', nhom25_ipn($o3, $secret, ['vnp_ResponseCode' => '11', 'vnp_TransactionStatus' => '02']), '00', $o3, 'failed', $stock0);

// 4. Thanh cong
$check('4. Thanh toan thanh cong', nhom25_ipn($o1, $secret), '00', $o1, 'processing', $stock0 - 1);

// 5. VNPay gui lai cung thong bao (truong hop 5b): khong tru kho lan hai
$check('5. Thong bao gui lap lai', nhom25_ipn($o1, $secret), '02', $o1, 'processing', $stock0 - 1);

// 6. Het hang khi dang tra (truong hop 6): ton kho ve 0 truoc khi IPN toi
$o6 = nhom25_new_order($product);
wc_update_product_stock($product->get_id(), 0, 'set');
$check('6. Het hang khi dang tra tien', nhom25_ipn($o6, $secret), '00', $o6, 'on-hold', 0);

printf("%-10s %-32s %-28s %-11s %s\n", 'Ket qua', 'Truong hop', 'Phan hoi gui VNPay', 'Trang thai', 'Ton kho');
foreach ($rows as $r) {
    printf("%-10s %-32s %-28s %-11s %d\n", ...$r);
}

// Don dep: xoa don thu, tra ton kho
foreach ([$o1, $o3, $o6] as $o) {
    wc_get_order($o->get_id())->delete(true);
}
wc_update_product_stock($product->get_id(), $stock0, 'set');
printf("\nDa xoa don thu, ton kho tra ve %d.\n", (int) wc_get_product($product->get_id())->get_stock_quantity());
