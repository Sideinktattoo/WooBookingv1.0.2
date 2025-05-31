<?php
class WC_Booking_Admin {
    public static function init() {
        add_action('admin_enqueue_scripts', [__CLASS__, 'admin_scripts']);
        add_action('admin_menu', [__CLASS__, 'add_menu_items']);
        add_filter('woocommerce_product_data_tabs', [__CLASS__, 'add_product_tab']);
        add_action('woocommerce_product_data_panels', [__CLASS__, 'add_product_tab_content']);
        add_action('woocommerce_process_product_meta', [__CLASS__, 'save_product_data']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_meta_boxes'], 10, 2);
    }

    public static function admin_scripts() {
        wp_enqueue_style('wc-booking-admin', WC_BOOKING_PLUGIN_URL . 'assets/css/booking-styles.css', array(), WC_BOOKING_VERSION);
        wp_enqueue_script('wc-booking-admin', WC_BOOKING_PLUGIN_URL . 'assets/js/booking-admin.js', array('jquery', 'jquery-ui-datepicker'), WC_BOOKING_VERSION, true);
        
        wp_localize_script('wc-booking-admin', 'wc_booking_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc-booking-nonce'),
            'i18n' => array(
                'select_employee' => __('Select Employee', 'wc-booking'),
                'select_service' => __('Select Service', 'wc-booking'),
            )
        ));
    }

    public static function add_menu_items() {
        add_submenu_page(
            'woocommerce',
            __('Booking Settings', 'wc-booking'),
            __('Booking Settings', 'wc-booking'),
            'manage_woocommerce',
            'wc-booking-settings',
            [__CLASS__, 'settings_page']
        );
    }

    public static function settings_page() {
        include WC_BOOKING_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    public static function add_product_tab($tabs) {
        $tabs['booking'] = array(
            'label'    => __('Booking', 'wc-booking'),
            'target'   => 'booking_product_data',
            'class'    => array('show_if_booking'),
            'priority' => 21,
        );
        return $tabs;
    }

    public static function add_product_tab_content() {
        global $post;
        
        echo '<div id="booking_product_data" class="panel woocommerce_options_panel hidden">';
        
        // Booking enabled
        woocommerce_wp_checkbox(array(
            'id'          => '_wc_booking_enabled',
            'label'       => __('Enable booking', 'wc-booking'),
            'description' => __('Enable this to allow bookings for this product.', 'wc-booking'),
            'value'       => get_post_meta($post->ID, '_wc_booking_enabled', true),
        ));
        
        // Duration
        woocommerce_wp_text_input(array(
            'id'          => '_wc_booking_duration',
            'label'       => __('Duration (minutes)', 'wc-booking'),
            'description' => __('Duration of each booking slot in minutes.', 'wc-booking'),
            'value'       => get_post_meta($post->ID, '_wc_booking_duration', true) ?: 60,
            'type'        => 'number',
            'desc_tip'    => true,
        ));
        
        // Available services
        $services = get_terms(array(
            'taxonomy' => 'wc_booking_service',
            'hide_empty' => false,
        ));
        
        $selected_services = get_post_meta($post->ID, '_wc_booking_services', true) ?: array();
        
        echo '<p class="form-field"><label>' . __('Available Services', 'wc-booking') . '</label>';
        echo '<select multiple="multiple" name="_wc_booking_services[]" class="wc-enhanced-select" style="width: 50%;">';
        foreach ($services as $service) {
            echo '<option value="' . esc_attr($service->term_id) . '" ' . selected(in_array($service->term_id, $selected_services), true, false) . '>' . esc_html($service->name) . '</option>';
        }
        echo '</select>';
        echo '</p>';
        
        // Available employees
        $employees = get_posts(array(
            'post_type' => 'wc_booking_employee',
            'numberposts' => -1,
            'post_status' => 'publish',
        ));
        
        $selected_employees = get_post_meta($post->ID, '_wc_booking_employees', true) ?: array();
        
        echo '<p class="form-field"><label>' . __('Available Employees', 'wc-booking') . '</label>';
        echo '<select multiple="multiple" name="_wc_booking_employees[]" class="wc-enhanced-select" style="width: 50%;">';
        foreach ($employees as $employee) {
            echo '<option value="' . esc_attr($employee->ID) . '" ' . selected(in_array($employee->ID, $selected_employees), true, false) . '>' . esc_html($employee->post_title) . '</option>';
        }
        echo '</select>';
        echo '</p>';
        
        // Extra options
        $extras = get_terms(array(
            'taxonomy' => 'wc_booking_extra',
            'hide_empty' => false,
        ));
        
        $selected_extras = get_post_meta($post->ID, '_wc_booking_extras', true) ?: array();
        
        echo '<p class="form-field"><label>' . __('Available Extras', 'wc-booking') . '</label>';
        echo '<select multiple="multiple" name="_wc_booking_extras[]" class="wc-enhanced-select" style="width: 50%;">';
        foreach ($extras as $extra) {
            echo '<option value="' . esc_attr($extra->term_id) . '" ' . selected(in_array($extra->term_id, $selected_extras), true, false) . '>' . esc_html($extra->name) . '</option>';
        }
        echo '</select>';
        echo '</p>';
        
        echo '</div>';
    }

    public static function save_product_data($post_id) {
        $booking_enabled = isset($_POST['_wc_booking_enabled']) ? 'yes' : 'no';
        update_post_meta($post_id, '_wc_booking_enabled', $booking_enabled);
        
        if (isset($_POST['_wc_booking_duration'])) {
            update_post_meta($post_id, '_wc_booking_duration', absint($_POST['_wc_booking_duration']));
        }
        
        if (isset($_POST['_wc_booking_services'])) {
            update_post_meta($post_id, '_wc_booking_services', array_map('absint', (array) $_POST['_wc_booking_services']));
        } else {
            update_post_meta($post_id, '_wc_booking_services', array());
        }
        
        if (isset($_POST['_wc_booking_employees'])) {
            update_post_meta($post_id, '_wc_booking_employees', array_map('absint', (array) $_POST['_wc_booking_employees']));
        } else {
            update_post_meta($post_id, '_wc_booking_employees', array());
        }
        
        if (isset($_POST['_wc_booking_extras'])) {
            update_post_meta($post_id, '_wc_booking_extras', array_map('absint', (array) $_POST['_wc_booking_extras']));
        } else {
            update_post_meta($post_id, '_wc_booking_extras', array());
        }
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'wc-booking-details',
            __('Booking Details', 'wc-booking'),
            [__CLASS__, 'booking_details_meta_box'],
            'wc_booking',
            'normal',
            'high'
        );
    }

    public static function booking_details_meta_box($post) {
        $booking = new WC_Booking($post->ID);
        include WC_BOOKING_PLUGIN_DIR . 'templates/admin/booking-details.php';
    }

    public static function save_meta_boxes($post_id, $post) {
        if ($post->post_type !== 'wc_booking' || !isset($_POST['wc_booking_details_nonce']) || !wp_verify_nonce($_POST['wc_booking_details_nonce'], 'wc_booking_details')) {
            return;
        }
        
        if (isset($_POST['_booking_status'])) {
            $booking = new WC_Booking($post_id);
            $booking->set_status($_POST['_booking_status']);
            $booking->save();
        }
    }
}
