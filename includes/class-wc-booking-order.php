<?php
class WC_Booking_Order {
    public static function init() {
        add_action('woocommerce_checkout_order_processed', [__CLASS__, 'create_booking_from_order']);
        add_action('woocommerce_order_status_completed', [__CLASS__, 'complete_booking']);
        add_action('woocommerce_order_status_cancelled', [__CLASS__, 'cancel_booking']);
        add_action('woocommerce_order_status_refunded', [__CLASS__, 'cancel_booking']);
        add_action('woocommerce_order_item_meta_end', [__CLASS__, 'display_booking_details'], 10, 3);
    }

    public static function create_booking_from_order($order_id) {
        $order = wc_get_order($order_id);
        
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            
            if ('yes' !== get_post_meta($product->get_id(), '_wc_booking_enabled', true)) {
                continue;
            }
            
            $booking_date = $item->get_meta('Booking Date');
            $booking_time = $item->get_meta('Booking Time');
            $employee_id = $item->get_meta('_booking_employee_id');
            $service_id = $item->get_meta('_booking_service_id');
            $extras = $item->get_meta('_booking_extras');
            
            $booking_data = array(
                'post_title'   => sprintf(__('Booking &ndash; %s', 'wc-booking'), strftime(_x('%b %d, %Y @ %H:%M', 'Booking date parsed by strftime', 'wc-booking'))),
                'post_status'  => 'confirmed',
                'post_type'    => 'wc_booking',
                'post_author'  => $order->get_customer_id(),
            );
            
            $booking_id = wp_insert_post($booking_data);
            
            if ($booking_id) {
                update_post_meta($booking_id, '_booking_order_id', $order_id);
                update_post_meta($booking_id, '_booking_product_id', $product->get_id());
                update_post_meta($booking_id, '_booking_date', $booking_date);
                update_post_meta($booking_id, '_booking_time', $booking_time);
                update_post_meta($booking_id, '_booking_employee_id', $employee_id);
                update_post_meta($booking_id, '_booking_service_id', $service_id);
                update_post_meta($booking_id, '_booking_extras', $extras);
                update_post_meta($booking_id, '_booking_customer_id', $order->get_customer_id());
                update_post_meta($booking_id, '_booking_customer_name', $order->get_formatted_billing_full_name());
                update_post_meta($booking_id, '_booking_customer_email', $order->get_billing_email());
                update_post_meta($booking_id, '_booking_customer_phone', $order->get_billing_phone());
                
                // Link booking to order item
                wc_add_order_item_meta($item_id, '_booking_id', $booking_id);
            }
        }
    }

    public static function complete_booking($order_id) {
        $order = wc_get_order($order_id);
        
        foreach ($order->get_items() as $item_id => $item) {
            $booking_id = $item->get_meta('_booking_id');
            
            if ($booking_id) {
                $booking = new WC_Booking($booking_id);
                $booking->set_status('completed');
                $booking->save();
            }
        }
    }

    public static function cancel_booking($order_id) {
        $order = wc_get_order($order_id);
        
        foreach ($order->get_items() as $item_id => $item) {
            $booking_id = $item->get_meta('_booking_id');
            
            if ($booking_id) {
                $booking = new WC_Booking($booking_id);
                $booking->set_status('cancelled');
                $booking->save();
            }
        }
    }

    public static function display_booking_details($item_id, $item, $order) {
        $booking_id = $item->get_meta('_booking_id');
        
        if ($booking_id) {
            $booking = new WC_Booking($booking_id);
            include WC_BOOKING_PLUGIN_DIR . 'templates/order/booking-details.php';
        }
    }
}
