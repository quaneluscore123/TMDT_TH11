<?php
/*
 * Plugin Name: Nhom25 - Khoa bi mat tu bien moi truong
 * Description: Khoa VNPay va tai khoan VietQR doc tu bien moi truong (file .env), khong luu trong CSDL hay ma nguon.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Doc khoa: uu tien bien moi truong (.env cua ban Docker), neu khong co thi doc hang so cung ten
 * trong wp-config.php (hosting chia se nhu InfinityFree khong co bien moi truong).
 */
function nhom25_env($name)
{
    $v = getenv($name);
    if ($v === false || $v === '') {
        $v = defined($name) ? constant($name) : '';
    }
    return trim((string) $v);
}

// Chua co khoa VNPay (vi du ban public chua cau hinh) thi an cong VNPay de khong co don treo o "Cho thanh toan"
// Chua co so tai khoan VietQR THAT thi an VietQR: khong bao gio hien so tai khoan bia cho khach chuyen tien
add_filter('woocommerce_available_payment_gateways', function ($gateways) {
    if (isset($gateways['woo_vnpay']) && (nhom25_env('VNP_TMN_CODE') === '' || nhom25_env('VNP_HASH_SECRET') === '')) {
        unset($gateways['woo_vnpay']);
    }
    if (nhom25_env('VIETQR_ACCOUNT_NUMBER') === '' || nhom25_env('VIETQR_ACCOUNT_NAME') === '') {
        foreach (array_keys($gateways) as $id) {
            if (stripos($id, 'vietqr') !== false) {
                unset($gateways[$id]);
            }
        }
    }
    return $gateways;
});

// VNPay: ghi de ma TmnCode va HashSecret khi doc cau hinh
add_filter('option_woocommerce_woo_vnpay_settings', function ($s) {
    if (!is_array($s)) {
        return $s;
    }
    $s['vnp_TmnCode'] = nhom25_env('VNP_TMN_CODE');
    $s['vnp_HashSecret'] = nhom25_env('VNP_HASH_SECRET');
    return $s;
});

// Khong de khoa bi luu lai vao CSDL khi quan tri vien bam Luu o trang cai dat
add_filter('pre_update_option_woocommerce_woo_vnpay_settings', function ($new) {
    if (is_array($new)) {
        $new['vnp_TmnCode'] = '';
        $new['vnp_HashSecret'] = '';
    }
    return $new;
});

// VietQR: so tai khoan nhan tien
add_filter('option_woocommerce_vietqr_settings', function ($s) {
    if (!is_array($s)) {
        $s = [];
    }
    $bin = nhom25_env('VIETQR_BANK_BIN') ?: '970436';
    $acc_num = nhom25_env('VIETQR_ACCOUNT_NUMBER');
    $acc_name = nhom25_env('VIETQR_ACCOUNT_NAME');

    $s['bank_bin'] = $bin;
    $s['bank_id'] = $bin; // VietQR plugin expects 'bank_id'
    $s['bank_name'] = 'Vietcombank';
    $s['account_number'] = $acc_num;
    $s['account_name'] = $acc_name;
    return $s;
});

// VietQR: danh sach tai khoan ngan hang de plugin render thankyou page va tao QR code
add_filter('option_woocommerce_vietqr_accounts', function ($accounts) {
    $bin = nhom25_env('VIETQR_BANK_BIN') ?: '970436';
    $acc_num = nhom25_env('VIETQR_ACCOUNT_NUMBER');
    $acc_name = nhom25_env('VIETQR_ACCOUNT_NAME');

    return [
        [
            'account_name'   => $acc_name,
            'account_number' => $acc_num,
            'bank_name'      => 'Vietcombank',
            'bank_id'        => $bin,
        ]
    ];
});

// Dam bao cac truong VietQR tren trang thankyou luon day du va khong bi rong
add_filter('woocommerce_vietqr_account_fields', function ($fields, $order_id) {
    $bin = nhom25_env('VIETQR_BANK_BIN') ?: '970436';
    $acc_num = nhom25_env('VIETQR_ACCOUNT_NUMBER');
    $acc_name = nhom25_env('VIETQR_ACCOUNT_NAME');

    if (isset($fields['bank_id'])) $fields['bank_id']['value'] = $bin;
    if (isset($fields['bank_name'])) $fields['bank_name']['value'] = 'Vietcombank';
    if (isset($fields['account_number'])) $fields['account_number']['value'] = $acc_num;
    if (isset($fields['account_name'])) $fields['account_name']['value'] = $acc_name;

    return $fields;
}, 10, 2);

