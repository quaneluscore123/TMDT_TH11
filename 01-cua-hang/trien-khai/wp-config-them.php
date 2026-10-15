<?php
/*
 * Doan cau hinh them vao wp-config.php tren hosting (dan NGAY TREN dong "That's all, stop editing!").
 * KHONG tai file nay len hosting nhu mot file rieng va KHONG commit ban da dien khoa that.
 */

// Ten mien cong khai (thay TEN-MIEN bang ten mien that, vi du butchixanh.infinityfree.me)
defined('WP_HOME') || define('WP_HOME', 'https://TEN-MIEN');
defined('WP_SITEURL') || define('WP_SITEURL', 'https://TEN-MIEN');

// Hosting dung proxy: bao cho WordPress biet trang dang chay HTTPS (de cookie co co Secure, khong lap chuyen huong)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
defined('FORCE_SSL_ADMIN') || define('FORCE_SSL_ADMIN', true);

// Khong sua ma tu trang quan tri, khong hien loi chi tiet cho khach
defined('DISALLOW_FILE_EDIT') || define('DISALLOW_FILE_EDIT', true);
defined('WP_DEBUG') || define('WP_DEBUG', false);
defined('WP_DEBUG_DISPLAY') || define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', '0');

// Tu cai ban va bao mat nho cua WordPress
defined('WP_AUTO_UPDATE_CORE') || define('WP_AUTO_UPDATE_CORE', 'minor');

// Khoa thanh toan (thay cho file .env). De TRONG thi cong VNPay tu an.
// InfinityFree chan thong bao IPN cua VNPay nen KHONG nen dien khoa VNPay tren hosting nay.
defined('VNP_TMN_CODE') || define('VNP_TMN_CODE', '');
defined('VNP_HASH_SECRET') || define('VNP_HASH_SECRET', '');
// InfinityFree chan IPN: dat true de xac nhan don khi khach quay ve tu VNPay (van kiem tra chu ky, so tien, chong trung).
defined('NHOM25_VNPAY_XAC_NHAN_QUA_RETURN') || define('NHOM25_VNPAY_XAC_NHAN_QUA_RETURN', false);
defined('VIETQR_BANK_BIN') || define('VIETQR_BANK_BIN', '970436');
defined('VIETQR_ACCOUNT_NUMBER') || define('VIETQR_ACCOUNT_NUMBER', '');
defined('VIETQR_ACCOUNT_NAME') || define('VIETQR_ACCOUNT_NAME', '');

// NHO: thay 8 dong AUTH_KEY ... NONCE_SALT trong wp-config.php bang bo moi lay tu
// https://api.wordpress.org/secret-key/1.1/salt/  (moi trang web mot bo rieng, khong dung lai cua may khac)
