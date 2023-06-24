<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
add_action('admin_init', 'advance_payment_check_woocommerce');
function advance_payment_check_woocommerce()
{
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'advance_payment_woocommerce_missing_notice');
    }
}

// Display WooCommerce missing notice
function advance_payment_woocommerce_missing_notice()
{
    $message = __('Advance Payment Plugin requires WooCommerce to be installed and activated.', 'wc-advance-payment');
    echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
}
