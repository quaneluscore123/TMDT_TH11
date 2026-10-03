<?php
// TH12 - NV3: dang 3 van ban (tiep can duoc truoc khi dat hang) + thoi gian luu theo bang dang ky du lieu
// Chay (tu thu muc goc repo):
//   python 05-buoi-12/tools/md2html.py 05-buoi-12/ho-so-tuan-thu/chinh-sach-*.md 05-buoi-12/ho-so-tuan-thu/dieu-khoan-ban-hang.md -o /tmp/hoso
//   docker cp /tmp/hoso 01-cua-hang-shop-1:/tmp/hoso && docker cp 01-cua-hang/setup-ho-so-tuan-thu.php 01-cua-hang-shop-1:/tmp/
//   docker exec 01-cua-hang-shop-1 wp --allow-root --path=/var/www/html eval-file /tmp/setup-ho-so-tuan-thu.php

$dir = '/tmp/hoso';

function nhom25_upsert_page($id, $slug, $title, $file) {
    $data = ['post_title' => $title, 'post_name' => $slug, 'post_status' => 'publish', 'post_type' => 'page',
             'post_content' => file_get_contents($file)];
    if ($id && get_post($id)) {
        $data['ID'] = $id;
    } elseif ($existing = get_page_by_path($slug)) {
        $data['ID'] = $existing->ID;
    }
    return isset($data['ID']) ? wp_update_post($data) : wp_insert_post($data);
}

// ---------- 1. Ba van ban ----------
$privacy = nhom25_upsert_page((int) get_option('wp_page_for_privacy_policy'), 'chinh-sach-quyen-rieng-tu', 'Chính sách quyền riêng tư', "$dir/chinh-sach-quyen-rieng-tu.html");
$refund  = nhom25_upsert_page(9, 'chinh-sach-doi-tra', 'Chính sách đổi trả', "$dir/chinh-sach-doi-tra.html");
$terms   = nhom25_upsert_page((int) get_option('woocommerce_terms_page_id'), 'dieu-khoan-ban-hang', 'Điều khoản bán hàng', "$dir/dieu-khoan-ban-hang.html");
update_option('wp_page_for_privacy_policy', $privacy);
update_option('woocommerce_terms_page_id', $terms); // o "Toi da doc va dong y" tai trang thanh toan
update_option('woocommerce_checkout_terms_and_conditions_checkbox_text', 'Tôi đã đọc và đồng ý với [terms] và Chính sách đổi trả.');
echo "1. Trang: quyen rieng tu #$privacy, doi tra #$refund, dieu khoan #$terms\n";

// Lien ket o chan trang (tiep can duoc tu moi trang)
$menu_name = 'Chân trang - Chính sách';
$menu = wp_get_nav_menu_object($menu_name);
$menu_id = $menu ? $menu->term_id : wp_create_nav_menu($menu_name);
foreach (wp_get_nav_menu_items($menu_id) ?: [] as $it) wp_delete_post($it->ID, true);
foreach ([$terms, $refund, $privacy] as $pid) {
    wp_update_nav_menu_item($menu_id, 0, ['menu-item-object-id' => $pid, 'menu-item-object' => 'page',
        'menu-item-type' => 'post_type', 'menu-item-status' => 'publish']);
}
$sidebars = get_option('sidebars_widgets', []);
$nav_widgets = get_option('widget_nav_menu', []);
$wid = max(array_merge([1], array_filter(array_keys($nav_widgets), 'is_int'))) + 1;
$nav_widgets[$wid] = ['title' => 'Chính sách', 'nav_menu' => $menu_id];
update_option('widget_nav_menu', $nav_widgets);
$sidebars['footer-1'] = array_values(array_filter((array) ($sidebars['footer-1'] ?? []), function ($w) { return strpos($w, 'nav_menu-') !== 0; }));
$sidebars['footer-1'][] = "nav_menu-$wid";
update_option('sidebars_widgets', $sidebars);
echo "2. Menu chan trang '$menu_name'\n";

// ---------- 2. Thoi gian luu (bang dang ky du lieu) ----------
update_option('woocommerce_anonymize_completed_orders', ['number' => '12', 'unit' => 'months']);
update_option('woocommerce_trash_pending_orders', ['number' => '1', 'unit' => 'months']);
update_option('woocommerce_trash_failed_orders', ['number' => '1', 'unit' => 'months']);
update_option('woocommerce_trash_cancelled_orders', ['number' => '1', 'unit' => 'months']);
update_option('woocommerce_erasure_request_removes_order_data', 'yes');
update_option('woocommerce_logs_retention_period_days', 30);
update_option('woocommerce_feature_order_attribution_enabled', 'no'); // khong ai dung + chua co banner xin dong y
update_option('show_avatars', 0);                                     // khong gui ma bam email sang Gravatar
echo "3. Luu tru: an danh don hoan tat 12 thang; xoa don cho/that bai/huy 1 thang; tat Order Attribution, Gravatar\n";
