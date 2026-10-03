<?php
/**
 * Plugin Name: Nhom25 - Checkout Viet Nam
 * Description: 34 tinh/thanh (sau sap nhap 01/7/2025) lam co so tinh phi van chuyen; bo cac truong khong dung (theo bang dang ky du lieu TH12).
 */

if (!defined('ABSPATH')) exit;

/** Ma tinh => [ten, vung giao hang]. Vung dung cho vung van chuyen (shipping zone). */
function nhom25_vn_provinces() {
    return [
        'HN'  => ['Hà Nội', 'ha-noi'],
        // Mien Bac
        'HP'  => ['Hải Phòng', 'mien-bac'],
        'QNI' => ['Quảng Ninh', 'mien-bac'],
        'BN'  => ['Bắc Ninh', 'mien-bac'],
        'HY'  => ['Hưng Yên', 'mien-bac'],
        'NB'  => ['Ninh Bình', 'mien-bac'],
        'PT'  => ['Phú Thọ', 'mien-bac'],
        'TN'  => ['Thái Nguyên', 'mien-bac'],
        'TQ'  => ['Tuyên Quang', 'mien-bac'],
        'LCA' => ['Lào Cai', 'mien-bac'],
        'LS'  => ['Lạng Sơn', 'mien-bac'],
        'CB'  => ['Cao Bằng', 'mien-bac'],
        'LCH' => ['Lai Châu', 'mien-bac'],
        'DB'  => ['Điện Biên', 'mien-bac'],
        'SL'  => ['Sơn La', 'mien-bac'],
        // Mien Trung - Tay Nguyen
        'TH'  => ['Thanh Hóa', 'mien-trung'],
        'NA'  => ['Nghệ An', 'mien-trung'],
        'HT'  => ['Hà Tĩnh', 'mien-trung'],
        'QT'  => ['Quảng Trị', 'mien-trung'],
        'HUE' => ['Huế', 'mien-trung'],
        'DN'  => ['Đà Nẵng', 'mien-trung'],
        'QNG' => ['Quảng Ngãi', 'mien-trung'],
        'GL'  => ['Gia Lai', 'mien-trung'],
        'DL'  => ['Đắk Lắk', 'mien-trung'],
        'KH'  => ['Khánh Hòa', 'mien-trung'],
        'LD'  => ['Lâm Đồng', 'mien-trung'],
        // Mien Nam
        'HCM' => ['TP. Hồ Chí Minh', 'mien-nam'],
        'DNA' => ['Đồng Nai', 'mien-nam'],
        'TNI' => ['Tây Ninh', 'mien-nam'],
        'CT'  => ['Cần Thơ', 'mien-nam'],
        'VL'  => ['Vĩnh Long', 'mien-nam'],
        'DT'  => ['Đồng Tháp', 'mien-nam'],
        'AG'  => ['An Giang', 'mien-nam'],
        'CM'  => ['Cà Mau', 'mien-nam'],
    ];
}

add_filter('woocommerce_states', function ($states) {
    $states['VN'] = array_map(function ($p) { return $p[0]; }, nhom25_vn_provinces());
    return $states;
});

// Tinh/thanh la bat buoc (quyet dinh phi ship); khong dung ma buu chinh.
add_filter('woocommerce_get_country_locale', function ($locale) {
    $locale['VN']['state'] = ['label' => 'Tỉnh / Thành phố', 'required' => true, 'hidden' => false, 'priority' => 45];
    $locale['VN']['city'] = ['label' => 'Phường / Xã', 'required' => true];
    $locale['VN']['postcode'] = ['required' => false, 'hidden' => true];
    return $locale;
});

// Bo cac truong khong ai dung (bang dang ky du lieu: "Truong nao khong ai biet dung de lam gi thi phai bo")
add_filter('woocommerce_checkout_fields', function ($fields) {
    foreach (['billing', 'shipping'] as $g) {
        unset($fields[$g][$g . '_company'], $fields[$g][$g . '_address_2'], $fields[$g][$g . '_postcode']);
    }
    if (isset($fields['billing']['billing_address_1'])) {
        $fields['billing']['billing_address_1']['placeholder'] = 'Số nhà, tên đường';
    }
    return $fields;
});

// Ghi chu ve quyen rieng tu ngay tai form dat hang (tiep can duoc truoc khi dat hang)
add_filter('woocommerce_get_privacy_policy_text', function ($text, $type) {
    if ($type === 'checkout') {
        return 'Thông tin bạn cung cấp chỉ dùng để xử lý và giao đơn hàng, liên hệ khi cần và thực hiện nghĩa vụ kế toán – thuế. Xem [privacy_policy] để biết chúng tôi lưu bao lâu và chia sẻ với ai.';
    }
    return $text;
}, 10, 2);

// Bang dang ky du lieu 2.C: khong luu IP va trinh duyet cua nguoi viet danh gia (khong ai dung)
add_filter('pre_comment_user_ip', '__return_empty_string');
add_filter('pre_comment_user_agent', '__return_empty_string');
