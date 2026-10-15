<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Woo_VNPay_Payment_Gateway')) :
    class Woo_VNPay_Payment_Gateway extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'woo_vnpay';
            $this->has_fields = false;
            $this->method_title = __('VNPAY', 'woo-vnpay');
            $this->method_description = __('Thanh toán qua VNPAY.', 'woo-vnpay');

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->instructions = "";

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_filter('woocommerce_order_button_text', array($this, 'order_button_name'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'vnpay_payment_results'));
        }

        public function init_form_fields()
        {
            $this->form_fields = include('vnpay/vnpay-settings.php');
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            $order->update_status($this->get_option('order_created'));

            return array(
                'result' => 'success',
                'redirect' => $this->get_pay_url($order)
            );
        }

        public function get_pay_url($order)
        {
            $vnp_TmnCode = $this->get_option('vnp_TmnCode');
            $vnp_HashSecret = $this->get_option('vnp_HashSecret');
            $vnp_Url = $this->get_option('vnp_Url');
            $vnp_Returnurl = admin_url('admin-ajax.php?action=vnpay_response&type=international');
            $array = array(
                '{{orderid}}' => $order->get_id()
            );
            $order_desc = strtr($this->get_option('order_desc'), $array);
            // Nhom25: ma tham chieu rieng cho moi lan thanh toan (thu lai cung don khong bi VNPay tu choi trung)
            $vnp_TxnRef = $order->get_id() . '-' . time();
            $order->update_meta_data('_nhom25_vnp_txnref', $vnp_TxnRef);
            $order->save();
            $vnp_OrderInfo = $order_desc;
            $vnp_OrderType = 'other';
            $vnp_Amount = number_format($order->get_total(), 2, '.', '') * 100;
            $vnp_Locale = 'vn';
            $vnp_BankCode = '';
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                // Nhom25: VNPay dung gio Viet Nam (GMT+7); bat buoc co thoi han thanh toan
                "vnp_CreateDate" => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('YmdHis'),
                "vnp_ExpireDate" => (new DateTime('+15 minutes', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            );
            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }
            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            return $vnp_Url;
        }

        /**
         * Nhom25: trang "Da nhan don" chi HIEN THI ket qua.
         * Trang thai don chi doi khi nhan thong bao IPN may chu toi may chu (Nhom25_VNPay_IPN),
         * vi tham so tren trinh duyet do nguoi dung kiem soat (ly thuyet buoi 4, slide 17 va 24).
         */
        public function vnpay_payment_results($order_id)
        {
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }
            $hasParams = isset($_GET['vnp_SecureHash']);
            $validSig = $hasParams && Nhom25_VNPay_IPN::verify(wp_unslash($_GET), $this->get_option('vnp_HashSecret'));

            if ($order->is_paid()) {
                $title = 'Thanh toán thành công';
                $icon = 'correct.svg';
                $msg = sprintf('Đơn hàng #%s đã được thanh toán. Mã giao dịch VNPAY: %s.', $order->get_id(), esc_html($order->get_transaction_id()));
            } elseif ($order->has_status('failed')) {
                $title = 'Thanh toán không thành công';
                $icon = 'caution.svg';
                $msg = 'Ngân hàng chưa chấp nhận giao dịch. Giỏ hàng của bạn vẫn được giữ nguyên; bạn có thể thanh toán lại hoặc chọn Thanh toán khi nhận hàng.';
            } elseif ($hasParams && !$validSig) {
                $title = 'Cảnh báo';
                $icon = 'caution.svg';
                $msg = 'Chữ ký phản hồi không hợp lệ. Đơn hàng chưa được ghi nhận là đã thanh toán.';
            } else {
                $title = 'Đang chờ xác nhận từ VNPAY';
                $icon = 'caution.svg';
                $msg = 'Cửa hàng chỉ ghi nhận thanh toán khi nhận được thông báo trực tiếp từ VNPAY. Trang này sẽ cập nhật sau ít phút; bạn không cần thanh toán lại.';
            }
            ?>
            <section class="vnpay-payment-gateway-wrapper">
                <div class="vnpay-payment-gateway-notification">
                    <img src="<?= esc_url(WOO_VNPAY_PLUGIN_URL . 'assets/images/' . $icon); ?>" alt="">
                    <h2 class="notification-title"><?php echo esc_html($title); ?></h2>
                    <p class="notification-content"><?php echo wp_kses_post($msg); ?></p>
                </div>
            </section>
            <?php
        }

        public function order_button_name($order_button_name)
        {
            $chosen_payment_method = WC()->session->get('chosen_payment_method');
            if ($chosen_payment_method == 'woo_vnpay') {
                $order_button_name = $this->get_option('button_label');;
            } ?>
            <script type="text/javascript">
                (function ($) {
                    $('form.checkout').on('change', 'input[name^="payment_method"]', function () {
                        var t = {
                            updateTimer: !1, dirtyInput: !1,
                            reset_update_checkout_timer: function () {
                                clearTimeout(t.updateTimer)
                            }, trigger_update_checkout: function () {
                                t.reset_update_checkout_timer(), t.dirtyInput = !1,
                                    $(document.body).trigger("update_checkout")
                            }
                        };
                        t.trigger_update_checkout();
                    });
                })(jQuery);
            </script><?php
            return $order_button_name;
        }

        public function isValidCurrency()
        {
            return in_array(get_woocommerce_currency(), array('VND'));
        }

        public function admin_options()
        {
            if ($this->isValidCurrency()) {
                parent::admin_options();
            } else {
                ?>
                <div class="inline error">
                    <p>
                        <strong><?php _e('Phương thức thanh toán không khả dụng', 'woo-vnpay'); ?></strong>:
                        <?php _e('VNPAY không hỗ trợ đơn vị tiền tệ của bạn. Hiện tại, VNPAY chỉ hỗ trợ đơn vị tiền tệ Việt Nam Đồng (VND).', 'woo-vnpay'); ?>
                    </p>
                </div>
                <?php
            }
        }
    }
endif;
