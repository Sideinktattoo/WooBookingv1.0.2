<?php
declare(strict_types=1);

class WC_Booking {
    protected int $id;
    protected ?WP_Post $data;
    protected string $status;
    
    public function __construct(int $booking_id = 0) {
        $this->id = $booking_id;
        $this->data = get_post($booking_id);
        
        if ($this->data instanceof WP_Post) {
            $this->status = $this->data->post_status;
        } else {
            $this->status = '';
        }
    }
    
    public function get_id(): int {
        return $this->id;
    }
    
    public function get_status(): string {
        return $this->status;
    }
    
    public function set_status(string $new_status): bool {
        $valid_statuses = [
            'confirmed',
            'paid',
            'completed',
            'cancelled',
            'pending',
        ];
        
        if (!in_array($new_status, $valid_statuses, true)) {
            return false;
        }
        
        $this->status = $new_status;
        return true;
    }
    
    public function save(): void {
        if ($this->id > 0) {
            wp_update_post([
                'ID' => $this->id,
                'post_status' => $this->status,
            ]);
        }
    }
    
    public function get_product_id(): int {
        return (int) get_post_meta($this->id, '_booking_product_id', true);
    }
    
    public function get_order_id(): int {
        return (int) get_post_meta($this->id, '_booking_order_id', true);
    }
    
    public function get_date(): string {
        return (string) get_post_meta($this->id, '_booking_date', true);
    }
    
    public function get_time(): string {
        return (string) get_post_meta($this->id, '_booking_time', true);
    }
    
    public function get_employee_id(): int {
        return (int) get_post_meta($this->id, '_booking_employee_id', true);
    }
    
    public function get_service_id(): int {
        return (int) get_post_meta($this->id, '_booking_service_id', true);
    }
    
    public function get_extras(): array {
        $extras = get_post_meta($this->id, '_booking_extras', true);
        return is_array($extras) ? array_map('intval', $extras) : [];
    }
    
    public function get_customer_id(): int {
        return (int) get_post_meta($this->id, '_booking_customer_id', true);
    }
    
    public function get_customer_name(): string {
        return (string) get_post_meta($this->id, '_booking_customer_name', true);
    }
    
    public function get_customer_email(): string {
        return (string) get_post_meta($this->id, '_booking_customer_email', true);
    }
    
    public function get_customer_phone(): string {
        return (string) get_post_meta($this->id, '_booking_customer_phone', true);
    }
    
    public function get_duration(): int {
        $service_id = $this->get_service_id();
        $duration = $service_id > 0 ? (int) get_term_meta($service_id, 'service_duration', true) : 60;
        
        // Add duration from extras
        foreach ($this->get_extras() as $extra_id) {
            $extra_duration = (int) get_term_meta($extra_id, 'extra_duration', true);
            $duration += $extra_duration;
        }
        
        return max($duration, 60);
    }
    
    public function get_formatted_date(): string {
        $date = $this->get_date();
        $time = $this->get_time();
        
        if (empty($date) || empty($time)) {
            return '';
        }
        
        try {
            $datetime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
            return $datetime ? $datetime->format(wc_date_format() . ' ' . wc_time_format()) : '';
        } catch (Exception $e) {
            return '';
        }
    }
    
    public function get_employee_name(): string {
        $employee_id = $this->get_employee_id();
        $employee = $employee_id > 0 ? get_post($employee_id) : null;
        return $employee instanceof WP_Post ? $employee->post_title : '';
    }
    
    public function get_service_name(): string {
        $service_id = $this->get_service_id();
        $service = $service_id > 0 ? get_term($service_id, 'wc_booking_service') : null;
        return $service instanceof WP_Term ? $service->name : '';
    }
    
    public function get_extras_names(): array {
        $extras = $this->get_extras();
        $names = [];
        
        foreach ($extras as $extra_id) {
            $extra = get_term($extra_id, 'wc_booking_extra');
            if ($extra instanceof WP_Term) {
                $names[] = $extra->name;
            }
        }
        
        return $names;
    }
}
