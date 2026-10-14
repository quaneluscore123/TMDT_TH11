<?php
/**
 * Giao dien: ten menu va tieu de trang bang tieng Viet, sua ten thuoc tinh "Co" -> "Có".
 * Chay: docker compose run --rm wpcli wp eval-file /scripts/setup-giao-dien.php
 * Chay lai nhieu lan khong sao (chi dat lai gia tri).
 */

// Tieu de trang WooCommerce (slug giu nguyen: /shop/, /cart/, /checkout/)
$tieu_de = [
    'shop' => 'Cửa hàng',
    'cart' => 'Giỏ hàng',
    'checkout' => 'Thanh toán',
    'myaccount' => 'Tài khoản',
];
foreach ($tieu_de as $trang => $ten) {
    $id = wc_get_page_id($trang);
    if ($id > 0) {
        wp_update_post(['ID' => $id, 'post_title' => $ten]);
        echo "Trang $trang (#$id) -> $ten\n";
    }
}

// Menu chinh: muc tro toi trang lay theo tieu de trang moi; them "Tra cuu don"
$menu = wp_get_nav_menu_object('Primary');
if ($menu) {
    foreach (wp_get_nav_menu_items($menu->term_id) as $muc) {
        if ($muc->object === 'page' && ($ten = get_the_title($muc->object_id))) {
            wp_update_nav_menu_item($menu->term_id, $muc->ID, [
                'menu-item-title' => $ten,
                'menu-item-object' => 'page',
                'menu-item-object-id' => $muc->object_id,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
                'menu-item-position' => $muc->menu_order,
            ]);
            echo "Menu: $ten\n";
        }
    }
    $tra_cuu = get_page_by_path('tra-cuu-don-hang');
    $da_co = array_filter(wp_get_nav_menu_items($menu->term_id), function ($m) use ($tra_cuu) {
        return $tra_cuu && (int) $m->object_id === $tra_cuu->ID;
    });
    if ($tra_cuu && !$da_co) {
        wp_update_nav_menu_item($menu->term_id, 0, [
            'menu-item-title' => 'Tra cứu đơn',
            'menu-item-object' => 'page',
            'menu-item-object-id' => $tra_cuu->ID,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ]);
        echo "Menu: them Tra cứu đơn\n";
    }
}

// Ten gia tri thuoc tinh hien o bo loc
$co = get_term_by('slug', 'co', 'pa_kiem-dinh-an-toan');
if ($co && $co->name !== 'Có') {
    wp_update_term($co->term_id, 'pa_kiem-dinh-an-toan', ['name' => 'Có']);
    echo "Thuoc tinh kiem dinh: Co -> Có\n";
}
