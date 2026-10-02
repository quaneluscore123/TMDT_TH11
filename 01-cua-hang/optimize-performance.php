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
    if (function_exists('is_woocommerce') && !is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
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
