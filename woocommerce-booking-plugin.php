<?php
/*
Plugin Name: WooCommerce Booking System
Plugin URI: https://example.com
Description: Advanced booking system for WooCommerce with employee selection, services and extra options.
Version: 1.0.1
Author: Your Name
Author URI: https://example.com
Text Domain: wc-booking
Domain Path: /languages
Requires at least: 5.6
Requires PHP: 7.2
WC requires at least: 3.4
WC tested up to: 3.4.8
*/

defined('ABSPATH') || exit;

// Define plugin constants
define('WC_BOOKING_PLUGIN_FILE', __FILE__);
define('WC_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_BOOKING_VERSION', '1.0.1');

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>';
        _e('WooCommerce Booking System requires WooCommerce to be installed and active!', 'wc-booking');
        echo '</p></div>';
    });
    return;
}

// Autoload classes
spl_autoload_register(function($class) {
    $prefix = 'WC_Booking_';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = WC_BOOKING_PLUGIN_DIR . 'includes/class-wc-booking-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
add_action('plugins_loaded', function() {
    // Load text domain
    load_plugin_textdomain('wc-booking', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize components
    WC_Booking_Post_Types::init();
    WC_Booking_Admin::init();
    WC_Booking_Frontend::init();
    WC_Booking_Cart::init();
    WC_Booking_Order::init();
    WC_Booking_Employee::init();
    WC_Booking_Service::init();
    WC_Booking_Extra_Options::init();
    
    // Add settings link
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=booking') . '">' . __('Settings', 'wc-booking') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    });
});

// Activation and deactivation hooks
register_activation_hook(__FILE__, ['WC_Booking_Post_Types', 'register_post_types']);
register_activation_hook(__FILE__, ['WC_Booking_Post_Types', 'register_taxonomies']);
register_activation_hook(__FILE__, 'wc_booking_flush_rewrite_rules');

function wc_booking_flush_rewrite_rules() {
    WC_Booking_Post_Types::register_post_types();
    WC_Booking_Post_Types::register_taxonomies();
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'wc_booking_deactivate');

function wc_booking_deactivate() {
    flush_rewrite_rules();
}

// Helper functions
function wc_booking_get_statuses() {
    return array(
        'confirmed' => __('Confirmed', 'wc-booking'),
        'paid'      => __('Paid', 'wc-booking'),
        'completed' => __('Completed', 'wc-booking'),
        'cancelled' => __('Cancelled', 'wc-booking'),
        'pending'   => __('Pending', 'wc-booking'),
    );
}

function wc_booking_get_employees_for_product($product_id) {
    $employee_ids = get_post_meta($product_id, '_wc_booking_employees', true);
    
    if (empty($employee_ids)) {
        return array();
    }
    
    return get_posts(array(
        'post_type' => 'wc_booking_employee',
        'post__in' => $employee_ids,
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
}

function wc_booking_get_services_for_product($product_id) {
    $service_ids = get_post_meta($product_id, '_wc_booking_services', true);
    
    if (empty($service_ids)) {
        return array();
    }
    
    return get_terms(array(
        'taxonomy' => 'wc_booking_service',
        'include' => $service_ids,
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ));
}

function wc_booking_get_extras_for_product($product_id) {
    $extra_ids = get_post_meta($product_id, '_wc_booking_extras', true);
    
    if (empty($extra_ids)) {
        return array();
    }
    
    return get_terms(array(
        'taxonomy' => 'wc_booking_extra',
        'include' => $extra_ids,
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ));
}
