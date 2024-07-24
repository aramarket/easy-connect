<?php
/*
 * Plugin Name:       EasyConnect
 * Plugin URI:        https://easy-ship.in
 * Description:       Seamlessly integrate WhatsApp messaging with your WooCommerce store for better customer communication and support.
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            AKASH
 * Update URI:        https://easy-ship.in
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin version and directory constants
if (!defined('EASY_WHATSAPP_VERSION')) {
    define('EASY_WHATSAPP_VERSION', '1.0.0');
}

if (!defined('EASY_WHATSAPP_DIR')) {
    define('EASY_WHATSAPP_DIR', plugin_dir_path(__FILE__));
}

// Include the main class file
require_once EASY_WHATSAPP_DIR . 'includes/ew-setting-page.php';
require_once EASY_WHATSAPP_DIR . 'includes/ew-testing-page.php';

require_once EASY_WHATSAPP_DIR . 'includes/ew-general-function.php';
require_once EASY_WHATSAPP_DIR . 'includes/ew-order-status-function.php';
require_once EASY_WHATSAPP_DIR . 'includes/ew-after-review-functions.php';

// Initialize the plugin
function run_easy_whatsapp_api() {
    $easy_whatsapp_setting = new Easy_WhatsApp_Setting();
	$ew_order_status_functions = new EW_Order_Status_Functions();
	$ew_order_status_functions->run();
	$ew_after_review_functions = new EW_After_Review_Functions();
	$ew_after_review_functions->run();
}

run_easy_whatsapp_api();
