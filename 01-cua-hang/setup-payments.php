<?php
// Enable COD, VNPay, VietQR payment gateways

update_option('woocommerce_cod_settings', [
    'enabled' => 'yes',
    'title' => 'Thanh toán khi nhận hàng (COD)',
    'description' => 'Trả tiền mặt cho nhân viên giao hàng khi nhận. Bạn được kiểm tra ngoại quan hàng trước khi trả tiền. Cửa hàng gọi xác nhận đơn trước khi gửi.',
    'instructions' => 'Cửa hàng sẽ gọi số điện thoại bạn đã nhập để xác nhận đơn trong giờ làm việc (8:00–21:00). Vui lòng chuẩn bị đúng số tiền khi nhận hàng.',
    'enable_for_methods' => [],
    'enable_for_virtual' => 'yes',
]);

update_option('woocommerce_woo_vnpay_settings', [
    'enabled' => 'yes',
    'title' => 'VNPAY',
    'description' => 'Thanh toán bằng thẻ ATM nội địa, thẻ quốc tế hoặc ví qua cổng VNPAY. Bạn nhập thông tin thẻ trên trang của VNPAY; cửa hàng không nhận và không lưu số thẻ. Đơn chỉ được xác nhận khi VNPAY báo đã nhận tiền.',
    'order_desc' => 'DH{{orderid}}',
    'button_label' => 'Thanh toán qua VNPAY',
    'order_created' => 'pending',
    'payment_success' => 'processing',
    'payment_failed' => 'cancelled',
    'vnp_Url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
    // vnp_TmnCode va vnp_HashSecret doc tu .env qua mu-plugins/nhom25-secrets.php
    'vnp_TmnCode' => '',
    'vnp_HashSecret' => '',
]);

update_option('woocommerce_vietqr_settings', [
    'enabled' => 'yes',
    'title' => 'VietQR',
    'description' => 'Quét mã VietQR bằng ứng dụng ngân hàng',
    // ngan hang va so tai khoan doc tu .env qua mu-plugins/nhom25-secrets.php
    'bank_name' => 'Vietcombank',
    'bank_bin' => '970436',
    'account_number' => '',
    'account_name' => '',
]);

echo 'COD: ' . (get_option('woocommerce_cod_settings')['enabled'] ?? 'MISSING') . "\n";
echo 'VNPay: ' . (get_option('woocommerce_woo_vnpay_settings')['enabled'] ?? 'MISSING') . "\n";
echo 'VietQR: ' . (get_option('woocommerce_vietqr_settings')['enabled'] ?? 'MISSING') . "\n";
echo "=== Done ===\n";
