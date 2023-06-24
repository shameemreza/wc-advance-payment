<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Advance_Payment')) {

    class Advance_Payment {

        /**
         * Initialize the class
         */
        public static function init() {
            add_action('woocommerce_checkout_create_order', array(__CLASS__, 'add_advance_payment_to_order'), 10, 2);
            add_action('woocommerce_review_order_before_payment', array(__CLASS__, 'display_advance_payment_info'));
            add_action('woocommerce_admin_order_data_after_order_details', array(__CLASS__, 'display_advance_payment_details'));
            add_action('woocommerce_email_order_meta', array(__CLASS__, 'add_advance_payment_details_to_email'), 10, 3);
            add_action('woocommerce_thankyou', array(__CLASS__, 'display_advanced_payment_on_order_received'), 10, 1);
        }

        /**
         * Add advance payment to the order
         *
         * @param WC_Order $order
         * @param array    $data
         */
        public static function add_advance_payment_to_order($order, $data) {
            $requirement = get_option('advance_payment_requirement', 'optional');
            $payment_type = get_option('advance_payment_type', 'fixed');
            $payment_value = floatval(get_option('advance_payment_value', 0));

            if ($requirement === 'required' || ($requirement === 'optional' && isset($_POST['advance_payment']) && $_POST['advance_payment'] === 'yes')) {
                if ($payment_type === 'percentage') {
                    $total = $order->get_subtotal();
                    $advance_payment = ($total * $payment_value) / 100;
                } else {
                    $advance_payment = $payment_value;
                }

                $order->set_total($order->get_total() - $advance_payment);
                $order->update_meta_data('advance_payment', $advance_payment);
                $order->update_meta_data('advance_payment_due', $order->get_total());
            }

            // Save payment information to order meta
            $payment_information = isset($_POST['payment_information']) ? sanitize_textarea_field($_POST['payment_information']) : '';
            $order->update_meta_data('payment_information', $payment_information);
            $order->save();
        }

        /**
         * Display advance payment information on the checkout page
         */
        public static function display_advance_payment_info() {
            $requirement = get_option('advance_payment_requirement', 'optional');
            $payment_type = get_option('advance_payment_type', 'fixed');
            $payment_value = floatval(get_option('advance_payment_value', 0));

            $total = WC()->cart->subtotal;
            $advance_payment = 0;

            if ($payment_type === 'percentage') {
                $advance_payment = ($total * $payment_value) / 100;
            } elseif ($payment_type === 'fixed') {
                $advance_payment = $payment_value;
            }

            if (($requirement === 'required') || (isset($_POST['make_advance_payment']) && $_POST['make_advance_payment'] === 'yes')) {
                $due_amount = $total - $advance_payment;

                echo '<div class="advance-payment-info">';
                echo '<h3>' . __('Advance Payment Information', 'wc-advance-payment') . '</h3>';
                echo '<p>' . __('Advance Payment Amount: ', 'wc-advance-payment') . wc_price($advance_payment) . '</p>';
                echo '<p>' . __('Due Amount Upon Receiving: ', 'wc-advance-payment') . wc_price($due_amount) . '</p>';

                // Display payment information box
                woocommerce_form_field('payment_information', array(
                    'type' => 'textarea',
                    'class' => array('form-row-wide'),
                    'label' => __('Payment Information', 'wc-advance-payment'),
                    'required' => true,
                ));

                echo '</div>';
            }
        }

        /**
         * Display advance payment details in admin order page
         *
         * @param WC_Order $order
         */
        public static function display_advance_payment_details($order) {
            $advance_payment = $order->get_meta('advance_payment');
            $payment_information = $order->get_meta('payment_information');

            echo '<div class="advance-payment-details">';
            echo '<h3>' . __('Advance Payment Details', 'wc-advance-payment') . '</h3>';
            echo '<p><strong>' . __('Advance Payment Amount:', 'wc-advance-payment') . '</strong> ' . wc_price($advance_payment) . '</p>';
            echo '<p><strong>' . __('Payment Information:', 'wc-advance-payment') . '</strong> ' . esc_html($payment_information) . '</p>';
            echo '</div>';
        }

        /**
         * Display advanced payment information on the Order Received page
         *
         * @param int $order_id
         */
        public static function display_advanced_payment_on_order_received($order_id) {
            $order = wc_get_order($order_id);
            $advance_payment = $order->get_meta('advance_payment');
            $payment_information = $order->get_meta('payment_information');

            if ($advance_payment > 0) {
                echo '<h2>' . __('Advance Payment Information', 'wc-advance-payment') . '</h2>';
                echo '<p><strong>' . __('Advance Payment Amount:', 'wc-advance-payment') . '</strong> ' . wc_price($advance_payment) . '</p>';
                echo '<p><strong>' . __('Payment Information:', 'wc-advance-payment') . '</strong> ' . esc_html($payment_information) . '</p>';
            }
        }

        /**
         * Add advance payment details to order email
         *
         * @param WC_Order $order
         * @param bool     $sent_to_admin
         * @param bool     $plain_text
         */
        public static function add_advance_payment_details_to_email($order, $sent_to_admin, $plain_text) {
            $advance_payment = $order->get_meta('advance_payment');
            $payment_information = $order->get_meta('payment_information');

            echo '<h2>' . __('Advance Payment Details', 'wc-advance-payment') . '</h2>';
            echo '<p><strong>' . __('Advance Payment Amount:', 'wc-advance-payment') . '</strong> ' . wc_price($advance_payment) . '</p>';
            echo '<p><strong>' . __('Payment Information:', 'wc-advance-payment') . '</strong> ' . esc_html($payment_information) . '</p>';
        }
    }

    Advance_Payment::init();
}
