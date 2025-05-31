<?php
class WC_Booking_Employee {
    public static function init() {
        add_action('save_post_wc_booking_employee', [__CLASS__, 'save_employee_meta'], 10, 3);
        add_filter('manage_wc_booking_employee_posts_columns', [__CLASS__, 'employee_columns']);
        add_action('manage_wc_booking_employee_posts_custom_column', [__CLASS__, 'employee_column_content'], 10, 2);
        add_action('add_meta_boxes_wc_booking_employee', [__CLASS__, 'add_meta_boxes']);
    }

    public static function save_employee_meta($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (isset($_POST['employee_email'])) {
            update_post_meta($post_id, '_employee_email', sanitize_email($_POST['employee_email']));
        }
        
        if (isset($_POST['employee_phone'])) {
            update_post_meta($post_id, '_employee_phone', sanitize_text_field($_POST['employee_phone']));
        }
        
        if (isset($_POST['employee_specialization'])) {
            update_post_meta($post_id, '_employee_specialization', sanitize_text_field($_POST['employee_specialization']));
        }
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'wc-booking-employee-details',
            __('Employee Details', 'wc-booking'),
            [__CLASS__, 'employee_details_meta_box'],
            'wc_booking_employee',
            'normal',
            'high'
        );
    }

    public static function employee_details_meta_box($post) {
        wp_nonce_field('wc_booking_save_employee_data', 'wc_booking_employee_nonce');
        
        $email = get_post_meta($post->ID, '_employee_email', true);
        $phone = get_post_meta($post->ID, '_employee_phone', true);
        $specialization = get_post_meta($post->ID, '_employee_specialization', true);
        ?>
        <div class="wc-booking-employee-fields">
            <div class="form-field">
                <label for="employee_email"><?php _e('Email', 'wc-booking'); ?></label>
                <input type="email" name="employee_email" id="employee_email" value="<?php echo esc_attr($email); ?>">
            </div>
            
            <div class="form-field">
                <label for="employee_phone"><?php _e('Phone', 'wc-booking'); ?></label>
                <input type="text" name="employee_phone" id="employee_phone" value="<?php echo esc_attr($phone); ?>">
            </div>
            
            <div class="form-field">
                <label for="employee_specialization"><?php _e('Specialization', 'wc-booking'); ?></label>
                <input type="text" name="employee_specialization" id="employee_specialization" value="<?php echo esc_attr($specialization); ?>">
            </div>
        </div>
        <?php
    }

    public static function employee_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'title') {
                $new_columns['email'] = __('Email', 'wc-booking');
                $new_columns['phone'] = __('Phone', 'wc-booking');
                $new_columns['specialization'] = __('Specialization', 'wc-booking');
            }
        }
        
        return $new_columns;
    }

    public static function employee_column_content($column, $post_id) {
        switch ($column) {
            case 'email':
                echo esc_html(get_post_meta($post_id, '_employee_email', true));
                break;
                
            case 'phone':
                echo esc_html(get_post_meta($post_id, '_employee_phone', true));
                break;
                
            case 'specialization':
                echo esc_html(get_post_meta($post_id, '_employee_specialization', true));
                break;
        }
    }
}
