<?php
/**
 * Plugin Name: VietQR Payment Gateway
 * Description: Thanh toán qua mã VietQR
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

add_action('plugins_loaded', 'init_vietqr_gateway');

function init_vietqr_gateway() {
    if (!class_exists('WC_Payment_Gateway')) return;

    class WC_Gateway_VietQR extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'vietqr';
            $this->method_title = 'VietQR';
            $this->method_description = 'Thanh toán qua mã VietQR';
            $this->has_fields = true;
            $this->supports = ['products'];

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title', 'VietQR');
            $this->description = $this->get_option('description', 'Quét mã VietQR để thanh toán');
            $this->bank_bin = $this->get_option('bank_bin', '970422');
            $this->account_number = $this->get_option('account_number', '');
            $this->account_name = $this->get_option('account_name', '');
            $this->bank_name = $this->get_option('bank_name', '');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('woocommerce_thankyou_' . $this->id, [$this, 'thankyou_page']);
        }

        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title' => 'Bật/Tắt',
                    'type' => 'checkbox',
                    'label' => 'Bật phương thức thanh toán',
                    'default' => 'yes'
                ],
                'title' => [
                    'title' => 'Tiêu đề',
                    'type' => 'text',
                    'default' => 'VietQR'
                ],
                'description' => [
                    'title' => 'Mô tả',
                    'type' => 'textarea',
                    'default' => 'Quét mã VietQR để thanh toán'
                ],
                'bank_name' => [
                    'title' => 'Tên ngân hàng',
                    'type' => 'text',
                    'default' => 'Vietcombank'
                ],
                'bank_bin' => [
                    'title' => 'Mã BIN ngân hàng',
                    'type' => 'text',
                    'default' => '970422'
                ],
                'account_number' => [
                    'title' => 'Số tài khoản',
                    'type' => 'text',
                    'default' => ''
                ],
                'account_name' => [
                    'title' => 'Tên tài khoản',
                    'type' => 'text',
                    'default' => ''
                ]
            ];
        }

        /** Anh QR theo chuan VietQR (NAPAS). Trinh duyet tai anh truc tiep, may chu khong goi API ben ngoai. */
        public function get_vietqr_image($amount, $message) {
            if (empty($this->account_number) || empty($this->bank_bin)) return '';
            return 'https://img.vietqr.io/image/' . rawurlencode($this->bank_bin) . '-' . rawurlencode($this->account_number) . '-compact2.png?'
                . http_build_query(['amount' => (int) round($amount), 'addInfo' => $message, 'accountName' => $this->account_name]);
        }

        // Nhom25: o trang thanh toan chi huong dan; ma QR kem noi dung DH<ma don> hien sau khi dat hang
        public function payment_fields() {
            echo '<div class="vietqr-payment">';
            echo '<p>Quét mã QR bằng ứng dụng ngân hàng bất kỳ hoặc ví hỗ trợ VietQR. Không mất phí, tiền về cửa hàng ngay.</p>';
            echo '<ol style="margin-left:1.2em">';
            echo '<li>Bấm <strong>Đặt hàng</strong> — trang tiếp theo hiện mã QR đã điền sẵn số tiền và nội dung.</li>';
            echo '<li>Giữ nguyên nội dung chuyển khoản <strong>DH&lt;mã đơn&gt;</strong> để cửa hàng đối chiếu đúng đơn.</li>';
            echo '<li>Cửa hàng xác nhận trong giờ làm việc (8:00–21:00) sau khi kiểm tra sao kê ngân hàng, rồi mới giao hàng.</li>';
            echo '</ol>';
            echo '</div>';
        }

        public function thankyou_page($order_id) {
            $order = wc_get_order($order_id);
            $total = $order->get_total();
            $qr_url = $this->get_vietqr_image($total, 'DH' . $order_id);
            echo '<div class="vietqr-thankyou">';
            echo '<h2>Thông tin thanh toán VietQR</h2>';
            if ($qr_url) {
                echo '<img src="' . esc_url($qr_url) . '" alt="Mã VietQR cho đơn DH' . (int) $order_id . '" style="max-width:250px;" />';
                echo '<p><strong>Ngân hàng:</strong> ' . esc_html($this->bank_name) . '</p>';
                echo '<p><strong>Số tài khoản:</strong> ' . esc_html($this->account_number) . '</p>';
                echo '<p><strong>Tên tài khoản:</strong> ' . esc_html($this->account_name) . '</p>';
            } else {
                echo '<p><strong>Bản thử nghiệm:</strong> cửa hàng chưa khai báo tài khoản nhận tiền nên chưa hiện mã QR. Đơn được giữ ở trạng thái "Tạm giữ"; nhân viên sẽ liên hệ qua số điện thoại bạn đã nhập.</p>';
            }
            echo '<p><strong>Số tiền:</strong> ' . wc_price($total) . '</p>';
            echo '<p>Nội dung chuyển khoản: <strong>DH' . (int) $order_id . '</strong></p>';
            echo '</div>';
        }

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            $order->update_status('on-hold', 'Chờ thanh toán VietQR');
            WC()->cart->empty_cart();
            return [
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            ];
        }
    }
}

add_filter('woocommerce_payment_gateways', 'add_vietqr_gateway');
function add_vietqr_gateway($gateways) {
    $gateways[] = 'WC_Gateway_VietQR';
    return $gateways;
}
