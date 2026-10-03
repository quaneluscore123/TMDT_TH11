<?php
// TH12 - NV1: hien du chi phi ngay tai trang san pham (gia + phi van chuyen + phu phi thanh toan)
add_action('woocommerce_single_product_summary', 'custom_product_info', 25);
function custom_product_info() {
    global $product;
    if (!$product) return;

    $grams = (float) wc_get_weight((float) $product->get_weight(), 'g');
    $rows = function_exists('nhom25_shipping_table') ? nhom25_shipping_table($grams) : [];
    $threshold = defined('NHOM25_FREE_SHIP_THRESHOLD') ? NHOM25_FREE_SHIP_THRESHOLD : 0;
    $cart_total = (WC()->cart) ? (float) WC()->cart->get_displayed_subtotal() : 0;
    $policy = get_page_by_path('chinh-sach-doi-tra');
    $terms = get_page_by_path('dieu-khoan-ban-hang');

    echo '<div class="custom-product-info nhom25-cost-box" style="margin:20px 0;padding:15px;background:#f7f7f7;border-left:4px solid #2e7d32;">';
    echo '<p style="margin-bottom:8px"><strong>Tổng chi phí bạn trả = giá sản phẩm + phí vận chuyển.</strong> Giá niêm yết là giá cuối cùng, không cộng thêm thuế hay phí ẩn; thanh toán COD, VietQR, VNPay đều không thu phụ phí.</p>';
    if ($rows) {
        echo '<table style="width:100%;font-size:14px;margin-bottom:8px"><thead><tr><th style="text-align:left">Giao đến</th><th style="text-align:right">Phí vận chuyển*</th><th style="text-align:right">Thời gian</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            echo '<tr><td>' . esc_html($r[0]) . '</td><td style="text-align:right">' . wc_price($r[1]) . '</td><td style="text-align:right">' . esc_html($r[2]) . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '<p style="font-size:13px;margin-bottom:8px">* Ước tính cho đơn chỉ gồm 1 sản phẩm này (' . esc_html(wc_format_decimal($grams / 1000, 2)) . ' kg). Phí chính xác hiển thị ở giỏ hàng trước khi thanh toán.</p>';
    }
    if ($threshold) {
        echo '<p style="margin-bottom:8px"><strong>Miễn phí vận chuyển toàn quốc cho đơn từ ' . wc_price($threshold) . '.</strong>';
        if ($cart_total > 0 && $cart_total < $threshold) {
            echo ' Giỏ hàng của bạn còn thiếu ' . wc_price($threshold - $cart_total) . '.';
        }
        echo '</p>';
    }
    echo '<p style="margin:0"><strong>Đổi trả:</strong> 7 ngày, miễn phí chiều về nếu hàng lỗi hoặc giao sai.';
    if ($policy) echo ' <a href="' . esc_url(get_permalink($policy)) . '">Chính sách đổi trả</a>';
    if ($terms) echo ' · <a href="' . esc_url(get_permalink($terms)) . '">Điều khoản bán hàng</a>';
    echo '</p></div>';
}
