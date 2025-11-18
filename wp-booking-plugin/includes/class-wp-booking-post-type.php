<?php
/**
 * Register custom post type for bookings
 *
 * @package WP_Booking_Plugin
 */

/**
 * Register custom post type for bookings.
 */
class WP_Booking_Post_Type {

	/**
	 * Register the custom post type for bookings.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Bookings', 'post type general name', 'wp-booking-plugin' ),
			'singular_name'         => _x( 'Booking', 'post type singular name', 'wp-booking-plugin' ),
			'menu_name'             => _x( 'Bookings', 'admin menu', 'wp-booking-plugin' ),
			'name_admin_bar'        => _x( 'Booking', 'add new on admin bar', 'wp-booking-plugin' ),
			'add_new'               => _x( 'Add New', 'booking', 'wp-booking-plugin' ),
			'add_new_item'          => __( 'Add New Booking', 'wp-booking-plugin' ),
			'new_item'              => __( 'New Booking', 'wp-booking-plugin' ),
			'edit_item'             => __( 'Edit Booking', 'wp-booking-plugin' ),
			'view_item'             => __( 'View Booking', 'wp-booking-plugin' ),
			'all_items'             => __( 'All Bookings', 'wp-booking-plugin' ),
			'search_items'          => __( 'Search Bookings', 'wp-booking-plugin' ),
			'parent_item_colon'     => __( 'Parent Bookings:', 'wp-booking-plugin' ),
			'not_found'             => __( 'No bookings found.', 'wp-booking-plugin' ),
			'not_found_in_trash'    => __( 'No bookings found in Trash.', 'wp-booking-plugin' ),
			'filter_items_list'     => __( 'Filter bookings list', 'wp-booking-plugin' ),
			'items_list_navigation' => __( 'Bookings list navigation', 'wp-booking-plugin' ),
			'items_list'            => __( 'Bookings list', 'wp-booking-plugin' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'booking' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-calendar-alt',
			'supports'           => array( 'title' ),
			'show_in_rest'       => false,
		);

		register_post_type( 'wp_booking', $args );

		// Register custom post statuses for booking workflow
		$this->register_post_statuses();
	}

	/**
	 * Register custom post statuses for booking workflow.
	 */
	private function register_post_statuses() {
		// Pending approval status
		register_post_status(
			'pending_approval',
			array(
				'label'                     => _x( 'Pending Approval', 'booking status', 'wp-booking-plugin' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count of bookings */
				'label_count'               => _n_noop(
					'Pending Approval <span class="count">(%s)</span>',
					'Pending Approval <span class="count">(%s)</span>',
					'wp-booking-plugin'
				),
			)
		);

		// Approved status
		register_post_status(
			'approved',
			array(
				'label'                     => _x( 'Approved', 'booking status', 'wp-booking-plugin' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count of bookings */
				'label_count'               => _n_noop(
					'Approved <span class="count">(%s)</span>',
					'Approved <span class="count">(%s)</span>',
					'wp-booking-plugin'
				),
			)
		);

		// Rejected status
		register_post_status(
			'rejected',
			array(
				'label'                     => _x( 'Rejected', 'booking status', 'wp-booking-plugin' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count of bookings */
				'label_count'               => _n_noop(
					'Rejected <span class="count">(%s)</span>',
					'Rejected <span class="count">(%s)</span>',
					'wp-booking-plugin'
				),
			)
		);
	}
}
