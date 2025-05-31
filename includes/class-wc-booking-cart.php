<?php
class WC_Booking_Cart {
    public static function init() {
        add_filter('woocommerce_get_cart_item_from_session', [__CLASS__, 'get_cart_item_from_session'], 10, 2);
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'calculate_totals']);
    }

    public static function get_cart_item_from_session($cart_item, $values) {
        if (isset($values['booking_date'])) {
            $cart_item['booking_date'] = $values['booking_date'];
        }
        
        if (isset($values['booking_time'])) {
            $cart_item['booking_time'] = $values['booking_time'];
        }
        
        if (isset($values['booking_employee'])) {
            $cart_item['booking_employee'] = $values['booking_employee'];
            $cart_item['booking_employee_name'] = $values['booking_employee_name'];
        }
        
        if (isset($values['booking_service'])) {
            $cart_item['booking_service'] = $values['booking_service'];
            $cart_item['booking_service_name'] = $values['booking_service_name'];
        }
        
        if (isset($values['booking_extras'])) {
            $cart_item['booking_extras'] = $values['booking_extras'];
            $cart_item['booking_extras_names'] = $values['booking_extras_names'];
        }
        
        return $cart_item;
    }

    public static function calculate_totals($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['booking_extras']) && !empty($cart_item['booking_extras'])) {
                $extra_cost = 0;
                
                foreach ($cart_item['booking_extras'] as $extra_id) {
                    $cost = get_term_meta($extra_id, '_wc_booking_extra_cost', true);
                    $extra_cost += floatval($cost);
                }
                
                $cart_item['data']->set_price($cart_item['data']->get_price() + $extra_cost);
            }
        }
    }
}
