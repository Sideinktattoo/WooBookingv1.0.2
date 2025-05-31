<?php
class WC_Booking_Frontend {
    public static function init() {
        add_action('wp_enqueue_scripts', [__CLASS__, 'frontend_scripts']);
        add_filter('woocommerce_locate_template', [__CLASS__, 'locate_template'], 10, 3);
        add_action('woocommerce_before_add_to_cart_button', [__CLASS__, 'booking_form']);
        add_filter('woocommerce_add_to_cart_validation', [__CLASS__, 'validate_booking'], 10, 3);
        add_filter('woocommerce_add_cart_item_data', [__CLASS__, 'add_cart_item_data'], 10, 3);
        add_filter('woocommerce_get_item_data', [__CLASS__, 'display_booking_data_in_cart'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [__CLASS__, 'add_booking_data_to_order'], 10, 4);
        add_shortcode('booking_calendar', [__CLASS__, 'booking_calendar_shortcode']);
    }

    public static function frontend_scripts() {
        wp_enqueue_style('wc-booking-frontend', WC_BOOKING_PLUGIN_URL . 'assets/css/booking-styles.css', array(), WC_BOOKING_VERSION);
        wp_enqueue_script('wc-booking-frontend', WC_BOOKING_PLUGIN_URL . 'assets/js/booking-frontend.js', array('jquery', 'jquery-ui-datepicker', 'select2'), WC_BOOKING_VERSION, true);
        
        wp_localize_script('wc-booking-frontend', 'wc_booking_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc-booking-nonce'),
            'date_format' => wc_date_format(),
            'time_format' => wc_time_format(),
            'i18n' => array(
                'invalid_date' => __('Please select a valid date.', 'wc-booking'),
                'invalid_time' => __('Please select a valid time.', 'wc-booking'),
                'invalid_employee' => __('Please select an employee.', 'wc-booking'),
                'invalid_service' => __('Please select a service.', 'wc-booking'),
                'loading' => __('Loading...', 'wc-booking'),
                'select_time' => __('Select a time', 'wc-booking'),
            )
        ));
    }

    public static function locate_template($template, $template_name, $template_path) {
        $plugin_path = WC_BOOKING_PLUGIN_DIR . 'templates/';
        
        if (file_exists($plugin_path . $template_name)) {
            return $plugin_path . $template_name;
        }
        
        return $template;
    }

    public static function booking_form() {
        global $product;
        
        if ('yes' !== get_post_meta($product->get_id(), '_wc_booking_enabled', true)) {
            return;
        }
        
        $employees = wc_booking_get_employees_for_product($product->get_id());
        $services = wc_booking_get_services_for_product($product->get_id());
        $extras = wc_booking_get_extras_for_product($product->get_id());
        
        include WC_BOOKING_PLUGIN_DIR . 'templates/booking-form.php';
    }

    public static function validate_booking($passed, $product_id, $quantity) {
        if ('yes' !== get_post_meta($product_id, '_wc_booking_enabled', true)) {
            return $passed;
        }
        
        if (!isset($_POST['booking_date']) || empty($_POST['booking_date'])) {
            wc_add_notice(__('Please select a booking date.', 'wc-booking'), 'error');
            return false;
        }
        
        if (!isset($_POST['booking_time']) || empty($_POST['booking_time'])) {
            wc_add_notice(__('Please select a booking time.', 'wc-booking'), 'error');
            return false;
        }
        
        if (!isset($_POST['booking_employee']) || empty($_POST['booking_employee'])) {
            wc_add_notice(__('Please select an employee.', 'wc-booking'), 'error');
            return false;
        }
        
        if (!isset($_POST['booking_service']) || empty($_POST['booking_service'])) {
            wc_add_notice(__('Please select a service.', 'wc-booking'), 'error');
            return false;
        }
        
        // Check availability
        $date = sanitize_text_field($_POST['booking_date']);
        $time = sanitize_text_field($_POST['booking_time']);
        $employee_id = absint($_POST['booking_employee']);
        
        if (!self::is_slot_available($product_id, $date, $time, $employee_id)) {
            wc_add_notice(__('The selected time slot is not available. Please choose another time.', 'wc-booking'), 'error');
            return false;
        }
        
        return $passed;
    }

    public static function is_slot_available($product_id, $date, $time, $employee_id) {
        // Check if the employee is available at this time
        $args = array(
            'post_type' => 'wc_booking',
            'post_status' => array('confirmed', 'paid', 'completed'),
            'meta_query' => array(
                array(
                    'key' => '_booking_product_id',
                    'value' => $product_id,
                ),
                array(
                    'key' => '_booking_date',
                    'value' => $date,
                ),
                array(
                    'key' => '_booking_time',
                    'value' => $time,
                ),
                array(
                    'key' => '_booking_employee_id',
                    'value' => $employee_id,
                ),
            ),
        );
        
        $existing_bookings = get_posts($args);
        
        return empty($existing_bookings);
    }

    public static function add_cart_item_data($cart_item_data, $product_id, $variation_id) {
        if ('yes' !== get_post_meta($product_id, '_wc_booking_enabled', true)) {
            return $cart_item_data;
        }
        
        if (isset($_POST['booking_date'])) {
            $cart_item_data['booking_date'] = sanitize_text_field($_POST['booking_date']);
        }
        
        if (isset($_POST['booking_time'])) {
            $cart_item_data['booking_time'] = sanitize_text_field($_POST['booking_time']);
        }
        
        if (isset($_POST['booking_employee'])) {
            $cart_item_data['booking_employee'] = absint($_POST['booking_employee']);
            $employee = get_post($cart_item_data['booking_employee']);
            $cart_item_data['booking_employee_name'] = $employee ? $employee->post_title : '';
        }
        
        if (isset($_POST['booking_service'])) {
            $cart_item_data['booking_service'] = absint($_POST['booking_service']);
            $service = get_term($cart_item_data['booking_service'], 'wc_booking_service');
            $cart_item_data['booking_service_name'] = $service ? $service->name : '';
        }
        
        if (isset($_POST['booking_extras']) && is_array($_POST['booking_extras'])) {
            $cart_item_data['booking_extras'] = array_map('absint', $_POST['booking_extras']);
            
            $extra_names = array();
            foreach ($cart_item_data['booking_extras'] as $extra_id) {
                $extra = get_term($extra_id, 'wc_booking_extra');
                if ($extra) {
                    $extra_names[] = $extra->name;
                }
            }
            $cart_item_data['booking_extras_names'] = $extra_names;
        }
        
        return $cart_item_data;
    }

    public static function display_booking_data_in_cart($item_data, $cart_item) {
        if (isset($cart_item['booking_date'])) {
            $item_data[] = array(
                'key' => __('Booking Date', 'wc-booking'),
                'value' => wc_format_datetime(new DateTime($cart_item['booking_date'])),
            );
        }
        
        if (isset($cart_item['booking_time'])) {
            $item_data[] = array(
                'key' => __('Booking Time', 'wc-booking'),
                'value' => $cart_item['booking_time'],
            );
        }
        
        if (isset($cart_item['booking_employee_name'])) {
            $item_data[] = array(
                'key' => __('Employee', 'wc-booking'),
                'value' => $cart_item['booking_employee_name'],
            );
        }
        
        if (isset($cart_item['booking_service_name'])) {
            $item_data[] = array(
                'key' => __('Service', 'wc-booking'),
                'value' => $cart_item['booking_service_name'],
            );
        }
        
        if (isset($cart_item['booking_extras_names']) && !empty($cart_item['booking_extras_names'])) {
            $item_data[] = array(
                'key' => __('Extras', 'wc-booking'),
                'value' => implode(', ', $cart_item['booking_extras_names']),
            );
        }
        
        return $item_data;
    }

    public static function add_booking_data_to_order($item, $cart_item_key, $values, $order) {
        if (isset($values['booking_date'])) {
            $item->add_meta_data(__('Booking Date', 'wc-booking'), $values['booking_date']);
        }
        
        if (isset($values['booking_time'])) {
            $item->add_meta_data(__('Booking Time', 'wc-booking'), $values['booking_time']);
        }
        
        if (isset($values['booking_employee_name'])) {
            $item->add_meta_data(__('Employee', 'wc-booking'), $values['booking_employee_name']);
            $item->add_meta_data('_booking_employee_id', $values['booking_employee']);
        }
        
        if (isset($values['booking_service_name'])) {
            $item->add_meta_data(__('Service', 'wc-booking'), $values['booking_service_name']);
            $item->add_meta_data('_booking_service_id', $values['booking_service']);
        }
        
        if (isset($values['booking_extras_names']) && !empty($values['booking_extras_names'])) {
            $item->add_meta_data(__('Extras', 'wc-booking'), implode(', ', $values['booking_extras_names']));
            $item->add_meta_data('_booking_extras', $values['booking_extras']);
        }
    }

    public static function booking_calendar_shortcode($atts) {
        $atts = shortcode_atts(array(
            'product_id' => 0,
        ), $atts, 'booking_calendar');
        
        if (!$atts['product_id']) {
            global $product;
            $atts['product_id'] = $product ? $product->get_id() : 0;
        }
        
        if (!$atts['product_id'] || 'yes' !== get_post_meta($atts['product_id'], '_wc_booking_enabled', true)) {
            return '';
        }
        
        ob_start();
        include WC_BOOKING_PLUGIN_DIR . 'templates/booking-calendar.php';
        return ob_get_clean();
    }
}
