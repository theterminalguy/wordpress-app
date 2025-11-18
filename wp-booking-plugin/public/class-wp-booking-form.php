<?php
/**
 * Booking form functionality
 *
 * @package WP_Booking_Plugin
 */

/**
 * Handle booking form shortcode and AJAX requests.
 */
class WP_Booking_Form {

	/**
	 * Render booking form shortcode.
	 *
	 * @return string Form HTML.
	 */
	public function render_form() {
		ob_start();
		?>
		<div class="wp-booking-form-container">
			<form id="wp-booking-form" class="wp-booking-form">
				<!-- Step 1: Date Selection -->
				<div class="booking-step" id="step-date">
					<h3><?php esc_html_e( 'Select a Date', 'wp-booking-plugin' ); ?></h3>
					<div class="form-group">
						<input type="text" id="booking-date" name="booking_date" placeholder="<?php esc_attr_e( 'Choose a date', 'wp-booking-plugin' ); ?>" required readonly />
					</div>
				</div>

				<!-- Step 2: Time Selection -->
				<div class="booking-step" id="step-time" style="display: none;">
					<h3><?php esc_html_e( 'Select a Time', 'wp-booking-plugin' ); ?></h3>
					<div class="form-group">
						<div id="time-slots-container" class="time-slots-grid">
							<!-- Time slots will be loaded here via AJAX -->
						</div>
						<input type="hidden" id="booking-time" name="booking_time" required />
					</div>
					<button type="button" class="btn btn-secondary" id="back-to-date">
						<?php esc_html_e( 'Back to Date', 'wp-booking-plugin' ); ?>
					</button>
				</div>

				<!-- Step 3: Contact Details -->
				<div class="booking-step" id="step-details" style="display: none;">
					<h3><?php esc_html_e( 'Your Details', 'wp-booking-plugin' ); ?></h3>

					<div class="form-group">
						<label for="booking-name"><?php esc_html_e( 'Full Name', 'wp-booking-plugin' ); ?> <span class="required">*</span></label>
						<input type="text" id="booking-name" name="booking_name" required />
					</div>

					<div class="form-group">
						<label for="booking-email"><?php esc_html_e( 'Email Address', 'wp-booking-plugin' ); ?> <span class="required">*</span></label>
						<input type="email" id="booking-email" name="booking_email" required />
					</div>

					<div class="form-group">
						<label for="booking-phone"><?php esc_html_e( 'Phone Number', 'wp-booking-plugin' ); ?> <span class="required">*</span></label>
						<input type="tel" id="booking-phone" name="booking_phone" required />
					</div>

					<div class="form-group">
						<label for="booking-message"><?php esc_html_e( 'Message / Notes', 'wp-booking-plugin' ); ?></label>
						<textarea id="booking-message" name="booking_message" rows="4"></textarea>
					</div>

					<div class="form-actions">
						<button type="button" class="btn btn-secondary" id="back-to-time">
							<?php esc_html_e( 'Back to Time', 'wp-booking-plugin' ); ?>
						</button>
						<button type="submit" class="btn btn-primary" id="submit-booking">
							<?php esc_html_e( 'Submit Booking', 'wp-booking-plugin' ); ?>
						</button>
					</div>
				</div>

				<!-- Loading indicator -->
				<div class="booking-loading" style="display: none;">
					<div class="spinner"></div>
					<p><?php esc_html_e( 'Processing...', 'wp-booking-plugin' ); ?></p>
				</div>

				<!-- Success message -->
				<div class="booking-success" style="display: none;">
					<div class="success-icon">✓</div>
					<h3><?php esc_html_e( 'Booking Submitted!', 'wp-booking-plugin' ); ?></h3>
					<p><?php esc_html_e( 'Your booking request has been received and is pending approval. You will receive a confirmation email shortly.', 'wp-booking-plugin' ); ?></p>
					<button type="button" class="btn btn-primary" id="new-booking">
						<?php esc_html_e( 'Make Another Booking', 'wp-booking-plugin' ); ?>
					</button>
				</div>

				<!-- Error message -->
				<div class="booking-error" style="display: none;">
					<div class="error-icon">✕</div>
					<h3><?php esc_html_e( 'Error', 'wp-booking-plugin' ); ?></h3>
					<p class="error-message"></p>
					<button type="button" class="btn btn-primary" id="retry-booking">
						<?php esc_html_e( 'Try Again', 'wp-booking-plugin' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * AJAX handler to get available time slots for a date.
	 */
	public function ajax_get_available_slots() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp_booking_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wp-booking-plugin' ) ) );
		}

		$date = isset( $_POST['date'] ) ? sanitize_text_field( $_POST['date'] ) : '';

		if ( empty( $date ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date.', 'wp-booking-plugin' ) ) );
		}

		// Get settings
		$settings = get_option( 'wp_booking_settings', array() );

		// Generate time slots
		$slots = $this->generate_time_slots( $date, $settings );

		// Get booked slots for this date
		$booked_slots = $this->get_booked_slots( $date );

		// Filter out booked slots
		$available_slots = array_diff( $slots, $booked_slots );

		wp_send_json_success( array( 'slots' => array_values( $available_slots ) ) );
	}

	/**
	 * AJAX handler to submit a booking.
	 */
	public function ajax_submit_booking() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp_booking_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wp-booking-plugin' ) ) );
		}

		// Validate and sanitize input
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$date    = isset( $_POST['date'] ) ? sanitize_text_field( $_POST['date'] ) : '';
		$time    = isset( $_POST['time'] ) ? sanitize_text_field( $_POST['time'] ) : '';
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';

		// Validate required fields
		if ( empty( $name ) || empty( $email ) || empty( $phone ) || empty( $date ) || empty( $time ) ) {
			wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'wp-booking-plugin' ) ) );
		}

		// Validate email
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'wp-booking-plugin' ) ) );
		}

		// Check if slot is still available
		if ( ! $this->is_slot_available( $date, $time ) ) {
			wp_send_json_error( array( 'message' => __( 'Sorry, this time slot is no longer available.', 'wp-booking-plugin' ) ) );
		}

		// Create booking post
		$booking_id = wp_insert_post(
			array(
				'post_type'   => 'wp_booking',
				'post_title'  => sprintf(
					/* translators: 1: name, 2: date, 3: time */
					__( 'Booking: %1$s - %2$s at %3$s', 'wp-booking-plugin' ),
					$name,
					$date,
					$time
				),
				'post_status' => 'pending_approval',
			)
		);

		if ( is_wp_error( $booking_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create booking. Please try again.', 'wp-booking-plugin' ) ) );
		}

		// Save booking meta
		update_post_meta( $booking_id, '_booking_name', $name );
		update_post_meta( $booking_id, '_booking_email', $email );
		update_post_meta( $booking_id, '_booking_phone', $phone );
		update_post_meta( $booking_id, '_booking_date', $date );
		update_post_meta( $booking_id, '_booking_time', $time );
		update_post_meta( $booking_id, '_booking_message', $message );

		// Send emails
		WP_Booking_Email::send_booking_confirmation( $booking_id );
		WP_Booking_Email::send_admin_notification( $booking_id );

		wp_send_json_success(
			array(
				'message'    => __( 'Booking submitted successfully!', 'wp-booking-plugin' ),
				'booking_id' => $booking_id,
			)
		);
	}

	/**
	 * Generate time slots for a given date.
	 *
	 * @param string $date     Date in Y-m-d format.
	 * @param array  $settings Plugin settings.
	 * @return array Array of time slots.
	 */
	private function generate_time_slots( $date, $settings ) {
		$slots            = array();
		$duration         = isset( $settings['time_slot_duration'] ) ? intval( $settings['time_slot_duration'] ) : 30;
		$start_time       = isset( $settings['start_time'] ) ? $settings['start_time'] : '09:00';
		$end_time         = isset( $settings['end_time'] ) ? $settings['end_time'] : '17:00';
		$booking_buffer   = isset( $settings['booking_buffer'] ) ? intval( $settings['booking_buffer'] ) : 24;
		$days_available   = isset( $settings['days_available'] ) ? $settings['days_available'] : array( 1, 2, 3, 4, 5 );

		// Check if date is in the past
		$selected_date = strtotime( $date );
		$current_time  = current_time( 'timestamp' );

		if ( $selected_date < strtotime( 'today', $current_time ) ) {
			return $slots;
		}

		// Check if date is within booking buffer
		if ( $selected_date < ( $current_time + ( $booking_buffer * HOUR_IN_SECONDS ) ) ) {
			return $slots;
		}

		// Check if day is available
		$day_of_week = intval( date( 'w', $selected_date ) );
		if ( ! in_array( $day_of_week, $days_available, true ) ) {
			return $slots;
		}

		// Generate time slots
		$start = strtotime( $date . ' ' . $start_time );
		$end   = strtotime( $date . ' ' . $end_time );

		while ( $start < $end ) {
			$slots[] = date( 'H:i', $start );
			$start   = strtotime( '+' . $duration . ' minutes', $start );
		}

		return $slots;
	}

	/**
	 * Get already booked time slots for a date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return array Array of booked time slots.
	 */
	private function get_booked_slots( $date ) {
		$booked_slots = array();

		$args = array(
			'post_type'      => 'wp_booking',
			'post_status'    => array( 'pending_approval', 'approved' ),
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => '_booking_date',
					'value'   => $date,
					'compare' => '=',
				),
			),
		);

		$bookings = get_posts( $args );

		foreach ( $bookings as $booking ) {
			$time = get_post_meta( $booking->ID, '_booking_time', true );
			if ( $time ) {
				$booked_slots[] = $time;
			}
		}

		return $booked_slots;
	}

	/**
	 * Check if a time slot is available.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @param string $time Time in H:i format.
	 * @return bool True if available, false otherwise.
	 */
	private function is_slot_available( $date, $time ) {
		$booked_slots = $this->get_booked_slots( $date );
		return ! in_array( $time, $booked_slots, true );
	}
}
