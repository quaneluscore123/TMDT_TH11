<?php
global $wpdb;
$attrs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies");
foreach ($attrs as $a) {
    echo $a->attribute_id . ': ' . $a->attribute_name . ' (' . $a->attribute_label . ")\n";
}
echo "---\n";
$terms = get_terms(['taxonomy' => 'pa_kiem-dinh-an-toan', 'hide_empty' => false]);
foreach ($terms as $t) { echo 'pa_kiem-dinh-an-toan: ' . $t->name . "\n"; }
$terms2 = get_terms(['taxonomy' => 'pa_khoi-lop', 'hide_empty' => false]);
foreach ($terms2 as $t) { echo 'pa_khoi-lop: ' . $t->name . "\n"; }
$terms3 = get_terms(['taxonomy' => 'pa_loai-san-pham', 'hide_empty' => false]);
foreach ($terms3 as $t) { echo 'pa_loai-san-pham: ' . $t->name . "\n"; }
