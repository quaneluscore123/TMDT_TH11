<?php
/*
 * Plugin Name: Performance Optimizer
 * Description: Tối ưu hiệu suất cho Lighthouse Performance score
 * Version: 1.0.0
 * Author: Nhom01
 */

// 1. Xóa jQuery Migrate (không cần thiết, giảm tải)
function remove_jquery_migrate($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $script = $scripts->registered['jquery'];
        if ($script->deps) {
            $script->deps = array_diff($script->deps, array('jquery-migrate'));
        }
    }
}
add_action('wp_default_scripts', 'remove_jquery_migrate');

// 2. Tắt WooCommerce Cart Fragments AJAX (giảm request)
function disable_woocommerce_cart_fragments() {
    if (is_front_page() && !is_checkout()) {
        wp_dequeue_script('wc-cart-fragments');
        wp_deregister_script('wc-cart-fragments');
    }
}
add_action('wp_enqueue_scripts', 'disable_woocommerce_cart_fragments', 99);

// 3. Tắt WooCommerce scripts/styles trên trang không phải shop
function disable_woocommerce_assets_on_non_shop() {
    // Trang chu co luoi san pham (nhom25-giao-dien.php) nen van can CSS cua WooCommerce
    if (function_exists('is_woocommerce') && !is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page() && !is_front_page()) {
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
        wp_dequeue_script('wc-add-to-cart');
    }
}
add_action('wp_enqueue_scripts', 'disable_woocommerce_assets_on_non_shop', 99);

// 4. Xóa query strings trên static resources
function remove_query_strings($src) {
    if (strpos($src, '?ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('style_loader_src', 'remove_query_strings', 10, 2);
add_filter('script_loader_src', 'remove_query_strings', 10, 2);

// 5. Tắt Emojis
function disable_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'disable_emojis');

// 6. Lazy loading cho ảnh (WordPress mặc định, đảm bảo bật)
function ensure_lazy_loading($content) {
    return $content;
}

// 7. Tắt Embeds
function disable_embeds() {
    wp_deregister_script('wp-embed');
}
add_action('wp_footer', 'disable_embeds');

// 8. Tối ưu loading fonts từ Google Fonts - dùng system fonts thay thế
function remove_google_fonts() {
    wp_dequeue_style('twentytwentyone-fonts');
    wp_deregister_style('twentytwentyone-fonts');
}
add_action('wp_enqueue_scripts', 'remove_google_fonts', 20);

// 9. Preload critical resources
function add_resource_hints() {
    echo '<link rel="preconnect" href="https://butchixanh.local" crossorigin>' . "\n";
}
add_action('wp_head', 'add_resource_hints', 1);

// 10. Page cache cho khach chua dang nhap (GET, khong phai admin/cart/checkout/account/ajax)
// Giam manh do tre do bind-mount Windows: lan thu 2 tra ve HTML tu file, bo qua WordPress
define('N25_PAGE_CACHE_DIR', WP_CONTENT_DIR . '/cache/n25-pages');
define('N25_PAGE_CACHE_TTL', 300); // 5 phut

function n25_is_cacheable_request() {
    if (function_exists('is_admin') && is_admin()) return false;
    if (function_exists('wp_doing_ajax') && wp_doing_ajax()) return false;
    if (defined('REST_REQUEST') && REST_REQUEST) return false;
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return false;
    // is_user_logged_in chua co khi mu-plugin load -> check cookie truc tiep
    foreach (array_keys($_COOKIE) as $ck) {
        if (strpos($ck, 'wordpress_logged_in_') === 0) return false;
        if (strpos($ck, 'wp-sec') === 0) return false;
    }
    if (defined('WP_CLI') && WP_CLI) return false;
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (preg_match('#/(cart|checkout|my-account|wp-admin|wp-login|admin-ajax)(/|$|\?)#', $uri)) return false;
    if (strpos($uri, 'add-to-cart') !== false) return false;
    if (strpos($uri, 'wc-ajax') !== false) return false;
    if (strpos($uri, 'nonce') !== false) return false;
    return true;
}

function n25_page_cache_key() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return md5($scheme . '|' . $host . '|' . $uri) . '.html';
}

// Doc cache truoc khi WordPress chay (mu-plugin chay som)
function n25_page_cache_serve() {
    if (!n25_is_cacheable_request()) return;
    $file = N25_PAGE_CACHE_DIR . '/' . n25_page_cache_key();
    if (!is_readable($file)) return;
    $age = time() - filemtime($file);
    if ($age > N25_PAGE_CACHE_TTL) { @unlink($file); return; }
    header('X-N25-Cache: HIT (age ' . $age . 's)');
    header('Content-Type: text/html; charset=UTF-8');
    readfile($file);
    exit;
}
// Chay truoc wp-settings, trong mu-plugin
n25_page_cache_serve();

// Ghi cache sau khi render xong
function n25_page_cache_save() {
    if (!n25_is_cacheable_request()) return;
    if (headers_sent()) return;
    // Khong ghi neu response khong phai 200
    $status = http_response_code();
    if ($status !== 200 && $status !== false) return;
    $html = ob_get_contents();
    if (!$html || strlen($html) < 500) return; // qua ngan, khong nen cache
    if (!is_dir(N25_PAGE_CACHE_DIR)) {
        wp_mkdir_p(N25_PAGE_CACHE_DIR);
        @file_put_contents(N25_PAGE_CACHE_DIR . '/index.php', "<?php // Silence is golden.\n");
    }
    @file_put_contents(N25_PAGE_CACHE_DIR . '/' . n25_page_cache_key(), $html);
    header('X-N25-Cache: MISS');
}
add_action('template_redirect', function () {
    ob_start('n25_page_cache_save');
}, 0);

// Xoa toan bo cache khi cap nhat san pham/don hang (don gian)
function n25_page_cache_flush() {
    if (!is_dir(N25_PAGE_CACHE_DIR)) return;
    foreach (glob(N25_PAGE_CACHE_DIR . '/*.html') as $f) @unlink($f);
}
add_action('save_post', 'n25_page_cache_flush');
add_action('woocommerce_order_status_changed', 'n25_page_cache_flush');
add_action('customize_save_after', 'n25_page_cache_flush');
