<?php
/**
 * Plugin Name: WC Advance Payment
 * Plugin URI: https://github.com/
 * Description: Adds advance payment functionality to the WooCommerce store checkout.
 * Version: 0.0.1
 * Author: Shameem Reza
 * Author URI: https://shameem.dev/
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Translation
 */
function wc_advance_payment_load_plugin_textdomain() {
  load_plugin_textdomain('wc-advance-payment', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'wc_advance_payment_load_plugin_textdomain');

/**
 * Load the plugin
 */
function load_advance_payment_plugin() {
  if (class_exists('WooCommerce')) {
      require_once 'class-advance-payment-settings.php';
      require_once 'class-advance-payment.php';
  }
}

add_action('plugins_loaded', 'load_advance_payment_plugin');
