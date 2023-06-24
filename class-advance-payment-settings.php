<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Advance_Payment_Settings')) {

    class Advance_Payment_Settings {

        /**
         * Initialize the settings
         */
        public static function init() {
            add_action('woocommerce_checkout_order_review', array(__CLASS__, 'display_advance_payment_option'));
            add_action('woocommerce_checkout_update_order_meta', array(__CLASS__, 'save_advance_payment_option'));
            add_action('woocommerce_order_details_after_order_table', array(__CLASS__, 'display_advance_payment_info'), 10, 1);
            add_filter('woocommerce_calculated_total', array(__CLASS__, 'calculate_total_amount'), 10, 2);
            add_filter('woocommerce_settings_tabs_array', array(__CLASS__, 'add_settings_tab'), 50);
            add_action('woocommerce_settings_advanced_payment', array(__CLASS__, 'output_settings'));
            add_action('woocommerce_update_options_advanced_payment', array(__CLASS__, 'save_settings'));
        }

        /**
         * Display the advance payment option on the checkout page
         */
        public static function display_advance_payment_option() {
            // Remove the checkbox code completely
        }
          

        /**
         * Save the advance payment option in the order meta
         *
         * @param int $order_id
         */
        public static function save_advance_payment_option($order_id) {
            if (isset($_POST['advance_payment_checkbox'])) {
                update_post_meta($order_id, 'advance_payment_option', 'yes');
            }
        }

        /**
         * Display the advance payment information in the order details
         *
         * @param WC_Order $order
         */
        public static function display_advance_payment_info($order) {
            $advance_payment_option = get_post_meta($order->get_id(), 'advance_payment_option', true);

            if ($advance_payment_option === 'yes') {
                $advance_payment_type = get_option('advance_payment_type', 'fixed');
                $advance_payment_value = get_option('advance_payment_value', 0);

                if ($advance_payment_type === 'percentage') {
                    $advance_payment = $order->get_total() * ($advance_payment_value / 100);
                } else {
                    $advance_payment = $advance_payment_value;
                }

                $remaining_payment = $order->get_total() - $advance_payment;

                if ($advance_payment > 0) {
                    echo '<p><strong>' . __('Advance Payment:', 'wc-advance-payment') . '</strong> ' . wc_price($advance_payment) . '</p>';
                }

                echo '<p><strong>' . __('Remaining Payment:', 'wc-advance-payment') . '</strong> ' . wc_price($remaining_payment) . '</p>';
            }
        }

        /**
         * Calculate the total amount with the deducted advance payment
         *
         * @param float $total
         * @param WC_Cart $cart
         * @return float
         */
        public static function calculate_total_amount($total, $cart) {
            $advance_payment_option = WC()->session->get('advance_payment_option');

            if ($advance_payment_option === 'yes') {
                $advance_payment_type = get_option('advance_payment_type', 'fixed');
                $advance_payment_value = get_option('advance_payment_value', 0);

                if ($advance_payment_type === 'percentage') {
                    $advance_payment = $total * ($advance_payment_value / 100);
                } else {
                    $advance_payment = $advance_payment_value;
                }

                $remaining_payment = $total - $advance_payment;

                if ($advance_payment > 0) {
                    WC()->session->set('advance_payment', $advance_payment);
                }

                return $remaining_payment;
            }

            return $total;
        }

        /**
         * Add the settings tab
         *
         * @param array $tabs
         * @return array
         */
        public static function add_settings_tab($tabs) {
            $tabs['advanced_payment'] = __('Advanced Payment', 'wc-advance-payment');
            return $tabs;
        }

        /**
         * Output the settings
         */
        public static function output_settings() {
            woocommerce_admin_fields(self::get_settings());
        }

        /**
         * Save the settings
         */
        public static function save_settings() {
            woocommerce_update_options(self::get_settings());
        }

        /**
         * Get the settings fields
         *
         * @return array
         */
        public static function get_settings() {
            $settings = array(
                'section_title' => array(
                    'name' => __('Advanced Payment Settings', 'wc-advance-payment'),
                    'type' => 'title',
                    'desc' => '',
                    'id'   => 'advance_payment_section_title'
                ),
                'advance_payment_type' => array(
                    'name'     => __('Advanced Payment Type', 'wc-advance-payment'),
                    'type'     => 'select',
                    'desc'     => __('Choose whether the advanced payment amount is fixed or percentage-based.', 'wc-advance-payment'),
                    'id'       => 'advance_payment_type',
                    'options'  => array(
                        'fixed'     => __('Fixed Amount', 'wc-advance-payment'),
                        'percentage' => __('Percentage', 'wc-advance-payment'),
                    ),
                    'default'  => 'fixed',
                ),
                'advance_payment_value' => array(
                    'name'     => __('Advanced Payment Value', 'wc-advance-payment'),
                    'type'     => 'number',
                    'desc'     => __('Enter the advanced payment value.', 'wc-advance-payment'),
                    'id'       => 'advance_payment_value',
                    'class'    => 'wc_input_price',
                    'custom_attributes' => array(
                        'step' => '0.01',
                    ),
                    'default'  => '0',
                ),
                'section_end' => array(
                    'type' => 'sectionend',
                    'id'   => 'advance_payment_section_end'
                ),
            );

            return apply_filters('woocommerce_advance_payment_settings', $settings);
        }
    }

    Advance_Payment_Settings::init();
}
