<?php
// TH11 buoc 11.5 + ly thuyet buoi 8: trang chu co dinh vi gia tri (lay tu phat bieu dinh vi buoi 5),
// bo cac cau quang cao khong chung minh duoc ("Gia tot nhat", "Giao hang 24h"), sua chan trang cho khop chinh sach.
// Chay (trong 01-cua-hang): docker compose run --rm wpcli wp eval-file /scripts/setup-trang-chu.php

$shop = (int) get_option('woocommerce_shop_page_id');
$cat = function ($slug) { $t = get_term_by('slug', $slug, 'pa_kiem-dinh-an-toan'); return $t; };

// Phat bieu dinh vi (buoi 5, slide 18-19): danh cho phu huynh co con tieu hoc lo ngai hang troi noi,
// But Chi Xanh la cua hang do dung hoc tap ghi ro kiem dinh an toan va khoi lop, khac doi thu o thong tin kiem dinh cong khai.
$hero = '<div style="background:linear-gradient(120deg,#1c5f8d 0%,#17b8a6 100%);color:#fff;padding:56px 40px;border-radius:4px;display:flex;flex-wrap:wrap;gap:32px;justify-content:space-between;align-items:flex-start">'
    . '<div style="flex:1 1 340px;max-width:600px">'
    . '<h1 style="color:#fff;font-size:38px;font-weight:800;margin:0 0 20px">Bút Chì Xanh</h1>'
    . '<p style="font-size:22px;line-height:1.55;margin:0 0 16px">Đồ dùng học tập cho học sinh tiểu học, <strong>ghi rõ kết quả kiểm định an toàn</strong> và khối lớp phù hợp cho từng sản phẩm.</p>'
    . '<p style="font-size:17px;line-height:1.6;margin:0 0 28px;opacity:.95">Dành cho phụ huynh muốn chọn đồ an toàn cho con mà không phải đoán: lọc theo khối lớp và theo kiểm định ngay trong cửa hàng.</p>'
    . '<a href="' . esc_url(get_permalink($shop)) . '" style="display:inline-block;background:#fff;color:#1c6fa8;padding:14px 28px;border-radius:24px;text-decoration:none;font-weight:600;font-size:17px">Xem đồ dùng theo khối lớp</a>'
    . '</div>'
    . '<ul style="flex:0 1 320px;list-style:none;margin:0;padding:24px 0 0;font-size:18px;line-height:2">'
    . '<li>✓ Lọc theo khối lớp và kiểm định an toàn</li>'
    . '<li>✓ Giao 1–5 ngày tùy vùng</li>'
    . '<li>✓ Miễn phí vận chuyển từ 499.000đ</li>'
    . '<li>✓ Đổi trả 7 ngày, hàng lỗi cửa hàng chịu phí</li>'
    . '</ul>'
    . '</div>';
$home = (int) get_option('page_on_front');
wp_update_post(['ID' => $home, 'post_content' => $hero]);
echo "1. Trang chu #$home: cau dinh vi moi\n";

// Chan trang: thong tin lien he khop chinh sach; bo muc "Dang ky nhan tin" vi site khong co bieu mau nay
$w = get_option('widget_custom_html', []);
$w[200] = ['title' => 'Về chúng tôi', 'content' => 'Bút Chì Xanh bán đồ dùng học tập cho học sinh tiểu học, ghi rõ kiểm định an toàn và khối lớp cho từng sản phẩm.'];
$w[202] = ['title' => 'Chăm sóc khách hàng', 'content' => 'Điện thoại/Zalo: 0901 234 567<br>Email: admin@butchixanh.vn<br>Thời gian: 8:00–21:00 hằng ngày'];
unset($w[203]);
update_option('widget_custom_html', $w);
$sb = get_option('sidebars_widgets', []);
foreach ($sb as $k => $list) {
    if (is_array($list)) $sb[$k] = array_values(array_diff($list, ['custom_html-203']));
}
update_option('sidebars_widgets', $sb);
echo "2. Chan trang: lien he 0901 234 567 / admin@butchixanh.vn, bo muc dang ky nhan tin\n";
wp_cache_flush();
