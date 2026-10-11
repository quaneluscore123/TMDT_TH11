<?php
/*
 * Plugin Name: Nhom25 - Khoa bi mat tu bien moi truong
 * Description: Khoa VNPay va tai khoan VietQR doc tu bien moi truong (file .env), khong luu trong CSDL hay ma nguon.
 */

if (!defined('ABSPATH')) {
    exit;
}

function nhom25_env($name)
{
    $v = getenv($name);
    return ($v === false) ? '' : trim($v);
}

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
        return $s;
    }
    $s['bank_bin'] = nhom25_env('VIETQR_BANK_BIN') ?: '970436';
    $s['bank_name'] = 'Vietcombank';
    $s['account_number'] = nhom25_env('VIETQR_ACCOUNT_NUMBER');
    $s['account_name'] = nhom25_env('VIETQR_ACCOUNT_NAME');
    return $s;
});
