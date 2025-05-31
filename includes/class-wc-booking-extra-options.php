<?php
class WC_Booking_Extra_Options {
    public static function init() {
        add_action('wc_booking_extra_add_form_fields', [__CLASS__, 'add_extra_fields']);
        add_action('wc_booking_extra_edit_form_fields', [__CLASS__, 'edit_extra_fields'], 10, 2);
        add_action('created_wc_booking_extra', [__CLASS__, 'save_extra_fields']);
        add_action('edited_wc_booking_extra', [__CLASS__, 'save_extra_fields']);
        add_filter('manage_edit-wc_booking_extra_columns', [__CLASS__, 'extra_columns']);
        add_filter('manage_wc_booking_extra_custom_column', [__CLASS__, 'extra_column_content'], 10, 3);
    }

    public static function add_extra_fields() {
        ?>
        <div class="form-field">
            <label for="extra_cost"><?php _e('Cost', 'wc-booking'); ?></label>
            <input type="text" name="extra_cost" id="extra_cost" value="0">
            <p class="description"><?php _e('Additional cost for this extra', 'wc-booking'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="extra_duration"><?php _e('Duration (minutes)', 'wc-booking'); ?></label>
            <input type="number" name="extra_duration" id="extra_duration" value="0" min="0">
            <p class="description"><?php _e('Additional duration in minutes (0 for no duration change)', 'wc-booking'); ?></p>
        </div>
        <?php
    }

    public static function edit_extra_fields($term, $taxonomy) {
        $cost = get_term_meta($term->term_id, 'extra_cost', true) ?: 0;
        $duration = get_term_meta($term->term_id, 'extra_duration', true) ?: 0;
        ?>
        <tr class="form-field">
            <th scope="row"><label for="extra_cost"><?php _e('Cost', 'wc-booking'); ?></label></th>
            <td>
                <input type="text" name="extra_cost" id="extra_cost" value="<?php echo esc_attr($cost); ?>">
                <p class="description"><?php _e('Additional cost for this extra', 'wc-booking'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row"><label for="extra_duration"><?php _e('Duration (minutes)', 'wc-booking'); ?></label></th>
            <td>
                <input type="number" name="extra_duration" id="extra_duration" value="<?php echo esc_attr($duration); ?>" min="0">
                <p class="description"><?php _e('Additional duration in minutes (0 for no duration change)', 'wc-booking'); ?></p>
            </td>
        </tr>
        <?php
    }

    public static function save_extra_fields($term_id) {
        if (isset($_POST['extra_cost'])) {
            update_term_meta($term_id, 'extra_cost', wc_format_decimal($_POST['extra_cost']));
        }
        
        if (isset($_POST['extra_duration'])) {
            update_term_meta($term_id, 'extra_duration', absint($_POST['extra_duration']));
        }
    }

    public static function extra_columns($columns) {
        $columns['cost'] = __('Cost', 'wc-booking');
        $columns['duration'] = __('Duration', 'wc-booking');
        return $columns;
    }

    public static function extra_column_content($content, $column_name, $term_id) {
        switch ($column_name) {
            case 'cost':
                $cost = get_term_meta($term_id, 'extra_cost', true);
                $content = $cost ? wc_price($cost) : '';
                break;
                
            case 'duration':
                $duration = get_term_meta($term_id, 'extra_duration', true);
                $content = $duration ? sprintf(_n('%d minute', '%d minutes', $duration, 'wc-booking'), $duration) : __('None', 'wc-booking');
                break;
        }
        
        return $content;
    }
}
