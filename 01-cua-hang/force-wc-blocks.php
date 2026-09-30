<?php
/**
 * Fix: Force-register WooCommerce block editor scripts early.
 */
add_action('wp_loaded', function () {
    $registry = WP_Block_Type_Registry::get_instance();
    $all_blocks = $registry->get_all_registered();
    $blocks_dir = WP_PLUGIN_DIR . '/woocommerce/assets/client/blocks/';
    $woo_base_url = plugins_url('/', WP_PLUGIN_DIR . '/woocommerce/woocommerce.php');

    foreach ($all_blocks as $block) {
        if (strpos($block->name, 'woocommerce/') !== 0) {
            continue;
        }
        if ($block->editor_script && !wp_script_is($block->editor_script, 'registered')) {
            $block_name = str_replace('woocommerce/', '', $block->name);
            $js_file = $blocks_dir . $block_name . '.js';
            $asset_file = $blocks_dir . $block_name . '.asset.php';
            if (file_exists($js_file)) {
                $deps = array('wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-data', 'wp-editor');
                $version = '1.0.0';
                if (file_exists($asset_file)) {
                    $asset_data = require $asset_file;
                    if (isset($asset_data['dependencies'])) { $deps = $asset_data['dependencies']; }
                    if (isset($asset_data['version'])) { $version = $asset_data['version']; }
                }
                wp_register_script($block->editor_script, $woo_base_url . 'assets/client/blocks/' . $block_name . '.js', $deps, $version, true);
            }
        }
    }
}, 5);
