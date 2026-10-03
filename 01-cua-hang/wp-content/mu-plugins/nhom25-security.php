<?php
/**
 * Plugin Name: Nhom25 - Gioi han dang nhap
 * Description: Sai mat khau 5 lan -> khoa IP 15 phut (TH12 - NV2).
 */

if (!defined('ABSPATH')) exit;

const NHOM25_LOGIN_MAX_FAILS = 5;
const NHOM25_LOGIN_LOCK_SECONDS = 15 * 60;

/** IP that cua khach: chi tin X-Real-IP khi request di qua proxy noi bo (nginx trong mang docker). */
function nhom25_client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $is_private = !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    if ($is_private && !empty($_SERVER['HTTP_X_REAL_IP']) && filter_var(trim($_SERVER['HTTP_X_REAL_IP']), FILTER_VALIDATE_IP)) {
        $ip = trim($_SERVER['HTTP_X_REAL_IP']);
    }
    return $ip;
}

function nhom25_login_key() {
    return 'nhom25_login_' . md5(nhom25_client_ip());
}

function nhom25_lock_message($data) {
    return sprintf('<strong>Tạm khóa:</strong> đăng nhập sai quá %d lần. Vui lòng thử lại sau %d phút.',
        NHOM25_LOGIN_MAX_FAILS, (int) ceil(($data['until'] - time()) / 60));
}

add_action('wp_login_failed', function () {
    $data = get_transient(nhom25_login_key()) ?: ['fails' => 0, 'until' => 0];
    $data['fails']++;
    if ($data['fails'] >= NHOM25_LOGIN_MAX_FAILS) {
        $data['until'] = time() + NHOM25_LOGIN_LOCK_SECONDS;
    }
    set_transient(nhom25_login_key(), $data, NHOM25_LOGIN_LOCK_SECONDS);
});

// Uu tien 99: ghi de ket qua cac bo xac thuc truoc, ke ca khi mat khau dung
add_filter('authenticate', function ($user) {
    $data = get_transient(nhom25_login_key());
    if ($data && $data['until'] > time()) {
        return new WP_Error('nhom25_locked', nhom25_lock_message($data));
    }
    return $user;
}, 99);

add_action('wp_login', function () {
    delete_transient(nhom25_login_key());
});

// Thong bao chung chung (khong cho biet ten dang nhap co ton tai) + so lan con lai
add_filter('login_errors', function ($error) {
    $data = get_transient(nhom25_login_key());
    if (!$data) return $error;
    if ($data['until'] > time()) return nhom25_lock_message($data);
    return '<strong>Lỗi:</strong> tên đăng nhập hoặc mật khẩu không đúng. Còn ' . max(0, NHOM25_LOGIN_MAX_FAILS - $data['fails']) . ' lần thử.';
});
