jQuery(function($) {
    'use strict';

    var calendarEl = document.getElementById('wc-booking-calendar');
    var calendar;
    var currentView = localStorage.getItem('wc_booking_calendar_view') || 'dayGridMonth';
    var filters = {
        employee: '',
        service: '',
        status: ''
    };

    // Initialize calendar
    function initCalendar() {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: currentView,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            views: {
                dayGridMonth: {
                    titleFormat: { year: 'numeric', month: 'long' }
                },
                timeGridWeek: {
                    titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
                },
                timeGridDay: {
                    titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
                }
            },
            eventClick: function(info) {
                showBookingDetails(info.event);
            },
            datesSet: function(info) {
                loadBookings(info.startStr, info.endStr);
            },
            viewDidMount: function(info) {
                currentView = info.view.type;
                localStorage.setItem('wc_booking_calendar_view', currentView);
            },
            eventContent: function(arg) {
                return {
                    html: '<div class="fc-event-time">' + arg.timeText + '</div>' +
                          '<div class="fc-event-title">' + arg.event.title + '</div>'
                };
            },
            eventClassNames: function(arg) {
                return ['fc-event-' + arg.event.extendedProps.status];
            }
        });

        calendar.render();
    }

    // Load bookings via AJAX
    function loadBookings(start, end) {
        $.ajax({
            url: wc_booking_reports.ajax_url,
            data: {
                action: 'wc_booking_get_calendar_data',
                start: start,
                end: end,
                employee: filters.employee,
                service: filters.service,
                status: filters.status,
                security: wc_booking_reports.nonce
            },
            beforeSend: function() {
                $('#wc-booking-calendar').addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    calendar.removeAllEvents();
                    calendar.addEventSource(response.data.events);
                    
                    $('#wc-booking-total-bookings').text(response.data.stats.total_bookings);
                    $('#wc-booking-total-revenue').text(response.data.stats.total_revenue);
                }
            },
            complete: function() {
                $('#wc-booking-calendar').removeClass('loading');
            }
        });
    }

    // Show booking details
    function showBookingDetails(event) {
        var details = `
            <div class="wc-booking-detail">
                <h4>${event.title}</h4>
                <p><strong>${wc_booking_reports.i18n.status}:</strong> ${event.extendedProps.status}</p>
                <p><strong>${wc_booking_reports.i18n.employee}:</strong> ${event.extendedProps.employee}</p>
                <p><strong>${wc_booking_reports.i18n.service}:</strong> ${event.extendedProps.service}</p>
                <p><strong>${wc_booking_reports.i18n.revenue}:</strong> ${event.extendedProps.revenue}</p>
                <p><strong>${wc_booking_reports.i18n.time}:</strong> ${event.start.toLocaleString()}</p>
                <a href="${event.url}" class="button button-primary">${wc_booking_reports.i18n.view_details}</a>
            </div>
        `;
        
        $('#wc-booking-details-content').html(details);
    }

    // Initialize filters
    function initFilters() {
        $('#wc-booking-filter-employee, #wc-booking-filter-service, #wc-booking-filter-status').on('change', function() {
            filters.employee = $('#wc-booking-filter-employee').val();
            filters.service = $('#wc-booking-filter-service').val();
            filters.status = $('#wc-booking-filter-status').val();
            
            var view = calendar.view;
            loadBookings(view.activeStart, view.activeEnd);
        });
    }

    // Initialize
    $(document).ready(function() {
        initCalendar();
        initFilters();
    });
});
