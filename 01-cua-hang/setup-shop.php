<?php
// Cấu hình cửa hàng Bút Chì Xanh sau khi import sản phẩm
// Run: wp --allow-root eval-file setup-shop.php

$t = "B\u{00FA}t Ch\u{00EC} Xanh"; // Bút Chĩ Xanh

// ---------- 1. Nhận diện site ----------
update_option('blogname', $t);
update_option('blogdescription', 'Cửa hàng đồ dùng học tập');

// ---------- 2. Cài đặt WooCommerce ----------
update_option('woocommerce_currency', 'VND');
update_option('woocommerce_currency_pos', 'left');
update_option('woocommerce_price_thousand_sep', '.'); // TH11: dau cham phan cach hang nghin
update_option('woocommerce_price_decimal_sep', ',');
update_option('woocommerce_price_num_decimals', 0); // VND khong co phan le
update_option('woocommerce_enable_guest_checkout', 'yes');
update_option('woocommerce_enable_checkout_login_reminder', 'no');
update_option('woocommerce_cart_redirect_after_add', 'no');
update_option('woocommerce_enable_ajax_add_to_cart', 'yes');

// Tạo trang WC nếu chưa có
if (class_exists('WC_Install') && !get_page_by_path('cart')) {
    WC_Install::create_pages();
}

$pages = [];
foreach (['shop', 'cart', 'checkout', 'my-account'] as $slug) {
    $p = get_page_by_path($slug);
    if ($p) $pages[$slug] = $p->ID;
}
echo 'Pages: ' . json_encode($pages) . "\n";

// Đổi tên trang tài khoản
if (!empty($pages['my-account'])) {
    wp_update_post(['ID' => $pages['my-account'], 'post_title' => 'Tài khoản']);
}

// ---------- 3. Trang "Tra cứu đơn hàng" ----------
$track = get_page_by_path('tra-cuu-don-hang');
if (!$track) {
    $track_id = wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => 'Tra cứu đơn hàng',
        'post_name' => 'tra-cuu-don-hang',
        'post_content' => '[woocommerce_order_tracking]',
    ]);
} else {
    $track_id = $track->ID;
}
echo "Order tracking page: $track_id\n";

// ---------- 4. Trang chủ + hero ----------
$home = get_page_by_path('trang-chu');
$hero = ''; // noi dung trang chu (cau dinh vi) dat boi setup-trang-chu.php
if (!$home) {
    $home_id = wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => 'Trang chủ',
        'post_name' => 'trang-chu',
        'post_content' => $hero,
    ]);
} else {
    $home_id = $home->ID;
}
update_option('show_on_front', 'page');
update_option('page_on_front', $home_id);
echo "Homepage: $home_id\n";

// ---------- 5. Menu chính ----------
$menu_name = 'Primary';
$menu = wp_get_nav_menu_object($menu_name);
if (!$menu) $menu = wp_create_nav_menu($menu_name);
$menu_id = (int) $menu->term_id;

$menu_items = wp_get_nav_menu_items($menu_id);
if (!$menu_items || count($menu_items) < 5) {
    if ($menu_items) foreach ($menu_items as $mi) wp_delete_post($mi->ID, true);

    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => 'Trang chủ',
        'menu-item-url' => home_url('/'),
        'menu-item-type' => 'custom',
        'menu-item-status' => 'publish',
        'menu-item-position' => 1,
    ]);
    $order = ['Shop' => 'shop', 'Cart' => 'cart', 'Checkout' => 'checkout', 'Tài khoản' => 'my-account'];
    $pos = 2;
    foreach ($order as $label => $slug) {
        if (empty($pages[$slug])) continue;
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $pages[$slug],
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
            'menu-item-position' => $pos++,
        ]);
    }
}
$locations = get_theme_mod('nav_menu_locations', []);
$locations['primary'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);
echo "Menu: $menu_id\n";

// ---------- 6. Footer widgets (4 cột) ----------
$footer_texts = [
    ['Về chúng tôi', $t . ' bán đồ dùng học tập cho học sinh tiểu học, ghi rõ kiểm định an toàn và khối lớp cho từng sản phẩm.'],
    ['Liên kết nhanh',
        '<a href="' . esc_url(get_permalink($pages['shop'])) . '">Cửa hàng</a><br>'
        . '<a href="' . esc_url(get_permalink($pages['cart'])) . '">Giỏ hàng</a><br>'
        . '<a href="' . esc_url(get_permalink($pages['checkout'])) . '">Thanh toán</a>'],
    ['Chăm sóc khách hàng', 'Điện thoại/Zalo: 0901 234 567<br>Email: admin@butchixanh.vn<br>Thời gian: 8:00–21:00 hằng ngày'],
];

global $wp_registered_sidebars;
$footer_sidebars = [];
foreach (array_keys((array) $wp_registered_sidebars) as $sb) {
    if (strpos($sb, 'footer-') === 0) $footer_sidebars[] = $sb;
}
sort($footer_sidebars);
echo 'Footer sidebars: ' . json_encode($footer_sidebars) . "\n";

$sidebars = get_option('sidebars_widgets');
if (!is_array($sidebars)) $sidebars = [];
$widget = get_option('widget_custom_html');
if (!is_array($widget)) $widget = [];
$next_id = 200;
foreach ($footer_texts as $i => $ft) {
    if (!isset($footer_sidebars[$i])) break;
    $sb = $footer_sidebars[$i];
    if (in_array("custom_html-$next_id", (array) ($sidebars[$sb] ?? []))) { $next_id++; continue; }
    $widget[$next_id] = ['title' => $ft[0], 'content' => $ft[1]];
    $sidebars[$sb][] = "custom_html-$next_id";
    $next_id++;
}
$widget['_multiwidget'] = 1;
update_option('widget_custom_html', $widget);
update_option('sidebars_widgets', $sidebars);

// ---------- 7. Xóa cache WC ----------
if (function_exists('wc_delete_product_transients')) wc_delete_product_transients();
wp_cache_flush();

echo "=== Setup done ===\n";
