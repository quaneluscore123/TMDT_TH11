<?php
if (!defined('ABSPATH')) {
    require_once '/var/www/html/wp-load.php';
}
// TH12 - NV1a: van chuyen 5 vung theo khoang cach tu kho Ha Noi, phi theo khoi luong (<= 1 kg / > 1 kg),
// mien phi tu NHOM25_FREE_SHIP_THRESHOLD (499.000d). Can cu chon nguong: 06-buoi-13/nguong-mien-phi-van-chuyen.md
// Chay (trong 01-cua-hang): docker compose run --rm wpcli wp eval-file /scripts/setup-shipping.php
// Muon nap lai ton kho tu products.csv: chep file vao /tmp/products.csv cua container truoc khi chay.

// ---------- 1. Cua hang o Ha Noi, chi giao trong Viet Nam ----------
update_option('woocommerce_default_country', 'VN:HN');
update_option('woocommerce_store_city', 'Hà Nội');
update_option('woocommerce_allowed_countries', 'specific');
update_option('woocommerce_specific_allowed_countries', ['VN']);
update_option('woocommerce_ship_to_countries', '');
update_option('woocommerce_default_customer_address', 'base');
update_option('woocommerce_weight_unit', 'kg');           // trong luong SP dang luu theo kg (0.767 = 767 g)
update_option('woocommerce_price_num_decimals', 0);       // VND khong co phan le
update_option('woocommerce_enable_shipping_calc', 'yes');
update_option('woocommerce_shipping_cost_requires_address', 'no');
echo "1. Cua hang VN:HN, chi giao trong nuoc, don vi kg\n";

// ---------- 2. Ton kho tu 02-dataset/products.csv (de kiem thu tru kho / het hang khi dang tra) ----------
update_option('woocommerce_manage_stock', 'yes');
$fh = file_exists('/tmp/products.csv') ? fopen('/tmp/products.csv', 'r') : false;
if (!$fh) {
    echo "2. Bo qua nap ton kho (khong co /tmp/products.csv) - giu nguyen ton kho hien tai\n";
} else {
$head = fgetcsv($fh);
$head[0] = preg_replace('/^\xEF\xBB\xBF/', '', $head[0]);
$n = 0;
while (($row = fgetcsv($fh)) !== false) {
    $r = array_combine($head, $row);
    $id = wc_get_product_id_by_sku($r['sku']);
    if (!$id) continue;
    $p = wc_get_product($id);
    $p->set_manage_stock(true);
    $p->set_stock_quantity((int) $r['ton_kho']);
    $p->set_backorders('no');
    $p->save();
    $n++;
}
echo "2. Quan ly ton kho cho $n san pham\n";
}

// ---------- 3. Nam vung van chuyen ----------
foreach (WC_Shipping_Zones::get_zones() as $z) {
    WC_Shipping_Zones::delete_zone($z['id']);
}
$codes = function ($list) { return array_map(function ($c) { return ['VN:' . $c, 'state']; }, $list); };
$zones = [
    // ten, vi tri, phi <= 1kg, phi > 1kg, thoi gian
    ['Nội thành Hà Nội', $codes(['HN']), 18000, 23000, '1–2 ngày'],
    ['Đồng bằng và trung du Bắc Bộ', $codes(['HP', 'QNI', 'BN', 'HY', 'NB', 'PT', 'TN']), 22000, 27000, '2–3 ngày'],
    ['Miền núi phía Bắc', $codes(['TQ', 'LCA', 'LS', 'CB', 'LCH', 'DB', 'SL']), 30000, 36000, '3–5 ngày'],
    ['Miền Trung và Tây Nguyên', $codes(['TH', 'NA', 'HT', 'QT', 'HUE', 'DN', 'QNG', 'GL', 'DL', 'KH', 'LD']), 28000, 34000, '3–4 ngày'],
    ['Miền Nam', [['VN', 'country']], 32000, 38000, '3–5 ngày'],
];
foreach ($zones as $i => [$name, $locations, $light, $heavy, $eta]) {
    $zone = new WC_Shipping_Zone();
    $zone->set_zone_name($name);
    $zone->set_zone_order($i);
    foreach ($locations as [$code, $type]) $zone->add_location($code, $type);
    $zone->save();

    $iid = $zone->add_shipping_method('nhom25_weight');
    update_option("woocommerce_nhom25_weight_{$iid}_settings", [
        'title' => 'Giao hàng tiêu chuẩn', 'cost_light' => (string) $light, 'cost_heavy' => (string) $heavy, 'eta' => $eta,
    ]);
    $fid = $zone->add_shipping_method('free_shipping');
    update_option("woocommerce_free_shipping_{$fid}_settings", [
        'title' => 'Miễn phí vận chuyển (đơn từ ' . number_format(NHOM25_FREE_SHIP_THRESHOLD, 0, ',', '.') . 'đ)', 'requires' => 'min_amount',
        'min_amount' => (string) NHOM25_FREE_SHIP_THRESHOLD, 'ignore_discounts' => 'no',
    ]);
    echo "3. Vung '$name': $light d (<= 1kg) / $heavy d (> 1kg), $eta, mien phi tu " . NHOM25_FREE_SHIP_THRESHOLD . " d\n";
}

WC_Cache_Helper::get_transient_version('shipping', true);
echo "=== Xong setup-shipping ===\n";
