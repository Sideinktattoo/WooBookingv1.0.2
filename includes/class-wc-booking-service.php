<?php
class WC_Booking_Service {
    public static function init() {
        add_action('wc_booking_service_add_form_fields', [__CLASS__, 'add_service_fields']);
        add_action('wc_booking_service_edit_form_fields', [__CLASS__, 'edit_service_fields'], 10, 2);
        add_action('created_wc_booking_service', [__CLASS__, 'save_service_fields']);
        add_action('edited_wc_booking_service', [__CLASS__, 'save_service_fields']);
        add_filter('manage_edit-wc_booking_service_columns', [__CLASS__, 'service_columns']);
        add_filter('manage_wc_booking_service_custom_column', [__CLASS__, 'service_column_content'], 10, 3);
    }

    public static function add_service_fields() {
        ?>
        <div class="form-field">
            <label for="service_duration"><?php _e('Duration (minutes)', 'wc-booking'); ?></label>
            <input type="number" name="service_duration" id="service_duration" value="60" min="1">
            <p class="description"><?php _e('Duration of this service in minutes', 'wc-booking'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="service_price"><?php _e('Additional Price', 'wc-booking'); ?></label>
            <input type="text" name="service_price" id="service_price" value="0">
            <p class="description"><?php _e('Additional price for this service', 'wc-booking'); ?></p>
        </div>
        <?php
    }

    public static function edit_service_fields($term, $taxonomy) {
        $duration = get_term_meta($term->term_id, 'service_duration', true) ?: 60;
        $price = get_term_meta($term->term_id, 'service_price', true) ?: 0;
        ?>
        <tr class="form-field">
            <th scope="row"><label for="service_duration"><?php _e('Duration (minutes)', 'wc-booking'); ?></label></th>
            <td>
                <input type="number" name="service_duration" id="service_duration" value="<?php echo esc_attr($duration); ?>" min="1">
                <p class="description"><?php _e('Duration of this service in minutes', 'wc-booking'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row"><label for="service_price"><?php _e('Additional Price', 'wc-booking'); ?></label></th>
            <td>
                <input type="text" name="service_price" id="service_price" value="<?php echo esc_attr($price); ?>">
                <p class="description"><?php _e('Additional price for this service', 'wc-booking'); ?></p>
            </td>
        </tr>
        <?php
    }

    public static function save_service_fields($term_id) {
        if (isset($_POST['service_duration'])) {
            update_term_meta($term_id, 'service_duration', absint($_POST['service_duration']));
        }
        
        if (isset($_POST['service_price'])) {
            update_term_meta($term_id, 'service_price', wc_format_decimal($_POST['service_price']));
        }
    }

    public static function service_columns($columns) {
        $columns['duration'] = __('Duration', 'wc-booking');
        $columns['price'] = __('Price', 'wc-booking');
        return $columns;
    }

    public static function service_column_content($content, $column_name, $term_id) {
        switch ($column_name) {
            case 'duration':
                $duration = get_term_meta($term_id, 'service_duration', true);
                $content = $duration ? sprintf(_n('%d minute', '%d minutes', $duration, 'wc-booking'), $duration) : '';
                break;
                
            case 'price':
                $price = get_term_meta($term_id, 'service_price', true);
                $content = $price ? wc_price($price) : '';
                break;
        }
        
        return $content;
    }
}
