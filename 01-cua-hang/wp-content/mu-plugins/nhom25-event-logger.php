<?php
/*
 * Plugin Name: Nhom25 - Ghi su kien pheu (do luong ben thu nhat)
 * Description: Ghi 5 su kien session, view_item, add_to_cart, begin_checkout, purchase kem nguon UTM vao bang
 *              {prefix}nhom25_events cua chinh cua hang (TH13 buoc 13.1-13.2). Du lieu khong roi khoi may chu,
 *              khong dung cho quang cao. Luot cua quan tri vien khong duoc ghi. Xem: Cong cu > Pheu Nhom 25.
 */

if (!defined('ABSPATH')) {
    exit;
}

const NHOM25_EV_VERSION = '1';
const NHOM25_EV_SID = 'n25_sid';   // ma phien ngau nhien, het han sau 30 phut khong hoat dong
const NHOM25_EV_SRC = 'n25_src';   // nguon cua phien: source|medium|campaign

function nhom25_ev_table()
{
    global $wpdb;
    return $wpdb->prefix . 'nhom25_events';
}

add_action('init', function () {
    if (get_option('nhom25_ev_version') === NHOM25_EV_VERSION) {
        return;
    }
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta('CREATE TABLE ' . nhom25_ev_table() . " (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        ts datetime NOT NULL,
        sid char(32) NOT NULL,
        event varchar(20) NOT NULL,
        src varchar(60) NOT NULL DEFAULT '',
        medium varchar(60) NOT NULL DEFAULT '',
        campaign varchar(100) NOT NULL DEFAULT '',
        product_id bigint unsigned NULL,
        order_id bigint unsigned NULL,
        value decimal(14,0) NULL,
        PRIMARY KEY  (id),
        KEY event_ts (event, ts),
        KEY sid (sid),
        KEY order_id (order_id)
    ) " . $wpdb->get_charset_collate() . ';');
    update_option('nhom25_ev_version', NHOM25_EV_VERSION);
}, 5);

function nhom25_ev_should_track()
{
    if (is_admin() && !wp_doing_ajax()) {
        return false;
    }
    if (current_user_can('manage_woocommerce')) {
        return false; // quan tri vien khong duoc ghi (TH13 buoc 13.1)
    }
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
    return $ua !== '' && !preg_match('/bot|crawl|spider|curl|wget|lighthouse/', $ua);
}

function nhom25_ev_set_cookie($name, $value)
{
    if (headers_sent()) {
        return;
    }
    setcookie($name, $value, ['expires' => time() + 30 * MINUTE_IN_SECONDS, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax']);
    $_COOKIE[$name] = $value;
}

/** Nguon cua luot truy cap: tham so UTM, neu khong co thi trang gioi thieu, neu khong co nua la (direct) */
function nhom25_ev_detect_source()
{
    $g = function ($k) { return isset($_GET[$k]) ? sanitize_text_field(wp_unslash($_GET[$k])) : ''; };
    if ($g('utm_source') !== '') {
        return [$g('utm_source'), $g('utm_medium') ?: '(none)', $g('utm_campaign')];
    }
    $ref = isset($_SERVER['HTTP_REFERER']) ? wp_parse_url(wp_unslash($_SERVER['HTTP_REFERER']), PHP_URL_HOST) : '';
    if ($ref && $ref !== wp_parse_url(home_url(), PHP_URL_HOST)) {
        $medium = preg_match('/google\.|bing\.|coccoc\.|yahoo\./', $ref) ? 'organic' : 'referral';
        return [$ref, $medium, ''];
    }
    return ['(direct)', '(none)', ''];
}

function nhom25_ev_log($event, $product_id = null, $order_id = null, $value = null)
{
    global $wpdb;
    $sid = isset($_COOKIE[NHOM25_EV_SID]) ? preg_replace('/[^a-f0-9]/', '', $_COOKIE[NHOM25_EV_SID]) : '';
    if (strlen($sid) !== 32) {
        return;
    }
    $src = explode('|', isset($_COOKIE[NHOM25_EV_SRC]) ? wp_unslash($_COOKIE[NHOM25_EV_SRC]) : '', 3) + ['', '', ''];
    $wpdb->insert(nhom25_ev_table(), [
        'ts' => current_time('mysql'), 'sid' => $sid, 'event' => $event,
        'src' => substr($src[0], 0, 60), 'medium' => substr($src[1], 0, 60), 'campaign' => substr($src[2], 0, 100),
        'product_id' => $product_id, 'order_id' => $order_id, 'value' => $value,
    ]);
}

// Moi luot tai trang: mo phien moi neu chua co (session), gia han phien 30 phut
add_action('template_redirect', function () {
    if (!nhom25_ev_should_track()) {
        return;
    }
    $sid = isset($_COOKIE[NHOM25_EV_SID]) ? preg_replace('/[^a-f0-9]/', '', $_COOKIE[NHOM25_EV_SID]) : '';
    $has_utm = isset($_GET['utm_source']);
    if (strlen($sid) !== 32 || $has_utm) {
        // phien moi; luot co UTM moi cung mo phien moi de ghi dung nguon
        nhom25_ev_set_cookie(NHOM25_EV_SID, bin2hex(random_bytes(16)));
        nhom25_ev_set_cookie(NHOM25_EV_SRC, implode('|', nhom25_ev_detect_source()));
        nhom25_ev_log('session');
    } else {
        nhom25_ev_set_cookie(NHOM25_EV_SID, $sid);
        nhom25_ev_set_cookie(NHOM25_EV_SRC, isset($_COOKIE[NHOM25_EV_SRC]) ? wp_unslash($_COOKIE[NHOM25_EV_SRC]) : '');
    }
    if (is_product()) {
        nhom25_ev_log('view_item', get_queried_object_id());
    } elseif (function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url('order-received') && WC()->cart && !WC()->cart->is_empty()) {
        nhom25_ev_log('begin_checkout', null, null, WC()->cart->get_total('edit'));
    }
}, 1);

// Them vao gio: ca tu trang chi tiet va tu trang danh muc (AJAX)
add_action('woocommerce_add_to_cart', function ($key, $product_id, $qty) {
    if (nhom25_ev_should_track()) {
        nhom25_ev_log('add_to_cart', $product_id, null, null);
    }
}, 10, 3);

// Don hoan tat: ghi mot lan moi don, tai lai trang cam on khong ghi trung
add_action('woocommerce_thankyou', function ($order_id) {
    global $wpdb;
    if (!$order_id || !nhom25_ev_should_track()) {
        return;
    }
    $exists = $wpdb->get_var($wpdb->prepare('SELECT 1 FROM ' . nhom25_ev_table() . " WHERE event = 'purchase' AND order_id = %d", $order_id));
    if (!$exists) {
        $order = wc_get_order($order_id);
        nhom25_ev_log('purchase', null, $order_id, $order ? $order->get_total() : null);
    }
});

// Thoi gian luu 12 thang theo Bang dang ky du lieu: xoa hang ngay su kien cu hon
add_action('init', function () {
    if (!wp_next_scheduled('nhom25_ev_cleanup')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'nhom25_ev_cleanup');
    }
});
add_action('nhom25_ev_cleanup', function () {
    global $wpdb;
    $wpdb->query($wpdb->prepare('DELETE FROM ' . nhom25_ev_table() . ' WHERE ts < %s', gmdate('Y-m-d H:i:s', strtotime('-12 months', current_time('timestamp')))));
});

// ---------- Trang quan tri: Cong cu > Pheu Nhom 25 ----------
function nhom25_ev_funnel_rows($days = 30)
{
    global $wpdb;
    $t = nhom25_ev_table();
    return $wpdb->get_results($wpdb->prepare(
        "SELECT src, medium,
            COUNT(DISTINCT CASE WHEN event='session' THEN sid END) AS session,
            COUNT(DISTINCT CASE WHEN event='view_item' THEN sid END) AS view_item,
            COUNT(DISTINCT CASE WHEN event='add_to_cart' THEN sid END) AS add_to_cart,
            COUNT(DISTINCT CASE WHEN event='begin_checkout' THEN sid END) AS begin_checkout,
            COUNT(DISTINCT CASE WHEN event='purchase' THEN order_id END) AS purchase,
            COALESCE(SUM(CASE WHEN event='purchase' THEN value END), 0) AS revenue
         FROM $t WHERE ts >= %s GROUP BY src, medium ORDER BY session DESC",
        gmdate('Y-m-d H:i:s', current_time('timestamp') - $days * DAY_IN_SECONDS)
    ), ARRAY_A);
}

add_action('admin_menu', function () {
    add_management_page('Phễu Nhóm 25', 'Phễu Nhóm 25', 'manage_woocommerce', 'nhom25-funnel', function () {
        $rows = nhom25_ev_funnel_rows(30);
        $steps = ['session' => 'Phiên', 'view_item' => 'Xem SP', 'add_to_cart' => 'Thêm giỏ', 'begin_checkout' => 'Bắt đầu TT', 'purchase' => 'Đơn'];
        echo '<div class="wrap"><h1>Phễu theo nguồn (30 ngày)</h1>';
        echo '<p>Đếm số phiên khác nhau có sự kiện; đơn đếm theo mã đơn. Lượt của quản trị viên không được ghi.</p>';
        echo '<table class="widefat striped"><thead><tr><th>Nguồn</th><th>Kênh</th>';
        foreach ($steps as $l) echo '<th>' . esc_html($l) . '</th>';
        echo '<th>Doanh thu</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            echo '<tr><td>' . esc_html($r['src']) . '</td><td>' . esc_html($r['medium']) . '</td>';
            foreach (array_keys($steps) as $k) echo '<td>' . (int) $r[$k] . '</td>';
            echo '<td>' . wc_price($r['revenue']) . '</td></tr>';
        }
        if (!$rows) echo '<tr><td colspan="8">Chưa có dữ liệu. Mở cửa hàng ở cửa sổ ẩn danh với một link có utm_source.</td></tr>';
        echo '</tbody></table>';
        $url = wp_nonce_url(admin_url('admin-post.php?action=nhom25_ev_export'), 'nhom25_ev_export');
        echo '<p><a class="button button-primary" href="' . esc_url($url) . '">Xuất toàn bộ sự kiện ra CSV</a></p></div>';
    });
});

add_action('admin_post_nhom25_ev_export', function () {
    if (!current_user_can('manage_woocommerce') || !check_admin_referer('nhom25_ev_export')) {
        wp_die('Không có quyền');
    }
    global $wpdb;
    $rows = $wpdb->get_results('SELECT ts, sid, event, src, medium, campaign, product_id, order_id, value FROM ' . nhom25_ev_table() . ' ORDER BY id', ARRAY_A);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=nhom25_events_' . current_time('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ts', 'sid', 'event', 'src', 'medium', 'campaign', 'product_id', 'order_id', 'value']);
    foreach ($rows as $r) fputcsv($out, $r);
    exit;
});
