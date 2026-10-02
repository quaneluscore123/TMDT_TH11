<?php
$csv = fopen('/var/www/html/woocommerce-import.csv', 'r');
$header = fgetcsv($csv);

$updated = 0;
while (($row = fgetcsv($csv)) !== false) {
    $data = array_combine($header, $row);
    $sku = $data['SKU'];

    $product_id = wc_get_product_id_by_sku($sku);
    if (!$product_id) { echo "Not found: $sku\n"; continue; }

    $product = wc_get_product($product_id);
    $attrs = [];

    $attr_map = [
        'pa_kiem-dinh-an-toan' => $data['Attribute 1 value(s)'],
        'pa_khoi-lop' => $data['Attribute 2 value(s)'],
        'pa_loai-san-pham' => $data['Attribute 3 value(s)'],
    ];

    foreach ($attr_map as $tax_name => $value) {
        $term = get_term_by('name', $value, $tax_name);
        if (!$term) {
            $result = wp_insert_term($value, $tax_name);
            if (is_wp_error($result)) { echo "Term error: " . $result->get_error_message() . "\n"; continue; }
            $term_id = $result['term_id'];
        } else {
            $term_id = $term->term_id;
        }

        $attr = new WC_Product_Attribute();
        $attr->set_id(wc_attribute_taxonomy_id_by_name($tax_name));
        $attr->set_name($tax_name);
        $attr->set_options([$term_id]);
        $attr->set_visible(true);
        $attr->set_variation(false);
        $attrs[] = $attr;
    }

    $product->set_attributes($attrs);
    $product->save();
    $updated++;
    echo "Updated: $sku\n";
}
fclose($csv);
echo "\n=== Done: $updated products updated ===\n";
