<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('VNPAYResponse')) {
    class VNPAYResponse
    {
        public function __construct()
        {
            $this->action();
        }

        public function action()
        {
            add_action('wp_ajax_vnpay_response', array($this, 'checkResponse'));
            add_action('wp_ajax_nopriv_vnpay_response', array($this, 'checkResponse'));
        }

        public function checkResponse()
        {
            global $woocommerce;
            $checkoutUrl = $woocommerce->cart->get_checkout_url();
            $gateway = new Woo_VNPay_Payment_Gateway;
            $hashSecret = $gateway->get_option('vnp_HashSecret');
            $inputData = array();
            $returnData = array();
            $amount = $_GET["vnp_Amount"] / 100;
            $txnResponseCode = $_GET["vnp_ResponseCode"];
            $order = $this->getOrder($_GET["vnp_OrderInfo"]);
            $data = $_REQUEST;
            foreach ($data as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }
            $vnp_SecureHash = $inputData['vnp_SecureHash'];
            unset($inputData['vnp_SecureHashType']);
            unset($inputData['vnp_SecureHash']);
            ksort($inputData);
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }
            $vnpTranId = $inputData['vnp_TransactionNo'];
            $vnp_BankCode = $inputData['vnp_BankCode'];
            $secureHash = hash_hmac('sha512', $hashData, $hashSecret);
            $Status = 0;
            $orderId = $inputData['vnp_TxnRef'];
            $transStatus = '';

            if ($order->post_status != null && $order->post_status != '') {
                if ($secureHash == $vnp_SecureHash) {
                    if ($order->get_status() != null && $order->get_status() != $gateway->get_option('payment_success')) {
                        if ($inputData['vnp_ResponseCode'] == '00') {
                            $transStatus = 'Thanh toan thanh cong. Ma giao dich tai VNPAY: ' . $vnpTranId . '. Noi dung thanh toan: ' . $_GET["vnp_OrderInfo"];
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                            $returnData['Signature'] = $secureHash;
                            $order->payment_complete($vnpTranId);
                            $order->add_order_note(__($transStatus, 'woo-vnpay'));
                            $woocommerce->cart->empty_cart();
                            $url = wc_get_checkout_url() . 'order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&' . $hashData . '&vnp_SecureHash=' . $vnp_SecureHash;
                            wp_redirect($url);
                        } else {
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                            $returnData['Signature'] = $secureHash;
                            $order->add_order_note(__('Thanh toan khong thanh cong', 'woo-vnpay'));
                            $order->update_status('failed');
                            $woocommerce->cart->empty_cart();
                            $url = wc_get_checkout_url() . 'order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&' . $hashData . '&vnp_SecureHash=' . $vnp_SecureHash;
                            wp_redirect($url);
                        }
                    } else {
                        $returnData['RspCode'] = '02';
                        $returnData['Message'] = 'Order already confirmed';
                        $woocommerce->cart->empty_cart();
                        $url = wc_get_checkout_url() . 'order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&' . $hashData . '&vnp_SecureHash=' . $vnp_SecureHash;
                        wp_redirect($url);
                    }
                } else {
                    $returnData['RspCode'] = '97';
                    $returnData['Message'] = 'Chu ky khong hop le';
                    $returnData['Signature'] = $secureHash;
                    $order->update_status('failed');
                    $order->add_order_note(__('Chu ky khong hop le', 'woo-vnpay'));
                    $woocommerce->cart->empty_cart();
                    $url = wc_get_checkout_url() . 'order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&' . $hashData . '&vnp_SecureHash=' . $vnp_SecureHash;
                    wp_redirect($url);
                }
            } else {
                $returnData['RspCode'] = '01';
                $returnData['Message'] = 'Order not found';
                $order->update_status('failed');
                $order->add_order_note(__('Order not found', 'woo-vnpay'));
                $woocommerce->cart->empty_cart();
                $url = wc_get_checkout_url() . 'order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&' . $hashData . '&vnp_SecureHash=' . $vnp_SecureHash;
                wp_redirect($url);
            }
            echo json_encode($returnData);
            die();
        }

        public function getOrder($orderId)
        {
            preg_match_all('!\d+!', $orderId, $matches);
            $order = new \WC_Order($matches[0][0]);
            return $order;
        }
    }
}
