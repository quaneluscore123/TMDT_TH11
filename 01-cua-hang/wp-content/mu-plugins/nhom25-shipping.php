<?php
/**
 * Plugin Name: Nhom25 - Phi van chuyen theo khoi luong
 * Description: Phuong thuc giao hang co 2 muc phi theo tong khoi luong don (<= 1 kg / > 1 kg). Cau hinh vung bang setup-shipping.php.
 */

if (!defined('ABSPATH')) exit;

const NHOM25_FREE_SHIP_THRESHOLD = 320000;

add_action('woocommerce_shipping_init', function () {
    class Nhom25_Weight_Shipping extends WC_Shipping_Method {
        public function __construct($instance_id = 0) {
            $this->id = 'nhom25_weight';
            $this->instance_id = absint($instance_id);
            $this->method_title = 'Giao hàng theo khối lượng';
            $this->method_description = 'Một mức phí cho đơn đến 1 kg, một mức phí cho đơn trên 1 kg.';
            $this->supports = ['shipping-zones', 'instance-settings', 'instance-settings-modal'];
            $this->instance_form_fields = [
                'title'      => ['title' => 'Tên hiển thị', 'type' => 'text', 'default' => 'Giao hàng tiêu chuẩn'],
                'cost_light' => ['title' => 'Phí đơn đến 1 kg (đ)', 'type' => 'number', 'default' => '15000'],
                'cost_heavy' => ['title' => 'Phí đơn trên 1 kg (đ)', 'type' => 'number', 'default' => '20000'],
                'eta'        => ['title' => 'Thời gian giao dự kiến', 'type' => 'text', 'default' => '2–4 ngày'],
            ];
            $this->init_instance_settings();
            $this->title = $this->get_option('title');
            add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
        }

        public function cost_for_grams($grams) {
            return (float) $this->get_option($grams <= 1000 ? 'cost_light' : 'cost_heavy');
        }

        public function calculate_shipping($package = []) {
            $grams = 0;
            foreach ($package['contents'] as $item) {
                $grams += (float) wc_get_weight((float) $item['data']->get_weight(), 'g') * $item['quantity'];
            }
            $this->add_rate([
                'id'    => $this->get_rate_id(),
                'label' => $this->title . ' (' . $this->get_option('eta') . ')',
                'cost'  => $this->cost_for_grams($grams),
            ]);
        }
    }
});

add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['nhom25_weight'] = 'Nhom25_Weight_Shipping';
    return $methods;
});

// Du dieu kien mien phi thi chi hien lua chon mien phi
add_filter('woocommerce_package_rates', function ($rates) {
    $free = array_filter($rates, function ($r) { return $r->get_method_id() === 'free_shipping'; });
    return $free ?: $rates;
}, 100);

/** Bang phi theo vung cho trang san pham: [[vung, phi, thoi gian], ...] */
function nhom25_shipping_table($grams) {
    $rows = [];
    if (!class_exists('WC_Shipping_Zones')) return $rows;
    foreach (WC_Shipping_Zones::get_zones() as $zone) {
        foreach ($zone['shipping_methods'] as $m) {
            if ($m->id === 'nhom25_weight' && $m->is_enabled()) {
                $rows[] = [$zone['zone_name'], $m->cost_for_grams($grams), $m->get_option('eta')];
            }
        }
    }
    return $rows;
}
