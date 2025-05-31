jQuery(function($) {
    'use strict';

    var WCBookingAdmin = {
        init: function() {
            this.bookingStatus();
            this.employeeManagement();
            this.serviceManagement();
            this.extraOptions();
        },

        bookingStatus: function() {
            $(document).on('change', '#_booking_status', function() {
                var status = $(this).val();
                $('#post-status-display').text($('option:selected', this).text());
            });
        },

        employeeManagement: function() {
            // Add availability calendar for employees
            $('.wc-booking-employee-availability').datepicker({
                dateFormat: 'yy-mm-dd',
                beforeShowDay: function(date) {
                    var day = date.getDay();
                    var dateString = $.datepicker.formatDate('yy-mm-dd', date);
                    var isAvailable = day !== 0 && day !== 6; // Not Sunday or Saturday
                    
                    return [isAvailable, isAvailable ? 'bookable' : 'not-bookable'];
                }
            });
        },

        serviceManagement: function() {
            // Duration validation
            $(document).on('blur', '#service_duration', function() {
                var value = $(this).val();
                if (value < 1) {
                    $(this).val(1);
                }
            });
        },

        extraOptions: function() {
            // Cost validation
            $(document).on('blur', '#extra_cost', function() {
                var value = $(this).val();
                if (isNaN(value) || value < 0) {
                    $(this).val(0);
                }
            });
            
            // Duration validation
            $(document).on('blur', '#extra_duration', function() {
                var value = $(this).val();
                if (isNaN(value) || value < 0) {
                    $(this).val(0);
                }
            });
        }
    };

    WCBookingAdmin.init();
});
