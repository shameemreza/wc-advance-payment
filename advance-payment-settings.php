<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Add settings page to WooCommerce settings menu
add_filter('woocommerce_get_settings_pages', 'add_advance_payment_settings_page');
function add_advance_payment_settings_page($settings)
{
    $settings[] = include dirname(__FILE__) . '/class-advance-payment-settings.php';
    return $settings;
}
