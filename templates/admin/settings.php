<div class="wrap woocommerce">
    <h1><?php _e('Booking Settings', 'wc-booking'); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('wc_booking_settings'); ?>
        <?php do_settings_sections('wc-booking-settings'); ?>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Booking Duration Unit', 'wc-booking'); ?></th>
                <td>
                    <select name="wc_booking_duration_unit">
                        <option value="minutes" <?php selected(get_option('wc_booking_duration_unit'), 'minutes'); ?>><?php _e('Minutes', 'wc-booking'); ?></option>
                        <option value="hours" <?php selected(get_option('wc_booking_duration_unit'), 'hours'); ?>><?php _e('Hours', 'wc-booking'); ?></option>
                    </select>
                    <p class="description"><?php _e('The unit used for booking durations', 'wc-booking'); ?></p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('Minimum Booking Time', 'wc-booking'); ?></th>
                <td>
                    <input type="number" name="wc_booking_min_time" value="<?php echo esc_attr(get_option('wc_booking_min_time', 1)); ?>" min="1">
                    <p class="description"><?php _e('Minimum time in advance a booking can be made (in hours)', 'wc-booking'); ?></p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('Maximum Booking Time', 'wc-booking'); ?></th>
                <td>
                    <input type="number" name="wc_booking_max_time" value="<?php echo esc_attr(get_option('wc_booking_max_time', 24)); ?>" min="1">
                    <p class="description"><?php _e('Maximum time in advance a booking can be made (in days)', 'wc-booking'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
