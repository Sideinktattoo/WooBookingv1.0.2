<?php
class WC_Booking_Reports {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_reports_page']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'admin_scripts']);
        add_action('wp_ajax_wc_booking_get_calendar_data', [__CLASS__, 'get_calendar_data']);
    }

    public static function admin_scripts($hook) {
        if ($hook !== 'woocommerce_page_wc-booking-reports') {
            return;
        }

        wp_enqueue_style('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css');
        wp_enqueue_style('wc-booking-reports', WC_BOOKING_PLUGIN_URL . 'assets/css/booking-reports.css', array(), WC_BOOKING_VERSION);
        
        wp_enqueue_script('moment', 'https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js', array(), null, true);
        wp_enqueue_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js', array('moment'), null, true);
        wp_enqueue_script('wc-booking-reports', WC_BOOKING_PLUGIN_URL . 'assets/js/booking-reports.js', array('jquery', 'fullcalendar'), WC_BOOKING_VERSION, true);
        
        wp_localize_script('wc-booking-reports', 'wc_booking_reports', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc-booking-reports'),
            'i18n' => array(
                'today' => __('Today', 'wc-booking'),
                'day' => __('Day', 'wc-booking'),
                'week' => __('Week', 'wc-booking'),
                'month' => __('Month', 'wc-booking'),
                'list' => __('List', 'wc-booking'),
                'total_bookings' => __('Total Bookings', 'wc-booking'),
                'total_revenue' => __('Total Revenue', 'wc-booking'),
                'no_bookings' => __('No bookings found', 'wc-booking'),
            )
        ));
    }

    public static function add_reports_page() {
        add_submenu_page(
            'woocommerce',
            __('Booking Reports', 'wc-booking'),
            __('Booking Reports', 'wc-booking'),
            'manage_woocommerce',
            'wc-booking-reports',
            [__CLASS__, 'render_reports_page']
        );
    }

    public static function render_reports_page() {
        include WC_BOOKING_PLUGIN_DIR . 'templates/admin/reports.php';
    }

    public static function get_calendar_data() {
        check_ajax_referer('wc-booking-reports', 'security');

        $start = sanitize_text_field($_GET['start']);
        $end = sanitize_text_field($_GET['end']);
        
        $args = array(
            'post_type' => 'wc_booking',
            'post_status' => array('confirmed', 'paid', 'completed'),
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_booking_date',
                    'value' => array($start, $end),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        );
        
        $bookings = get_posts($args);
        $events = array();
        $total_revenue = 0;
        $total_bookings = 0;
        
        foreach ($bookings as $booking) {
            $booking_obj = new WC_Booking($booking->ID);
            $order_id = $booking_obj->get_order_id();
            $order = wc_get_order($order_id);
            
            if ($order) {
                $total_revenue += $order->get_total();
            }
            
            $total_bookings++;
            
            $events[] = array(
                'title' => sprintf('#%d - %s', $booking->ID, $booking_obj->get_customer_name()),
                'start' => $booking_obj->get_date() . 'T' . $booking_obj->get_time(),
                'end' => $booking_obj->get_date() . 'T' . date('H:i', strtotime($booking_obj->get_time()) + $booking_obj->get_duration() * 60),
                'url' => get_edit_post_link($booking->ID),
                'backgroundColor' => self::get_status_color($booking_obj->get_status()),
                'borderColor' => self::get_status_color($booking_obj->get_status()),
                'extendedProps' => array(
                    'status' => $booking_obj->get_status(),
                    'employee' => $booking_obj->get_employee_name(),
                    'service' => $booking_obj->get_service_name(),
                    'revenue' => $order ? wc_price($order->get_total()) : '',
                )
            );
        }
        
        wp_send_json_success(array(
            'events' => $events,
            'stats' => array(
                'total_bookings' => $total_bookings,
                'total_revenue' => wc_price($total_revenue),
            )
        ));
    }

    private static function get_status_color($status) {
        $colors = array(
            'confirmed' => '#2ea2cc',
            'paid' => '#73a724',
            'completed' => '#21759b',
            'cancelled' => '#d54e21',
            'pending' => '#ffba00',
        );
        
        return $colors[$status] ?? '#2ea2cc';
    }
}
