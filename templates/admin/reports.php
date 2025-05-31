<div class="wrap woocommerce">
    <h1><?php _e('Booking Reports', 'wc-booking'); ?></h1>
    
    <div class="wc-booking-reports-header">
        <div class="wc-booking-stats">
            <div class="stat-box">
                <h3><?php _e('Total Bookings', 'wc-booking'); ?></h3>
                <span id="wc-booking-total-bookings">0</span>
            </div>
            <div class="stat-box">
                <h3><?php _e('Total Revenue', 'wc-booking'); ?></h3>
                <span id="wc-booking-total-revenue">$0.00</span>
            </div>
        </div>
        
        <div class="wc-booking-filters">
            <select id="wc-booking-filter-employee">
                <option value=""><?php _e('All Employees', 'wc-booking'); ?></option>
                <?php
                $employees = get_posts(array(
                    'post_type' => 'wc_booking_employee',
                    'posts_per_page' => -1
                ));
                
                foreach ($employees as $employee) {
                    echo '<option value="' . esc_attr($employee->ID) . '">' . esc_html($employee->post_title) . '</option>';
                }
                ?>
            </select>
            
            <select id="wc-booking-filter-service">
                <option value=""><?php _e('All Services', 'wc-booking'); ?></option>
                <?php
                $services = get_terms(array(
                    'taxonomy' => 'wc_booking_service',
                    'hide_empty' => false
                ));
                
                foreach ($services as $service) {
                    echo '<option value="' . esc_attr($service->term_id) . '">' . esc_html($service->name) . '</option>';
                }
                ?>
            </select>
            
            <select id="wc-booking-filter-status">
                <option value=""><?php _e('All Statuses', 'wc-booking'); ?></option>
                <?php
                $statuses = wc_booking_get_statuses();
                
                foreach ($statuses as $status => $label) {
                    echo '<option value="' . esc_attr($status) . '">' . esc_html($label) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
    
    <div id="wc-booking-calendar"></div>
    
    <div class="wc-booking-details-panel">
        <h3><?php _e('Booking Details', 'wc-booking'); ?></h3>
        <div id="wc-booking-details-content">
            <p><?php _e('Select a booking to view details', 'wc-booking'); ?></p>
        </div>
    </div>
</div>
