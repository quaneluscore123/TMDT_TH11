<?php
// Kiem tra phi van chuyen theo vung va nguong mien phi (TH12 buoc 12.2).
// Chay (trong 01-cua-hang): docker compose run --rm wpcli wp eval-file /scripts/tests/kiem-tra-phi-ship.php

// Ghep gio hang bang mot san pham nhe (< 1 kg) lap lai toi khi dat gia tri can thu
function nhom25_test_package($state, $target)
{
    $ids = wc_get_products(['limit' => -1, 'return' => 'ids', 'status' => 'publish']);
    $best = null;
    foreach ($ids as $id) {
        $p = wc_get_product($id);
        $g = (float) wc_get_weight((float) $p->get_weight(), 'g');
        $price = (float) $p->get_price();
        if ($price <= 0 || $g <= 0) continue;
        $qty = (int) round($target / $price);
        if ($qty < 1) continue;
        $diff = abs($qty * $price - $target);
        if ($best === null || $diff < $best[3]) $best = [$p, $qty, $qty * $g, $diff];
    }
    [$p, $qty, $grams] = $best;
    $line = $qty * (float) $p->get_price();
    return [[
        'contents' => ['k' => ['data' => $p, 'quantity' => $qty, 'line_total' => $line, 'line_tax' => 0, 'line_subtotal' => $line, 'line_subtotal_tax' => 0, 'product_id' => $p->get_id(), 'variation_id' => 0]],
        'contents_cost' => $line,
        'applied_coupons' => [],
        'user' => ['ID' => 0],
        'destination' => ['country' => 'VN', 'state' => $state, 'postcode' => '', 'city' => '', 'address' => '', 'address_2' => ''],
        'cart_subtotal' => $line,
    ], $line, $grams];
}

WC()->frontend_includes();
WC()->session = new WC_Session_Handler();
WC()->session->init();
WC()->cart = new WC_Cart();
WC()->customer = new WC_Customer(0, true);

printf("%-12s %-10s %-6s %-36s %s\n", 'Tinh', 'Gio hang', 'Kg', 'Vung', 'Phi');
foreach ([['HN', 450000], ['HN', 520000], ['HP', 300000], ['LCA', 300000], ['DN', 300000], ['HCM', 300000], ['HCM', 2000000]] as [$st, $amt]) {
    [$pkg, $line, $grams] = nhom25_test_package($st, $amt);
    WC()->cart->subtotal = $line;
    $zone = WC_Shipping_Zones::get_zone_matching_package($pkg);
    $calc = WC()->shipping()->calculate_shipping_for_package($pkg);
    $rates = array_map(function ($r) { return $r->get_label() . ' = ' . number_format($r->get_cost(), 0, ',', '.') . 'đ'; }, $calc['rates']);
    printf("%-12s %-10s %-6s %-36s %s\n", $st, number_format($line, 0, ',', '.'), round($grams / 1000, 2), $zone->get_zone_name(), implode(' | ', $rates));
}
