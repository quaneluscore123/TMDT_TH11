<?php
add_action('woocommerce_before_shop_loop', 'custom_product_filters', 20);
add_action('woocommerce_no_products_found', 'custom_product_filters', 20);

function custom_product_filters() {
    $attrs = [
        'pa_kiem-dinh-an-toan' => 'Kiểm định an toàn',
        'pa_khoi-lop' => 'Khối lớp',
        'pa_loai-san-pham' => 'Loại sản phẩm',
    ];

    $price_ranges = [
        '0-50000' => 'Dưới 50.000đ',
        '50000-150000' => '50.000đ - 150.000đ',
        '150000-300000' => '150.000đ - 300.000đ',
        '300000-999999999' => 'Trên 300.000đ',
    ];

    echo '<style>
        .cpf-wrap{margin:20px 0;padding:20px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:4px;font-family:inherit}
        .cpf-wrap label{font-weight:600;color:#333}
        .cpf-wrap .cpf-group{margin-bottom:15px}
        .cpf-wrap .cpf-label{display:block;font-weight:600;margin-bottom:8px;color:#333;font-size:14px}
        .cpf-wrap .cpf-opts{display:flex;flex-wrap:wrap;gap:8px 20px}
        .cpf-wrap .cpf-opts label{display:inline-flex;align-items:center;gap:6px;font-weight:400;font-size:14px;color:#555;cursor:pointer;margin:0}
        .cpf-wrap input[type="checkbox"]{accent-color:#7f54b3;width:16px;height:16px;cursor:pointer}
        .cpf-wrap .cpf-btn{display:inline-block;padding:10px 24px;background:#7f54b3;color:#fff;border:none;border-radius:3px;font-size:14px;font-weight:600;cursor:pointer;margin-top:5px}
        .cpf-wrap .cpf-btn:hover{background:#6b4599}
        .cpf-wrap .cpf-status{display:inline-block;margin-left:14px;font-size:13px;color:#7f54b3;font-weight:600}
        .cpf-wrap .cpf-reset{display:inline-block;margin-left:10px;font-size:13px;color:#c00;text-decoration:underline}
    </style>';

    echo '<div class="cpf-wrap">';
    echo '<form method="GET" action="">';

    foreach ($attrs as $tax => $label) {
        $terms = get_terms(['taxonomy' => $tax, 'hide_empty' => true]);
        if (empty($terms)) continue;

        $raw = isset($_GET[$tax]) ? $_GET[$tax] : [];
        if (!is_array($raw)) $raw = explode(',', (string) $raw);
        $selected = array_map('sanitize_text_field', $raw);

        echo '<div class="cpf-group">';
        echo '<span class="cpf-label">' . esc_html($label) . '</span>';
        echo '<div class="cpf-opts">';
        foreach ($terms as $term) {
            $checked = in_array($term->slug, $selected) ? 'checked' : '';
            echo '<label><input type="checkbox" name="' . esc_attr($tax) . '[]" value="' . esc_attr($term->slug) . '" ' . $checked . '> ' . esc_html($term->name) . '</label>';
        }
        echo '</div></div>';
    }

    $price_raw = isset($_GET['price_range']) ? $_GET['price_range'] : [];
    if (!is_array($price_raw)) $price_raw = explode(',', (string) $price_raw);
    $price_selected = array_map('sanitize_text_field', $price_raw);
    echo '<div class="cpf-group">';
    echo '<span class="cpf-label">Khoảng giá</span>';
    echo '<div class="cpf-opts">';
    foreach ($price_ranges as $val => $plabel) {
        $checked = in_array($val, $price_selected) ? 'checked' : '';
        echo '<label><input type="checkbox" name="price_range[]" value="' . esc_attr($val) . '" ' . $checked . '> ' . esc_html($plabel) . '</label>';
    }
    echo '</div></div>';

    $active_count = 0;
    foreach ($attrs as $tax => $label) {
        $raw = isset($_GET[$tax]) ? $_GET[$tax] : [];
        if (!is_array($raw)) $raw = explode(',', (string) $raw);
        $active_count += count(array_filter(array_map('sanitize_text_field', $raw)));
    }
    $active_count += count(array_filter($price_selected));

    echo '<button type="submit" class="cpf-btn">Lọc</button>';
    if ($active_count > 0) {
        $reset_url = strtok($_SERVER['REQUEST_URI'], '?');
        echo ' <span class="cpf-status">Đang lọc: ' . (int) $active_count . ' điều kiện</span>';
        echo ' <a class="cpf-reset" href="' . esc_url($reset_url) . '">✕ Xóa bộ lọc</a>';
    }
    echo '</form></div>';
}

add_action('woocommerce_product_query', 'custom_product_query_filter');

function custom_product_query_filter($q) {
    if (!is_shop() && !is_product_taxonomy()) return;

    $tax_query = [];
    $attrs = ['pa_kiem-dinh-an-toan', 'pa_khoi-lop', 'pa_loai-san-pham'];

    foreach ($attrs as $tax) {
        if (!empty($_GET[$tax]) && is_array($_GET[$tax])) {
            $slugs = array_map('sanitize_text_field', $_GET[$tax]);
            $tax_query[] = [
                'taxonomy' => $tax,
                'field' => 'slug',
                'terms' => $slugs,
            ];
        }
    }

    if (!empty($tax_query)) {
        $q->set('tax_query', $tax_query);
    }

    if (!empty($_GET['price_range']) && is_array($_GET['price_range'])) {
        $ranges = array_map('sanitize_text_field', $_GET['price_range']);
        $price_query = ['relation' => 'OR'];
        foreach ($ranges as $range) {
            $parts = explode('-', $range);
            $price_query[] = [
                'key' => '_price',
                'value' => [(float)$parts[0], (float)$parts[1]],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ];
        }
        $q->set('meta_query', array_merge(
            $q->get('meta_query', []),
            [$price_query]
        ));
    }
}
