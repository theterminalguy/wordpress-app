<?php
/**
 * Fired during plugin activation
 *
 * @package WP_Booking_Plugin
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class WP_Booking_Activator {

	/**
	 * Plugin activation handler.
	 *
	 * - Registers custom post type
	 * - Flushes rewrite rules
	 * - Sets default options
	 */
	public static function activate() {
		// Set default options
		self::set_default_options();

		// Register custom post type
		self::register_post_type();

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Register the custom post type for bookings.
	 */
	private static function register_post_type() {
		$args = array(
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'booking' ),
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-calendar-alt',
			'supports'            => array( 'title' ),
			'labels'              => array(
				'name'               => _x( 'Bookings', 'post type general name', 'wp-booking-plugin' ),
				'singular_name'      => _x( 'Booking', 'post type singular name', 'wp-booking-plugin' ),
				'menu_name'          => _x( 'Bookings', 'admin menu', 'wp-booking-plugin' ),
				'name_admin_bar'     => _x( 'Booking', 'add new on admin bar', 'wp-booking-plugin' ),
				'add_new'            => _x( 'Add New', 'booking', 'wp-booking-plugin' ),
				'add_new_item'       => __( 'Add New Booking', 'wp-booking-plugin' ),
				'new_item'           => __( 'New Booking', 'wp-booking-plugin' ),
				'edit_item'          => __( 'Edit Booking', 'wp-booking-plugin' ),
				'view_item'          => __( 'View Booking', 'wp-booking-plugin' ),
				'all_items'          => __( 'All Bookings', 'wp-booking-plugin' ),
				'search_items'       => __( 'Search Bookings', 'wp-booking-plugin' ),
				'parent_item_colon'  => __( 'Parent Bookings:', 'wp-booking-plugin' ),
				'not_found'          => __( 'No bookings found.', 'wp-booking-plugin' ),
				'not_found_in_trash' => __( 'No bookings found in Trash.', 'wp-booking-plugin' ),
			),
		);

		register_post_type( 'wp_booking', $args );
	}

	/**
	 * Set default plugin options.
	 */
	private static function set_default_options() {
		$default_settings = array(
			'time_slot_duration' => 30, // 30 minutes
			'start_time'         => '09:00',
			'end_time'           => '17:00',
			'days_available'     => array( 1, 2, 3, 4, 5 ), // Monday to Friday
			'booking_buffer'     => 24, // 24 hours minimum notice
			'admin_email'        => get_option( 'admin_email' ),
		);

		add_option( 'wp_booking_settings', $default_settings );
	}
}
