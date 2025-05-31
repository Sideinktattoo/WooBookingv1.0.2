jQuery(function($) {
    'use strict';

    var WCBooking = {
        init: function() {
            this.datepicker();
            this.timeSlots();
            this.employeeServices();
            this.extrasToggle();
        },

        datepicker: function() {
            $('.wc-booking-datepicker').datepicker({
                dateFormat: wc_booking_params.date_format,
                minDate: 0,
                beforeShowDay: function(date) {
                    var day = date.getDay();
                    var dateString = $.datepicker.formatDate('yy-mm-dd', date);
                    var isAvailable = WCBooking.isDateAvailable(dateString);
                    
                    return [isAvailable, isAvailable ? 'bookable' : 'not-bookable'];
                },
                onSelect: function(dateText) {
                    var date = $(this).datepicker('getDate');
                    var dateString = $.datepicker.formatDate('yy-mm-dd', date);
                    
                    $('input[name="booking_date"]').val(dateString);
                    WCBooking.loadTimeSlots(dateString);
                }
            });
        },

        isDateAvailable: function(date) {
            // In a real implementation, you would check availability via AJAX
            // This is a simplified version
            var day = new Date(date).getDay();
            return day !== 0 && day !== 6; // Not Sunday or Saturday
        },

        timeSlots: function() {
            $(document).on('change', 'select.wc-booking-time', function() {
                $('input[name="booking_time"]').val($(this).val());
            });
        },

        loadTimeSlots: function(date) {
            var product_id = $('input[name="add-to-cart"]').val();
            var employee_id = $('select[name="booking_employee"]').val();
            
            if (!employee_id) {
                return;
            }
            
            $.ajax({
                url: wc_booking_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'wc_booking_get_time_slots',
                    date: date,
                    product_id: product_id,
                    employee_id: employee_id,
                    security: wc_booking_params.nonce
                },
                beforeSend: function() {
                    $('.wc-booking-time-slots').html('<option value="">' + wc_booking_params.i18n.loading + '</option>');
                },
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">' + wc_booking_params.i18n.select_time + '</option>';
                        
                        $.each(response.data.slots, function(i, slot) {
                            options += '<option value="' + slot.time + '">' + slot.time + '</option>';
                        });
                        
                        $('.wc-booking-time-slots').html(options);
                    } else {
                        $('.wc-booking-time-slots').html('<option value="">' + response.data.message + '</option>');
                    }
                }
            });
        },

        employeeServices: function() {
            $(document).on('change', 'select[name="booking_employee"]', function() {
                var employee_id = $(this).val();
                var product_id = $('input[name="add-to-cart"]').val();
                
                if (!employee_id) {
                    return;
                }
                
                $.ajax({
                    url: wc_booking_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wc_booking_get_employee_services',
                        employee_id: employee_id,
                        product_id: product_id,
                        security: wc_booking_params.nonce
                    },
                    beforeSend: function() {
                        $('select[name="booking_service"]').html('<option value="">' + wc_booking_params.i18n.loading + '</option>');
                    },
                    success: function(response) {
                        if (response.success) {
                            var options = '<option value="">' + wc_booking_params.i18n.select_service + '</option>';
                            
                            $.each(response.data.services, function(i, service) {
                                options += '<option value="' + service.id + '">' + service.name + '</option>';
                            });
                            
                            $('select[name="booking_service"]').html(options);
                        } else {
                            $('select[name="booking_service"]').html('<option value="">' + response.data.message + '</option>');
                        }
                    }
                });
                
                // Also reload time slots if date is selected
                var date = $('input[name="booking_date"]').val();
                if (date) {
                    WCBooking.loadTimeSlots(date);
                }
            });
        },

        extrasToggle: function() {
            $(document).on('change', '.wc-booking-extras input[type="checkbox"]', function() {
                var extra_id = $(this).val();
                var is_checked = $(this).is(':checked');
                
                if (is_checked) {
                    $('input[name="booking_extras[]"][value="' + extra_id + '"]').prop('disabled', false);
                } else {
                    $('input[name="booking_extras[]"][value="' + extra_id + '"]').prop('disabled', true);
                }
            });
        }
    };

    WCBooking.init();
});
