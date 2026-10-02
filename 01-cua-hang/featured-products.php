<?php
// Danh dau 8 san pham featured (toi da 1 SP moi danh muc la) + them section vao trang chu
$leaves = [17, 18, 20, 21, 23, 24, 26, 27, 29, 30];
$count = 0;
foreach ($leaves as $cat_id) {
    if ($count >= 8) break;
    $products = get_posts([
        'post_type' => 'product',
        'posts_per_page' => 1,
        'tax_query' => [[
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => $cat_id,
        ]],
    ]);
    if ($products) {
        update_post_meta($products[0]->ID, '_featured', 'yes');
        echo "Featured: {$products[0]->post_title} ({$products[0]->ID})\n";
        $count++;
    }
}
echo "=== $count products featured ===\n";

$home_id = 92;
$page = get_post($home_id);
$content = $page->post_content;
if (strpos($content, 'Sản phẩm nổi bật') === false) {
    $content .= '<h2 style="text-align:center;font-size:34px;font-weight:700;margin:56px 0 30px">Sản phẩm nổi bật</h2>';
    $content .= do_shortcode('[featured_products limit="8"]');
    wp_update_post(['ID' => $home_id, 'post_content' => $content]);
    echo "Homepage updated with featured section\n";
} else {
    echo "Homepage already has featured section\n";
}
