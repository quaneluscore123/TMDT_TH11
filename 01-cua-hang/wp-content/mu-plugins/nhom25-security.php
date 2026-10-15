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

// An phien ban phan mem (buoi 12 hang muc 8): bo the generator cua WordPress va WooCommerce
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

// Chan liet ke ten dang nhap qua ?author=N va trang luu tru tac gia (buoi 12 hang muc 4)
add_action('template_redirect', function () {
    if (is_author() || (isset($_GET['author']) && !is_admin())) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
}, 0);

// ---------- Lop bao ve chay o tang PHP (khong phu thuoc nginx), dung duoc tren hosting chia se ----------

// Khong cho sua ma plugin/giao dien tu trang quan tri (lo tai khoan admin khong thanh lo ma nguon)
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

// Tat XML-RPC (kenh do mat khau hang loat) va tieu de X-Pingback
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_headers', function ($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});

// Tat mat khau ung dung: chi dang nhap qua trang dang nhap co 2FA
add_filter('wp_is_application_passwords_available', '__return_false');

// Khong liet ke nguoi dung qua REST API (ca /wp-json/wp/v2/users lan /?rest_route=/wp/v2/users) khi chua dang nhap
add_filter('rest_endpoints', function ($endpoints) {
    if (!is_user_logged_in()) {
        foreach (array_keys($endpoints) as $route) {
            if (strpos($route, '/wp/v2/users') === 0) {
                unset($endpoints[$route]);
            }
        }
    }
    return $endpoints;
});

// Khong dua tai khoan vao so do trang (wp-sitemap-users-1.xml lo ten dang nhap)
add_filter('wp_sitemaps_add_provider', function ($provider, $name) {
    return $name === 'users' ? false : $provider;
}, 10, 2);

// Tieu de bao mat gui tu PHP: nginx cua ban Docker an ban trung (proxy_hide_header), tren hosting chia se
// thi day la lop chinh cung voi .htaccess trong thu muc trien-khai/
function nhom25_security_headers()
{
    if (headers_sent()) {
        return;
    }
    header_remove('X-Powered-By'); // khong lo phien ban PHP
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; object-src 'none'");
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (is_ssl()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}
add_action('send_headers', 'nhom25_security_headers');
add_action('login_init', 'nhom25_security_headers');
add_action('admin_init', 'nhom25_security_headers');
