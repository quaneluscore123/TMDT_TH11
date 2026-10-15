<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nhom25: viet lai xu ly ket qua VNPay theo nguyen tac buoi 4
 * - ReturnUrl (trinh duyet): chi kiem tra chu ky va chuyen huong, KHONG doi trang thai don, KHONG xoa gio.
 * - IPN (may chu VNPay goi toi): noi DUY NHAT ghi nhan thanh toan. Kiem tra chu ky, so tien,
 *   chong xu ly trung, ton kho; ghi nhat ky moi lan goi (WooCommerce > Trang thai > Nhat ky > nhom25-vnpay).
 *   Dia chi IPN khai bao voi VNPay: https://<ten-mien>/?wc-api=nhom25_vnpay_ipn
 */
if (!class_exists('Nhom25_VNPay_IPN')) {
    class Nhom25_VNPay_IPN
    {
        /** Lay cac tham so vnp_* va tinh chuoi bam theo dac ta VNPay 2.1.0 */
        public static function hash_data(array $params)
        {
            $data = array();
            foreach ($params as $k => $v) {
                if (substr($k, 0, 4) === 'vnp_' && $k !== 'vnp_SecureHash' && $k !== 'vnp_SecureHashType') {
                    $data[$k] = $v;
                }
            }
            ksort($data);
            $parts = array();
            foreach ($data as $k => $v) {
                $parts[] = urlencode($k) . '=' . urlencode($v);
            }
            return implode('&', $parts);
        }

        public static function sign(array $params, $secret)
        {
            return hash_hmac('sha512', self::hash_data($params), (string) $secret);
        }

        /** So sanh hang thoi gian (hash_equals) de khong lo thong tin qua thoi gian phan hoi */
        public static function verify(array $params, $secret)
        {
            if (empty($params['vnp_SecureHash']) || $secret === '') {
                return false;
            }
            return hash_equals(self::sign($params, $secret), strtolower((string) $params['vnp_SecureHash']));
        }

        /** Tim don tu vnp_TxnRef dang "<ma don>-<thoi diem>" */
        public static function order_from_ref($ref)
        {
            if (!preg_match('/^(\d+)/', (string) $ref, $m)) {
                return false;
            }
            return wc_get_order((int) $m[1]);
        }

        public static function log($msg, $ctx = array())
        {
            wc_get_logger()->info($msg . ($ctx ? ' ' . wp_json_encode($ctx) : ''), array('source' => 'nhom25-vnpay'));
        }

        /**
         * Xu ly mot thong bao IPN. Tra ve mang [RspCode, Message] theo dac ta VNPay.
         * Tach rieng khoi dau ra HTTP de kiem thu duoc bang tests/kiem-tra-ipn-vnpay.php.
         */
        public static function process(array $p, $secret)
        {
            $ctx = array(
                'txn_ref' => isset($p['vnp_TxnRef']) ? $p['vnp_TxnRef'] : '',
                'amount' => isset($p['vnp_Amount']) ? $p['vnp_Amount'] : '',
                'code' => isset($p['vnp_ResponseCode']) ? $p['vnp_ResponseCode'] : '',
                'vnp_txn' => isset($p['vnp_TransactionNo']) ? $p['vnp_TransactionNo'] : '',
            );

            if (!self::verify($p, $secret)) {
                self::log('IPN bi tu choi: chu ky khong hop le', $ctx);
                return array('97', 'Invalid signature');
            }
            $order = self::order_from_ref($ctx['txn_ref']);
            if (!$order || $order->get_payment_method() !== 'woo_vnpay') {
                self::log('IPN bi tu choi: khong tim thay don', $ctx);
                return array('01', 'Order not found');
            }
            $expected = (string) round((float) $order->get_total() * 100);
            if ((string) $ctx['amount'] !== $expected) {
                $order->add_order_note(sprintf('VNPAY IPN: số tiền không khớp (VNPAY %s, đơn %s). Chưa ghi nhận thanh toán.', $ctx['amount'], $expected));
                self::log('IPN bi tu choi: lech so tien', $ctx + array('expected' => $expected));
                return array('04', 'Invalid amount');
            }
            // Chong xu ly trung: don da qua buoc cho thanh toan thi khong lam gi them
            if (!$order->has_status(array('pending', 'failed'))) {
                self::log('IPN trung: don da duoc xac nhan truoc do', $ctx + array('status' => $order->get_status()));
                return array('02', 'Order already confirmed');
            }

            $paid = $ctx['code'] === '00' && (!isset($p['vnp_TransactionStatus']) || $p['vnp_TransactionStatus'] === '00');
            if (!$paid) {
                $order->update_status('failed', sprintf('VNPAY IPN: giao dịch không thành công (mã %s).', esc_html($ctx['code'])));
                self::log('IPN: thanh toan that bai', $ctx);
                return array('00', 'Confirm Success');
            }

            // Het hang trong luc khach dang tra tien: khong tru ton kho am, giu don de hoan tien
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                if ($product && $product->managing_stock() && (int) $product->get_stock_quantity() < (int) $item->get_quantity()) {
                    $order->set_transaction_id($ctx['vnp_txn']);
                    // WooCommerce tu tru kho khi don sang "Tam giu" - danh dau da xu ly de ton kho khong bi am
                    $order->get_data_store()->set_stock_reduced($order->get_id(), true);
                    $order->update_status('on-hold', sprintf('VNPAY IPN: đã nhận tiền (mã GD %s) nhưng "%s" chỉ còn %d, cần %d. CẦN HOÀN TIỀN hoặc liên hệ khách trước khi giao.', esc_html($ctx['vnp_txn']), $product->get_name(), (int) $product->get_stock_quantity(), (int) $item->get_quantity()));
                    self::log('IPN: da nhan tien nhung het hang - giu don de hoan tien', $ctx);
                    return array('00', 'Confirm Success');
                }
            }

            $order->payment_complete($ctx['vnp_txn']);
            $order->add_order_note(sprintf('VNPAY IPN: thanh toán thành công, mã giao dịch VNPAY %s.', esc_html($ctx['vnp_txn'])));
            self::log('IPN: thanh toan thanh cong', $ctx);
            return array('00', 'Confirm Success');
        }

        public static function handle()
        {
            $gateway = new Woo_VNPay_Payment_Gateway();
            list($code, $message) = self::process(wp_unslash($_GET), $gateway->get_option('vnp_HashSecret'));
            wp_send_json(array('RspCode' => $code, 'Message' => $message));
        }
    }
}

if (!class_exists('VNPAYResponse')) {
    class VNPAYResponse
    {
        public function __construct()
        {
            add_action('wp_ajax_vnpay_response', array($this, 'checkResponse'));
            add_action('wp_ajax_nopriv_vnpay_response', array($this, 'checkResponse'));
            add_action('woocommerce_api_nhom25_vnpay_ipn', array('Nhom25_VNPay_IPN', 'handle'));
        }

        /** ReturnUrl: khach quay ve tu VNPay. Chi chuyen huong, khong doi trang thai don. */
        public function checkResponse()
        {
            $params = wp_unslash($_GET);
            $gateway = new Woo_VNPay_Payment_Gateway();
            $order = Nhom25_VNPay_IPN::order_from_ref(isset($params['vnp_TxnRef']) ? $params['vnp_TxnRef'] : '');
            $valid = Nhom25_VNPay_IPN::verify($params, $gateway->get_option('vnp_HashSecret'));

            if (!$order) {
                wc_add_notice('Không tìm thấy đơn hàng tương ứng với giao dịch VNPAY.', 'error');
                wp_safe_redirect(wc_get_checkout_url());
                exit;
            }
            // Hosting chan IPN tu may chu VNPay (vi du InfinityFree): cho phep xac nhan qua ReturnUrl, nhung CHI qua
            // cung ham kiem tra cua IPN (chu ky HMAC-SHA512 + so tien + chong xu ly trung). Tat mac dinh; bat bang hang so
            // NHOM25_VNPAY_XAC_NHAN_QUA_RETURN = true trong wp-config.php cua rieng hosting do.
            if ($valid && defined('NHOM25_VNPAY_XAC_NHAN_QUA_RETURN') && NHOM25_VNPAY_XAC_NHAN_QUA_RETURN) {
                list($code, $message) = Nhom25_VNPay_IPN::process($params, $gateway->get_option('vnp_HashSecret'));
                $order = wc_get_order($order->get_id());
                $order->add_order_note(sprintf('Xác nhận qua ReturnUrl (hosting không nhận được IPN): %s %s.', esc_html($code), esc_html($message)));
            } else {
                $order->add_order_note(sprintf('Khách quay về từ VNPAY (mã %s, chữ ký %s). Trạng thái đơn chỉ đổi khi nhận IPN.', esc_html(isset($params['vnp_ResponseCode']) ? $params['vnp_ResponseCode'] : '?'), $valid ? 'hợp lệ' : 'KHÔNG hợp lệ'));
            }

            if ($valid && isset($params['vnp_ResponseCode']) && $params['vnp_ResponseCode'] === '00') {
                $url = add_query_arg($params, $order->get_checkout_order_received_url());
                wp_safe_redirect($url);
                exit;
            }
            // That bai hoac huy: quay lai trang thanh toan, gio hang van con, goi y phuong thuc khac
            wc_add_notice('Thanh toán qua VNPAY chưa thành công. Giỏ hàng của bạn vẫn còn nguyên — bạn có thể thử lại hoặc chọn Thanh toán khi nhận hàng (COD).', 'error');
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }

        public function getOrder($orderId)
        {
            return Nhom25_VNPay_IPN::order_from_ref($orderId);
        }
    }
}
