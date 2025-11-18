<?php
/**
 * The public-facing functionality of the plugin
 *
 * @package WP_Booking_Plugin
 */

/**
 * The public-facing functionality of the plugin.
 */
class WP_Booking_Public {

	/**
	 * Enqueue public styles.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'wp-booking-public',
			WP_BOOKING_PLUGIN_URL . 'assets/css/public.css',
			array(),
			WP_BOOKING_VERSION,
			'all'
		);
	}

	/**
	 * Enqueue public scripts.
	 */
	public function enqueue_scripts() {
		// Enqueue Flatpickr for date/time picking
		wp_enqueue_style(
			'flatpickr',
			'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
			array(),
			'4.6.13',
			'all'
		);

		wp_enqueue_script(
			'flatpickr',
			'https://cdn.jsdelivr.net/npm/flatpickr',
			array(),
			'4.6.13',
			true
		);

		wp_enqueue_script(
			'wp-booking-public',
			WP_BOOKING_PLUGIN_URL . 'assets/js/public.js',
			array( 'jquery', 'flatpickr' ),
			WP_BOOKING_VERSION,
			true
		);

		// Localize script with AJAX URL and settings
		$settings = get_option( 'wp_booking_settings', array() );

		wp_localize_script(
			'wp-booking-public',
			'wpBooking',
			array(
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'wp_booking_nonce' ),
				'settings'         => $settings,
				'i18n'             => array(
					'selectDate'     => __( 'Select a date', 'wp-booking-plugin' ),
					'selectTime'     => __( 'Select a time slot', 'wp-booking-plugin' ),
					'noSlotsMessage' => __( 'No available time slots for this date.', 'wp-booking-plugin' ),
					'successMessage' => __( 'Booking submitted successfully! You will receive a confirmation email shortly.', 'wp-booking-plugin' ),
					'errorMessage'   => __( 'An error occurred. Please try again.', 'wp-booking-plugin' ),
				),
			)
		);
	}
}
