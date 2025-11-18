<?php
/**
 * Booking cancellation functionality
 *
 * @package WP_Booking_Plugin
 */

/**
 * Handle booking cancellations.
 */
class WP_Booking_Cancellation {

	/**
	 * Initialize cancellation functionality.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle_cancellation_request' ) );
		add_shortcode( 'wp_booking_cancel', array( $this, 'render_cancellation_page' ) );
	}

	/**
	 * Generate a unique cancellation token for a booking.
	 *
	 * @param int $booking_id Booking post ID.
	 * @return string Cancellation token.
	 */
	public static function generate_cancellation_token( $booking_id ) {
		$token = wp_generate_password( 32, false );
		update_post_meta( $booking_id, '_cancellation_token', $token );
		return $token;
	}

	/**
	 * Get cancellation URL for a booking.
	 *
	 * @param int $booking_id Booking post ID.
	 * @return string Cancellation URL.
	 */
	public static function get_cancellation_url( $booking_id ) {
		$token = get_post_meta( $booking_id, '_cancellation_token', true );

		if ( empty( $token ) ) {
			$token = self::generate_cancellation_token( $booking_id );
		}

		return add_query_arg(
			array(
				'booking_action' => 'cancel',
				'booking_id'     => $booking_id,
				'token'          => $token,
			),
			home_url( '/' )
		);
	}

	/**
	 * Handle cancellation request from URL.
	 */
	public function handle_cancellation_request() {
		if ( ! isset( $_GET['booking_action'] ) || 'cancel' !== $_GET['booking_action'] ) {
			return;
		}

		if ( ! isset( $_GET['booking_id'] ) || ! isset( $_GET['token'] ) ) {
			return;
		}

		$booking_id = absint( $_GET['booking_id'] );
		$token      = sanitize_text_field( $_GET['token'] );

		// Verify booking exists
		$booking = get_post( $booking_id );
		if ( ! $booking || 'wp_booking' !== $booking->post_type ) {
			wp_die( esc_html__( 'Invalid booking.', 'wp-booking-plugin' ) );
		}

		// Verify token
		$stored_token = get_post_meta( $booking_id, '_cancellation_token', true );
		if ( empty( $stored_token ) || $token !== $stored_token ) {
			wp_die( esc_html__( 'Invalid cancellation link.', 'wp-booking-plugin' ) );
		}

		// Check if booking can be cancelled
		$status = get_post_status( $booking_id );
		if ( ! in_array( $status, array( 'pending_approval', 'approved' ), true ) ) {
			wp_die( esc_html__( 'This booking cannot be cancelled.', 'wp-booking-plugin' ) );
		}

		// Check if already cancelled
		if ( 'cancelled' === $status ) {
			wp_die( esc_html__( 'This booking has already been cancelled.', 'wp-booking-plugin' ) );
		}

		// Check cancellation deadline (if configured)
		if ( ! $this->can_cancel_booking( $booking_id ) ) {
			wp_die( esc_html__( 'The cancellation deadline has passed for this booking.', 'wp-booking-plugin' ) );
		}

		// Confirm cancellation
		if ( ! isset( $_GET['confirm'] ) ) {
			$this->show_cancellation_confirmation( $booking_id );
			exit;
		}

		// Process cancellation
		$this->cancel_booking( $booking_id );

		// Redirect to success page
		wp_redirect( add_query_arg( 'cancelled', '1', home_url( '/' ) ) );
		exit;
	}

	/**
	 * Check if a booking can be cancelled based on settings.
	 *
	 * @param int $booking_id Booking post ID.
	 * @return bool True if can be cancelled, false otherwise.
	 */
	private function can_cancel_booking( $booking_id ) {
		$settings = get_option( 'wp_booking_settings', array() );

		// Check if cancellation is enabled
		if ( isset( $settings['allow_cancellation'] ) && ! $settings['allow_cancellation'] ) {
			return false;
		}

		// Check cancellation deadline
		$cancellation_hours = isset( $settings['cancellation_deadline'] ) ? intval( $settings['cancellation_deadline'] ) : 24;

		if ( $cancellation_hours > 0 ) {
			$booking_date = get_post_meta( $booking_id, '_booking_date', true );
			$booking_time = get_post_meta( $booking_id, '_booking_time', true );

			if ( $booking_date && $booking_time ) {
				$booking_datetime = strtotime( $booking_date . ' ' . $booking_time );
				$deadline         = $booking_datetime - ( $cancellation_hours * HOUR_IN_SECONDS );

				if ( current_time( 'timestamp' ) > $deadline ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Show cancellation confirmation page.
	 *
	 * @param int $booking_id Booking post ID.
	 */
	private function show_cancellation_confirmation( $booking_id ) {
		$booking_data = array(
			'name' => get_post_meta( $booking_id, '_booking_name', true ),
			'date' => get_post_meta( $booking_id, '_booking_date', true ),
			'time' => get_post_meta( $booking_id, '_booking_time', true ),
		);

		$confirm_url = add_query_arg( 'confirm', '1', $_SERVER['REQUEST_URI'] );

		get_header();
		?>
		<div class="wp-booking-cancellation-confirmation" style="max-width: 600px; margin: 50px auto; padding: 20px; text-align: center;">
			<h1><?php esc_html_e( 'Cancel Booking', 'wp-booking-plugin' ); ?></h1>
			<p><?php esc_html_e( 'Are you sure you want to cancel this booking?', 'wp-booking-plugin' ); ?></p>

			<div style="background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 8px;">
				<p><strong><?php esc_html_e( 'Name:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $booking_data['name'] ); ?></p>
				<p><strong><?php esc_html_e( 'Date:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking_data['date'] ) ) ); ?></p>
				<p><strong><?php esc_html_e( 'Time:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $booking_data['time'] ); ?></p>
			</div>

			<p>
				<a href="<?php echo esc_url( $confirm_url ); ?>" class="button" style="background: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-right: 10px;">
					<?php esc_html_e( 'Yes, Cancel Booking', 'wp-booking-plugin' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button" style="background: #6c757d; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
					<?php esc_html_e( 'No, Go Back', 'wp-booking-plugin' ); ?>
				</a>
			</p>
		</div>
		<?php
		get_footer();
	}

	/**
	 * Cancel a booking.
	 *
	 * @param int $booking_id Booking post ID.
	 * @return bool True on success, false on failure.
	 */
	private function cancel_booking( $booking_id ) {
		// Update booking status
		wp_update_post(
			array(
				'ID'          => $booking_id,
				'post_status' => 'cancelled',
			)
		);

		// Send cancellation emails
		$this->send_cancellation_email( $booking_id );
		$this->send_admin_cancellation_notification( $booking_id );

		return true;
	}

	/**
	 * Send cancellation confirmation email to user.
	 *
	 * @param int $booking_id Booking post ID.
	 */
	private function send_cancellation_email( $booking_id ) {
		$booking_data = array(
			'name'  => get_post_meta( $booking_id, '_booking_name', true ),
			'email' => get_post_meta( $booking_id, '_booking_email', true ),
			'date'  => get_post_meta( $booking_id, '_booking_date', true ),
			'time'  => get_post_meta( $booking_id, '_booking_time', true ),
		);

		$to      = $booking_data['email'];
		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Booking Cancelled - %s', 'wp-booking-plugin' ),
			get_bloginfo( 'name' )
		);

		$message = $this->get_cancellation_email_template( $booking_data );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Send cancellation notification to admin.
	 *
	 * @param int $booking_id Booking post ID.
	 */
	private function send_admin_cancellation_notification( $booking_id ) {
		$settings     = get_option( 'wp_booking_settings', array() );
		$admin_email  = isset( $settings['admin_email'] ) ? $settings['admin_email'] : get_option( 'admin_email' );

		$booking_data = array(
			'name'  => get_post_meta( $booking_id, '_booking_name', true ),
			'email' => get_post_meta( $booking_id, '_booking_email', true ),
			'phone' => get_post_meta( $booking_id, '_booking_phone', true ),
			'date'  => get_post_meta( $booking_id, '_booking_date', true ),
			'time'  => get_post_meta( $booking_id, '_booking_time', true ),
		);

		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Booking Cancelled by Customer - %s', 'wp-booking-plugin' ),
			get_bloginfo( 'name' )
		);

		$message = $this->get_admin_cancellation_email_template( $booking_data );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
		);

		wp_mail( $admin_email, $subject, $message, $headers );
	}

	/**
	 * Get cancellation email template for user.
	 *
	 * @param array $data Booking data.
	 * @return string Email HTML content.
	 */
	private function get_cancellation_email_template( $data ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #dc3545; color: #fff; padding: 20px; text-align: center; }
				.content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
				.booking-details { background: #fff; padding: 15px; margin: 15px 0; border-left: 4px solid #dc3545; }
				.footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php esc_html_e( 'Booking Cancelled', 'wp-booking-plugin' ); ?></h1>
				</div>
				<div class="content">
					<p><?php printf( esc_html__( 'Hello %s,', 'wp-booking-plugin' ), esc_html( $data['name'] ) ); ?></p>
					<p><?php esc_html_e( 'Your booking has been successfully cancelled:', 'wp-booking-plugin' ); ?></p>

					<div class="booking-details">
						<p><strong><?php esc_html_e( 'Date:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $data['date'] ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Time:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['time'] ); ?></p>
					</div>

					<p><?php esc_html_e( 'If you would like to book again, please visit our booking page.', 'wp-booking-plugin' ); ?></p>
				</div>
				<div class="footer">
					<p><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get admin cancellation notification email template.
	 *
	 * @param array $data Booking data.
	 * @return string Email HTML content.
	 */
	private function get_admin_cancellation_email_template( $data ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #dc3545; color: #fff; padding: 20px; text-align: center; }
				.content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
				.booking-details { background: #fff; padding: 15px; margin: 15px 0; border-left: 4px solid #dc3545; }
				.footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php esc_html_e( 'Booking Cancelled by Customer', 'wp-booking-plugin' ); ?></h1>
				</div>
				<div class="content">
					<p><?php esc_html_e( 'A customer has cancelled their booking:', 'wp-booking-plugin' ); ?></p>

					<div class="booking-details">
						<p><strong><?php esc_html_e( 'Name:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['name'] ); ?></p>
						<p><strong><?php esc_html_e( 'Email:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['email'] ); ?></p>
						<p><strong><?php esc_html_e( 'Phone:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['phone'] ); ?></p>
						<p><strong><?php esc_html_e( 'Date:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $data['date'] ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Time:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['time'] ); ?></p>
					</div>

					<p><?php esc_html_e( 'This time slot is now available for other bookings.', 'wp-booking-plugin' ); ?></p>
				</div>
				<div class="footer">
					<p><?php echo esc_html( get_bloginfo( 'name' ) ); ?> - <?php esc_html_e( 'Admin Panel', 'wp-booking-plugin' ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render cancellation success page shortcode.
	 *
	 * @return string HTML content.
	 */
	public function render_cancellation_page() {
		if ( ! isset( $_GET['cancelled'] ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="wp-booking-cancellation-success" style="max-width: 600px; margin: 50px auto; padding: 40px; text-align: center; background: #f9f9f9; border-radius: 12px;">
			<div style="width: 80px; height: 80px; margin: 0 auto 20px; background: #dc3545; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px;">
				✓
			</div>
			<h2><?php esc_html_e( 'Booking Cancelled', 'wp-booking-plugin' ); ?></h2>
			<p><?php esc_html_e( 'Your booking has been successfully cancelled. You will receive a confirmation email shortly.', 'wp-booking-plugin' ); ?></p>
			<p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button" style="background: #0073aa; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;">
					<?php esc_html_e( 'Return to Home', 'wp-booking-plugin' ); ?>
				</a>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}
}
