<?php
/*
 * Plugin Name: Nhom25 - Cong khai thong tin nguoi ban
 * Description: Hien thong tin dinh danh nguoi ban o chan moi trang (ly thuyet buoi 8, thuc hanh buoi 8 B1).
 *              Day la cua hang thu nghiem cua hoc phan, nen cac truong dinh danh ghi ro la du lieu mau.
 *              Khi van hanh that: thay cac gia tri trong nhom25_seller_info() bang thong tin dang ky kinh doanh.
 */

if (!defined('ABSPATH')) {
    exit;
}

function nhom25_seller_info()
{
    return [
        'ten'     => 'Hộ kinh doanh Bút Chì Xanh',
        'dia_chi' => 'Hà Nội (cửa hàng thử nghiệm, chưa có địa chỉ đăng ký)',
        'mst'     => 'Chưa đăng ký — dữ liệu mẫu của bài thực hành',
        'dkkd'    => 'Chưa đăng ký — dữ liệu mẫu của bài thực hành',
        'dien_thoai' => '0901 234 567 (Zalo)',
        'email'   => 'admin@butchixanh.vn',
        'gio'     => '8:00–21:00 hằng ngày',
    ];
}

function nhom25_seller_html()
{
    $s = nhom25_seller_info();
    $links = [];
    foreach (['dieu-khoan-ban-hang' => 'Điều khoản bán hàng', 'chinh-sach-doi-tra' => 'Chính sách đổi trả', 'chinh-sach-quyen-rieng-tu' => 'Chính sách quyền riêng tư'] as $slug => $label) {
        $p = get_page_by_path($slug);
        if ($p) {
            $links[] = '<a href="' . esc_url(get_permalink($p)) . '">' . esc_html($label) . '</a>';
        }
    }
    return '<div class="nhom25-seller" style="font-size:13px;line-height:1.7;text-align:left">'
        . '<strong>' . esc_html($s['ten']) . '</strong><br>'
        . 'Địa chỉ: ' . esc_html($s['dia_chi']) . '<br>'
        . 'Mã số thuế: ' . esc_html($s['mst']) . ' · Giấy ĐKKD: ' . esc_html($s['dkkd']) . '<br>'
        . 'Điện thoại: ' . esc_html($s['dien_thoai']) . ' · Email: ' . esc_html($s['email']) . ' · ' . esc_html($s['gio']) . '<br>'
        . implode(' · ', $links) . '<br>'
        . '<em>Đây là cửa hàng thử nghiệm của bài thực hành học phần Thương mại điện tử (Nhóm 25), không bán hàng và không nhận tiền thật.</em>'
        . '</div>';
}

// Chan trang Storefront: khoi thong tin nguoi ban truoc dong ban quyen (Storefront escape HTML trong dong ban quyen)
add_action('storefront_footer', function () {
    echo '<div class="col-full" style="padding:12px 0;border-top:1px solid rgba(0,0,0,.08)">' . nhom25_seller_html() . '</div>';
}, 15);
add_filter('storefront_copyright_text', function () {
    return '© Bút Chì Xanh ' . date('Y');
});
add_filter('storefront_credit_link', '__return_false');

// Trang thanh toan: nhac lai ten nguoi ban va lien he truoc nut Dat hang
add_action('woocommerce_review_order_before_submit', function () {
    $s = nhom25_seller_info();
    echo '<p class="nhom25-seller-checkout" style="font-size:13px">Người bán: <strong>' . esc_html($s['ten']) . '</strong> · ' . esc_html($s['dien_thoai']) . ' · ' . esc_html($s['email']) . '</p>';
});
