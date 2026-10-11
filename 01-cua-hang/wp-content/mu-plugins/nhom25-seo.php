<?php
/*
 * Plugin Name: Nhom25 - SEO ky thuat
 * Description: Ly thuyet buoi 5 (slide 22, 24): URL co bo loc/sap xep -> noindex,follow + the canonical ve danh muc goc;
 *              the mo ta (meta description) rieng cho trang chu, danh muc, san pham.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Cac tham so chi tao bien the cua cung mot danh muc, khong co truy van tim kiem rieng */
function nhom25_seo_filter_params()
{
    return ['pa_kiem-dinh-an-toan', 'pa_khoi-lop', 'pa_loai-san-pham', 'price_range', 'orderby', 'min_price', 'max_price', 'filter_khoi-lop', 'filter_kiem-dinh-an-toan', 'filter_loai-san-pham'];
}

function nhom25_seo_is_filtered()
{
    foreach (nhom25_seo_filter_params() as $p) {
        if (isset($_GET[$p]) && $_GET[$p] !== '') {
            return true;
        }
    }
    return false;
}

function nhom25_seo_is_listing()
{
    return function_exists('is_shop') && (is_shop() || is_product_taxonomy());
}

// Bien the do bo loc tao ra: khong lap chi muc nhung van cho di theo lien ket toi san pham
add_filter('wp_robots', function ($robots) {
    if (nhom25_seo_is_listing() && nhom25_seo_is_filtered()) {
        $robots['noindex'] = true;
        $robots['follow'] = true;
        unset($robots['max-image-preview']);
    }
    if (is_search()) {
        $robots['noindex'] = true;
    }
    return $robots;
});

// The canonical cho trang danh sach (WordPress chi tu in canonical cho bai viet va trang don)
add_action('wp_head', function () {
    if (!nhom25_seo_is_listing()) {
        return;
    }
    if (is_shop()) {
        $url = get_permalink(wc_get_page_id('shop'));
    } else {
        $url = get_term_link(get_queried_object());
    }
    $paged = (int) get_query_var('paged');
    if ($paged > 1 && !nhom25_seo_is_filtered()) {
        $url = trailingslashit($url) . 'page/' . $paged . '/';
    }
    if (!is_wp_error($url)) {
        echo '<link rel="canonical" href="' . esc_url($url) . '" />' . "\n";
    }
}, 1);

function nhom25_seo_trim($text, $len = 155)
{
    $text = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags((string) $text)));
    return mb_strlen($text) > $len ? rtrim(mb_substr($text, 0, $len - 1)) . '…' : $text;
}

function nhom25_seo_description()
{
    if (is_front_page()) {
        return 'Bút Chì Xanh: đồ dùng học tập cho học sinh tiểu học, ghi rõ kiểm định an toàn và khối lớp cho từng sản phẩm. Giao 1–5 ngày, miễn phí vận chuyển từ 499.000đ.';
    }
    if (function_exists('is_product') && is_product()) {
        $p = wc_get_product(get_queried_object_id());
        if ($p) {
            $d = $p->get_short_description() ?: $p->get_description();
            $d = wp_strip_all_tags((string) $d);
            return nhom25_seo_trim(mb_stripos($d, $p->get_name()) === 0 ? $d : $p->get_name() . ' – ' . $d);
        }
    }
    if (function_exists('is_product_category') && is_product_category()) {
        $t = get_queried_object();
        $d = term_description($t);
        return nhom25_seo_trim($d ?: ($t->name . ' cho học sinh tiểu học tại Bút Chì Xanh: lọc theo khối lớp và kiểm định an toàn, hiện rõ phí vận chuyển trước khi đặt hàng.'));
    }
    if (function_exists('is_shop') && is_shop()) {
        return 'Tất cả đồ dùng học tập của Bút Chì Xanh: bút, vở, dụng cụ, ba lô, đồ mỹ thuật. Lọc theo khối lớp, kiểm định an toàn và khoảng giá.';
    }
    if (is_page()) {
        return nhom25_seo_trim(get_post_field('post_content', get_queried_object_id()));
    }
    return '';
}

add_action('wp_head', function () {
    $d = nhom25_seo_description();
    if ($d !== '') {
        echo '<meta name="description" content="' . esc_attr($d) . '" />' . "\n";
    }
}, 2);
