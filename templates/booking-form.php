<div class="wc-booking-form">
    <div class="form-row">
        <label for="wc-booking-date"><?php _e('Booking Date', 'wc-booking'); ?></label>
        <input type="text" id="wc-booking-date" class="wc-booking-datepicker" readonly>
        <input type="hidden" name="booking_date" value="">
    </div>
    
    <div class="form-row">
        <label for="wc-booking-employee"><?php _e('Employee', 'wc-booking'); ?></label>
        <select name="booking_employee" id="wc-booking-employee" class="wc-enhanced-select">
            <option value=""><?php _e('Select an employee', 'wc-booking'); ?></option>
            <?php foreach ($employees as $employee) : ?>
                <option value="<?php echo esc_attr($employee->ID); ?>"><?php echo esc_html($employee->post_title); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-row">
        <label for="wc-booking-service"><?php _e('Service', 'wc-booking'); ?></label>
        <select name="booking_service" id="wc-booking-service" class="wc-enhanced-select">
            <option value=""><?php _e('Select a service', 'wc-booking'); ?></option>
            <?php foreach ($services as $service) : ?>
                <option value="<?php echo esc_attr($service->term_id); ?>"><?php echo esc_html($service->name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-row">
        <label for="wc-booking-time"><?php _e('Time Slot', 'wc-booking'); ?></label>
        <select name="booking_time_select" id="wc-booking-time" class="wc-booking-time-slots wc-enhanced-select">
            <option value=""><?php _e('Select a date and employee first', 'wc-booking'); ?></option>
        </select>
        <input type="hidden" name="booking_time" value="">
    </div>
    
    <?php if (!empty($extras)) : ?>
    <div class="form-row booking-extras">
        <label><?php _e('Extra Options', 'wc-booking'); ?></label>
        <?php foreach ($extras as $extra) : ?>
            <label>
                <input type="checkbox" name="booking_extras_toggle[]" value="<?php echo esc_attr($extra->term_id); ?>" class="wc-booking-extra">
                <?php echo esc_html($extra->name); ?> (+<?php echo wc_price(get_term_meta($extra->term_id, 'extra_cost', true)); ?>)
                <input type="hidden" name="booking_extras[]" value="<?php echo esc_attr($extra->term_id); ?>" disabled>
            </label>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
