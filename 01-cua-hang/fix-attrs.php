<?php
global $wpdb;

// Keep only the first 3 attribute taxonomies (ID 1,2,3), delete duplicates
$wpdb->query("DELETE FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id > 3");

// Re-register the 3 canonical attributes
$attrs = [
    ['name' => 'Kiểm định an toàn', 'slug' => 'kiem-dinh-an-toan', 'type' => 'select'],
    ['name' => 'Khối lớp', 'slug' => 'khoi-lop', 'type' => 'select'],
    ['name' => 'Loại sản phẩm', 'slug' => 'loai-san-pham', 'type' => 'select'],
];

foreach ($attrs as $a) {
    $tax_name = 'pa_' . $a['slug'];
    if (!taxonomy_exists($tax_name)) {
        wc_create_attribute($a);
    }
}

// Verify
$attrs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies");
echo "Attributes after cleanup:\n";
foreach ($attrs as $a) {
    echo $a->attribute_id . ': ' . $a->attribute_name . "\n";
}
