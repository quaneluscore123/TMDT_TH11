<?php
// TH12 - NV1a: van chuyen 2 vung (Noi thanh Ha Noi / Cac tinh khac), phi 15k-25k theo khoi luong, mien phi tu 320.000d
// Chay: docker cp setup-shipping.php 01-cua-hang-shop-1:/tmp/ && docker cp ../02-dataset/products.csv 01-cua-hang-shop-1:/tmp/
//       docker exec 01-cua-hang-shop-1 wp --allow-root --path=/var/www/html eval-file /tmp/setup-shipping.php

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
$fh = fopen('/tmp/products.csv', 'r');
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

// ---------- 3. Hai vung van chuyen ----------
foreach (WC_Shipping_Zones::get_zones() as $z) {
    WC_Shipping_Zones::delete_zone($z['id']);
}
$zones = [
    // ten, vi tri, phi <= 1kg, phi > 1kg, thoi gian
    ['Nội thành Hà Nội', [['VN:HN', 'state']], 15000, 20000, '1–2 ngày'],
    ['Các tỉnh khác',    [['VN', 'country']],  20000, 25000, '3–5 ngày'],
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
        'title' => 'Miễn phí vận chuyển (đơn từ 320.000đ)', 'requires' => 'min_amount',
        'min_amount' => (string) NHOM25_FREE_SHIP_THRESHOLD, 'ignore_discounts' => 'no',
    ]);
    echo "3. Vung '$name': $light d (<= 1kg) / $heavy d (> 1kg), $eta, mien phi tu " . NHOM25_FREE_SHIP_THRESHOLD . " d\n";
}

WC_Cache_Helper::get_transient_version('shipping', true);
echo "=== Xong setup-shipping ===\n";
