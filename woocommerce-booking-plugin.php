<?php
declare(strict_types=1);

/*
Plugin Name: WooCommerce Booking System
Plugin URI: https://example.com
Description: Advanced booking system for WooCommerce with employee selection, services and extra options.
Version: 1.0.2
Author: Your Name
Author URI: https://example.com
Text Domain: wc-booking
Domain Path: /languages
Requires at least: 5.6
Requires PHP: 8.0
WC requires at least: 3.4
WC tested up to: 3.4.8
*/

defined('ABSPATH') || exit;

// Define plugin constants
define('WC_BOOKING_PLUGIN_FILE', __FILE__);
define('WC_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_BOOKING_VERSION', '1.0.2');

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function(): void {
        echo '<div class="error"><p>';
        esc_html_e('WooCommerce Booking System requires WooCommerce to be installed and active!', 'wc-booking');
        echo '</p></div>';
    });
    return;
}

// Autoload classes
spl_autoload_register(function(string $class): void {
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
add_action('plugins_loaded', function(): void {
    // Load text domain
    load_plugin_textdomain('wc-booking', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        add_action('admin_notices', function(): void {
            echo '<div class="error"><p>';
            printf(
                esc_html__('WooCommerce Booking System requires PHP 8.0 or higher. Your current PHP version is %s. Please upgrade your PHP version.', 'wc-booking'),
                esc_html(PHP_VERSION)
            );
            echo '</p></div>';
        });
        return;
    }

    // Initialize components
    WC_Booking_Post_Types::init();
    WC_Booking_Admin::init();
    WC_Booking_Frontend::init();
    WC_Booking_Cart::init();
    WC_Booking_Order::init();
    WC_Booking_Employee::init();
    WC_Booking_Service::init();
    WC_Booking_Extra_Options::init();
    WC_Booking_Reports::init();
    
    // Add settings link
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), function(array $links): array {
        $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=booking')) . '">' . esc_html__('Settings', 'wc-booking') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    });
});

// Activation and deactivation hooks
register_activation_hook(__FILE__, function(): void {
    WC_Booking_Post_Types::register_post_types();
    WC_Booking_Post_Types::register_taxonomies();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function(): void {
    flush_rewrite_rules();
});

// Helper functions
function wc_booking_get_statuses(): array {
    return [
        'confirmed' => __('Confirmed', 'wc-booking'),
        'paid'      => __('Paid', 'wc-booking'),
        'completed' => __('Completed', 'wc-booking'),
        'cancelled' => __('Cancelled', 'wc-booking'),
        'pending'   => __('Pending', 'wc-booking'),
    ];
}

function wc_booking_get_employees_for_product(int $product_id): array {
    $employee_ids = get_post_meta($product_id, '_wc_booking_employees', true) ?: [];
    
    if (empty($employee_ids)) {
        return [];
    }
    
    return get_posts([
        'post_type' => 'wc_booking_employee',
        'post__in' => array_map('intval', $employee_ids),
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post_status' => 'publish'
    ]);
}

function wc_booking_get_services_for_product(int $product_id): array {
    $service_ids = get_post_meta($product_id, '_wc_booking_services', true) ?: [];
    
    if (empty($service_ids)) {
        return [];
    }
    
    return get_terms([
        'taxonomy' => 'wc_booking_service',
        'include' => array_map('intval', $service_ids),
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ]);
}

function wc_booking_get_extras_for_product(int $product_id): array {
    $extra_ids = get_post_meta($product_id, '_wc_booking_extras', true) ?: [];
    
    if (empty($extra_ids)) {
        return [];
    }
    
    return get_terms([
        'taxonomy' => 'wc_booking_extra',
        'include' => array_map('intval', $extra_ids),
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ]);
}
