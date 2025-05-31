<?php wp_nonce_field('wc_booking_details', 'wc_booking_details_nonce'); ?>

<table class="wc-booking-details">
    <tr>
        <th><?php _e('Status', 'wc-booking'); ?></th>
        <td>
            <select id="_booking_status" name="_booking_status">
                <?php foreach (wc_booking_get_statuses() as $status => $label) : ?>
                    <option value="<?php echo esc_attr($status); ?>" <?php selected($status, $booking->get_status()); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th><?php _e('Customer', 'wc-booking'); ?></th>
        <td>
            <?php echo esc_html($booking->get_customer_name()); ?><br>
            <?php echo esc_html($booking->get_customer_email()); ?><br>
            <?php echo esc_html($booking->get_customer_phone()); ?>
        </td>
    </tr>
    <tr>
        <th><?php _e('Date & Time', 'wc-booking'); ?></th>
        <td><?php echo esc_html($booking->get_formatted_date()); ?></td>
    </tr>
    <tr>
        <th><?php _e('Duration', 'wc-booking'); ?></th>
        <td><?php echo esc_html(sprintf(_n('%d minute', '%d minutes', $booking->get_duration(), 'wc-booking'), $booking->get_duration())); ?></td>
    </tr>
    <tr>
        <th><?php _e('Employee', 'wc-booking'); ?></th>
        <td><?php echo esc_html($booking->get_employee_name()); ?></td>
    </tr>
    <tr>
        <th><?php _e('Service', 'wc-booking'); ?></th>
        <td><?php echo esc_html($booking->get_service_name()); ?></td>
    </tr>
    <tr>
        <th><?php _e('Extras', 'wc-booking'); ?></th>
        <td><?php echo esc_html(implode(', ', $booking->get_extras_names())); ?></td>
    </tr>
    <tr>
        <th><?php _e('Product', 'wc-booking'); ?></th>
        <td><?php echo get_the_title($booking->get_product_id()); ?></td>
    </tr>
    <tr>
        <th><?php _e('Order', 'wc-booking'); ?></th>
        <td>
            <?php if ($booking->get_order_id()) : ?>
                <a href="<?php echo get_edit_post_link($booking->get_order_id()); ?>">
                    <?php printf(__('Order #%s', 'wc-booking'), $booking->get_order_id()); ?>
                </a>
            <?php else : ?>
                <?php _e('N/A', 'wc-booking'); ?>
            <?php endif; ?>
        </td>
    </tr>
</table>
