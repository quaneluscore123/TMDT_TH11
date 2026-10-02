<?php
// Create a test order for screenshot purposes
$product_id = wc_get_product_id_by_sku('BUT-GEL-001');
if (!$product_id) { echo "Product not found\n"; exit(1); }

$order = wc_create_order();
$order->add_product(wc_get_product($product_id), 2);
$order->set_address([
    'first_name' => 'Nguyen Van',
    'last_name'  => 'Test',
    'email'      => 'test@example.com',
    'phone'      => '0901234567',
    'address_1'  => '123 Test St',
    'city'       => 'Ha Noi',
    'country'    => 'VN',
], 'billing');
$order->set_address([
    'first_name' => 'Nguyen Van',
    'last_name'  => 'Test',
    'email'      => 'test@example.com',
    'phone'      => '0901234567',
    'address_1'  => '123 Test St',
    'city'       => 'Ha Noi',
    'country'    => 'VN',
], 'shipping');
$order->calculate_totals();
$order->update_status('completed', 'Test order for screenshot');
echo "Order created: #" . $order->get_id() . "\n";
echo "Order key: " . $order->get_order_key() . "\n";
