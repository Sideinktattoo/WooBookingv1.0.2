<?php
class WC_Booking_Post_Types {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_types'], 5);
        add_action('init', [__CLASS__, 'register_taxonomies'], 5);
    }

    public static function register_post_types() {
        if (post_type_exists('wc_booking')) {
            return;
        }

        // Booking post type
        register_post_type('wc_booking',
            apply_filters('wc_booking_register_post_type',
                array(
                    'label'               => __('Bookings', 'wc-booking'),
                    'labels'              => array(
                        'name'                  => __('Bookings', 'wc-booking'),
                        'singular_name'         => __('Booking', 'wc-booking'),
                        'add_new'               => __('Add Booking', 'wc-booking'),
                        'add_new_item'          => __('Add New Booking', 'wc-booking'),
                        'edit'                  => __('Edit', 'wc-booking'),
                        'edit_item'             => __('Edit Booking', 'wc-booking'),
                        'new_item'              => __('New Booking', 'wc-booking'),
                        'view'                  => __('View Booking', 'wc-booking'),
                        'view_item'             => __('View Booking', 'wc-booking'),
                        'search_items'          => __('Search Bookings', 'wc-booking'),
                        'not_found'             => __('No Bookings found', 'wc-booking'),
                        'not_found_in_trash'    => __('No Bookings found in trash', 'wc-booking'),
                        'menu_name'             => _x('Bookings', 'Admin menu name', 'wc-booking'),
                    ),
                    'description'         => __('This is where bookings are stored.', 'wc-booking'),
                    'public'              => false,
                    'show_ui'             => true,
                    'capability_type'     => 'shop_order',
                    'map_meta_cap'        => true,
                    'publicly_queryable'  => false,
                    'exclude_from_search' => true,
                    'show_in_menu'        => current_user_can('manage_woocommerce') ? 'woocommerce' : false,
                    'hierarchical'        => false,
                    'show_in_nav_menus'   => false,
                    'rewrite'             => false,
                    'query_var'          => false,
                    'supports'            => array(''),
                    'has_archive'         => false,
                )
            )
        );

        // Employee post type
        register_post_type('wc_booking_employee',
            array(
                'label'               => __('Employees', 'wc-booking'),
                'labels'              => array(
                    'name'                  => __('Employees', 'wc-booking'),
                    'singular_name'         => __('Employee', 'wc-booking'),
                    'add_new'               => __('Add Employee', 'wc-booking'),
                    'add_new_item'          => __('Add New Employee', 'wc-booking'),
                    'edit_item'             => __('Edit Employee', 'wc-booking'),
                    'new_item'              => __('New Employee', 'wc-booking'),
                    'view_item'             => __('View Employee', 'wc-booking'),
                    'search_items'          => __('Search Employees', 'wc-booking'),
                    'not_found'             => __('No Employees found', 'wc-booking'),
                    'not_found_in_trash'    => __('No Employees found in trash', 'wc-booking'),
                    'menu_name'             => _x('Employees', 'Admin menu name', 'wc-booking'),
                ),
                'description'         => __('This is where you can add employees for bookings.', 'wc-booking'),
                'public'              => false,
                'show_ui'             => true,
                'capability_type'     => 'shop_order',
                'map_meta_cap'        => true,
                'publicly_queryable'  => false,
                'exclude_from_search' => true,
                'show_in_menu'        => current_user_can('manage_woocommerce') ? 'woocommerce' : false,
                'hierarchical'        => false,
                'show_in_nav_menus'   => false,
                'rewrite'             => false,
                'query_var'          => false,
                'supports'            => array('title', 'thumbnail'),
                'has_archive'         => false,
            )
        );
    }

    public static function register_taxonomies() {
        // Service taxonomy
        register_taxonomy('wc_booking_service',
            array('product'),
            array(
                'hierarchical'      => true,
                'show_ui'           => true,
                'show_in_nav_menus' => false,
                'query_var'         => false,
                'rewrite'           => false,
                'public'            => false,
                'label'             => _x('Booking Services', 'Taxonomy name', 'wc-booking'),
                'labels'            => array(
                    'name'              => _x('Booking Services', 'Taxonomy name', 'wc-booking'),
                    'singular_name'     => _x('Booking Service', 'Taxonomy name', 'wc-booking'),
                    'search_items'      => __('Search Booking Services', 'wc-booking'),
                    'all_items'         => __('All Booking Services', 'wc-booking'),
                    'parent_item'       => __('Parent Booking Service', 'wc-booking'),
                    'parent_item_colon' => __('Parent Booking Service:', 'wc-booking'),
                    'edit_item'         => __('Edit Booking Service', 'wc-booking'),
                    'update_item'       => __('Update Booking Service', 'wc-booking'),
                    'add_new_item'      => __('Add New Booking Service', 'wc-booking'),
                    'new_item_name'     => __('New Booking Service Name', 'wc-booking'),
                    'menu_name'         => __('Booking Services', 'wc-booking'),
                ),
            )
        );

        // Extra options taxonomy
        register_taxonomy('wc_booking_extra',
            array('product'),
            array(
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_in_nav_menus' => false,
                'query_var'         => false,
                'rewrite'           => false,
                'public'            => false,
                'label'             => _x('Booking Extras', 'Taxonomy name', 'wc-booking'),
                'labels'            => array(
                    'name'              => _x('Booking Extras', 'Taxonomy name', 'wc-booking'),
                    'singular_name'     => _x('Booking Extra', 'Taxonomy name', 'wc-booking'),
                    'search_items'      => __('Search Booking Extras', 'wc-booking'),
                    'all_items'         => __('All Booking Extras', 'wc-booking'),
                    'edit_item'         => __('Edit Booking Extra', 'wc-booking'),
                    'update_item'       => __('Update Booking Extra', 'wc-booking'),
                    'add_new_item'      => __('Add New Booking Extra', 'wc-booking'),
                    'new_item_name'     => __('New Booking Extra Name', 'wc-booking'),
                    'menu_name'         => __('Booking Extras', 'wc-booking'),
                ),
            )
        );
    }
}
