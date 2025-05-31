<?php
class WC_Booking {
    protected $id;
    protected $data;
    protected $status;
    
    public function __construct($booking_id = 0) {
        $this->id = $booking_id;
        $this->data = get_post($booking_id);
        
        if ($this->data) {
            $this->status = $this->data->post_status;
        }
    }
    
    public function get_id() {
        return $this->id;
    }
    
    public function get_status() {
        return $this->status;
    }
    
    public function set_status($new_status) {
        $valid_statuses = array(
            'confirmed',
            'paid',
            'completed',
            'cancelled',
            'pending',
        );
        
        if (!in_array($new_status, $valid_statuses)) {
            return false;
        }
        
        $this->status = $new_status;
        return true;
    }
    
    public function save() {
        if ($this->id) {
            wp_update_post(array(
                'ID' => $this->id,
                'post_status' => $this->status,
            ));
        }
    }
    
    public function get_product_id() {
        return get_post_meta($this->id, '_booking_product_id', true);
    }
    
    public function get_order_id() {
        return get_post_meta($this->id, '_booking_order_id', true);
    }
    
    public function get_date() {
        return get_post_meta($this->id, '_booking_date', true);
    }
    
    public function get_time() {
        return get_post_meta($this->id, '_booking_time', true);
    }
    
    public function get_employee_id() {
        return get_post_meta($this->id, '_booking_employee_id', true);
    }
    
    public function get_service_id() {
        return get_post_meta($this->id, '_booking_service_id', true);
    }
    
    public function get_extras() {
        return get_post_meta($this->id, '_booking_extras', true) ?: array();
    }
    
    public function get_customer_id() {
        return get_post_meta($this->id, '_booking_customer_id', true);
    }
    
    public function get_customer_name() {
        return get_post_meta($this->id, '_booking_customer_name', true);
    }
    
    public function get_customer_email() {
        return get_post_meta($this->id, '_booking_customer_email', true);
    }
    
    public function get_customer_phone() {
        return get_post_meta($this->id, '_booking_customer_phone', true);
    }
    
    public function get_duration() {
        $service_id = $this->get_service_id();
        $duration = $service_id ? get_term_meta($service_id, 'service_duration', true) : 60;
        
        // Add duration from extras
        $extras = $this->get_extras();
        foreach ($extras as $extra_id) {
            $extra_duration = get_term_meta($extra_id, 'extra_duration', true);
            $duration += $extra_duration;
        }
        
        return $duration ?: 60;
    }
    
    public function get_formatted_date() {
        $date = $this->get_date();
        $time = $this->get_time();
        
        if (!$date || !$time) {
            return '';
        }
        
        $datetime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        return $datetime ? $datetime->format(wc_date_format() . ' ' . wc_time_format()) : '';
    }
    
    public function get_employee_name() {
        $employee_id = $this->get_employee_id();
        $employee = $employee_id ? get_post($employee_id) : null;
        return $employee ? $employee->post_title : '';
    }
    
    public function get_service_name() {
        $service_id = $this->get_service_id();
        $service = $service_id ? get_term($service_id, 'wc_booking_service') : null;
        return $service ? $service->name : '';
    }
    
    public function get_extras_names() {
        $extras = $this->get_extras();
        $names = array();
        
        foreach ($extras as $extra_id) {
            $extra = get_term($extra_id, 'wc_booking_extra');
            if ($extra) {
                $names[] = $extra->name;
            }
        }
        
        return $names;
    }
}
