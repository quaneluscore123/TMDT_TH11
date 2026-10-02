<?php
// Enable COD, VNPay, VietQR payment gateways

update_option('woocommerce_cod_settings', [
    'enabled' => 'yes',
    'title' => 'Thanh toán khi nhận hàng (COD)',
    'description' => 'Thanh toán bằng tiền mặt khi nhận hàng',
    'instructions' => 'Thanh toán bằng tiền mặt khi nhận hàng',
    'enable_for_methods' => [],
    'enable_for_virtual' => 'yes',
]);

update_option('woocommerce_woo_vnpay_settings', [
    'enabled' => 'yes',
    'title' => 'VNPAY',
    'description' => 'Thực hiện thanh toán qua VNPAY.',
    'order_desc' => 'DH{{orderid}}',
    'button_label' => 'Thanh toán qua VNPAY',
    'order_created' => 'pending',
    'payment_success' => 'processing',
    'payment_failed' => 'cancelled',
    'vnp_Url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
    'vnp_TmnCode' => 'B06ZF5NH',
    'vnp_HashSecret' => 'NUDQHRVALUKOTQFDWYXUSVIUKKBCRYQT',
]);

update_option('woocommerce_vietqr_settings', [
    'enabled' => 'yes',
    'title' => 'VietQR',
    'description' => 'Quét mã VietQR để thanh toán',
    'bank_name' => 'Vietcombank',
    'bank_bin' => '970422',
    'account_number' => '',
    'account_name' => '',
]);

echo 'COD: ' . (get_option('woocommerce_cod_settings')['enabled'] ?? 'MISSING') . "\n";
echo 'VNPay: ' . (get_option('woocommerce_woo_vnpay_settings')['enabled'] ?? 'MISSING') . "\n";
echo 'VietQR: ' . (get_option('woocommerce_vietqr_settings')['enabled'] ?? 'MISSING') . "\n";
echo "=== Done ===\n";
