<?php
// Import products from woocommerce-import.csv into WooCommerce
// Run: wp --allow-root eval-file import-csv.php

$csv = fopen('/var/www/html/woocommerce-import.csv', 'r');
if (!$csv) { fwrite(STDERR, "Cannot open CSV\n"); exit(1); }

$header = fgetcsv($csv);
$map = array_flip($header);

$created = 0;
$errors = [];

while (($row = fgetcsv($csv)) !== false) {
    $data = array_combine($header, $row);

    $product = new WC_Product_Simple();
    $product->set_sku($data['SKU']);
    $product->set_name($data['Name']);
    $product->set_short_description($data['Short description']);
    $product->set_description($data['Description']);
    $product->set_regular_price($data['Regular price']);
    $product->set_stock_quantity((int)$data['Stock']);
    $product->set_stock_status('instock');
    $product->set_weight($data['Weight (kg)']);
    $product->set_length($data['Length (cm)']);
    $product->set_width($data['Width (cm)']);
    $product->set_height($data['Height (cm)']);
    $product->set_catalog_visibility('visible');

    // Categories
    $cats = array_map('trim', explode('>', $data['Categories']));
    $cat_ids = [];
    $parent_id = 0;
    foreach ($cats as $cat_name) {
        $term = get_term_by('name', $cat_name, 'product_cat');
        if (!$term) {
            $result = wp_insert_term($cat_name, 'product_cat', ['parent' => $parent_id]);
            if (is_wp_error($result)) { $errors[] = "Cat '$cat_name': " . $result->get_error_message(); continue; }
            $term_id = $result['term_id'];
        } else {
            $term_id = $term->term_id;
        }
        $cat_ids[] = $term_id;
        $parent_id = $term_id;
    }
    if ($cat_ids) $product->set_category_ids($cat_ids);

    // Attributes
    $attrs = [];
    for ($i = 1; $i <= 3; $i++) {
        $name = $data["Attribute $i name"] ?? '';
        $value = $data["Attribute $i value(s)"] ?? '';
        $visible = $data["Attribute $i visible"] ?? '1';
        $global = $data["Attribute $i global"] ?? '1';
        if (!$name || !$value) continue;

        $attr = new WC_Product_Attribute();
        $attr->set_name($name);
        $attr->set_options(array_map('trim', explode('|', $value)));
        $attr->set_visible($visible === '1');
        $attr->set_variation(false);
        $attrs[] = $attr;

        // Also register as taxonomy for global attributes
        if ($global === '1') {
            $tax_name = wc_attribute_taxonomy_name($name);
            if (!taxonomy_exists($tax_name)) {
                wc_create_attribute(['name' => $name, 'slug' => sanitize_title($name), 'type' => 'select']);
            }
            $attr->set_name($tax_name);
        }
    }
    if ($attrs) $product->set_attributes($attrs);

    // Meta: gia_von
    $product->update_meta_data('gia_von', $data['Meta: gia_von']);

    // Image
    $img_path = $data['Images'];
    if ($img_path && file_exists(ABSPATH . 'wp-content/uploads/products/' . basename($img_path))) {
        $upload_dir = wp_upload_dir();
        $img_file = $upload_dir['basedir'] . '/products/' . basename($img_path);
        $filetype = wp_check_filetype(basename($img_file), null);
        $attachment = [
            'post_mime_type' => $filetype['type'],
            'post_title'     => sanitize_file_name(basename($img_file)),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];
        $attach_id = wp_insert_attachment($attachment, $img_file);
        if (!is_wp_error($attach_id)) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attach_data = wp_generate_attachment_metadata($attach_id, $img_file);
            wp_update_attachment_metadata($attach_id, $attach_data);
            $product->set_image_id($attach_id);
        }
    }

    $id = $product->save();
    if ($id) { $created++; echo "Created: {$data['SKU']} (ID $id)\n"; }
    else { $errors[] = "Failed: {$data['SKU']}"; }
}

fclose($csv);
echo "\n=== Done: $created products created ===\n";
if ($errors) { echo "Errors:\n - " . implode("\n - ", $errors) . "\n"; }
