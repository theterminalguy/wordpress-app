<?php
/**
 * Email notification functionality
 *
 * @package WP_Booking_Plugin
 */

/**
 * Handle email notifications for bookings.
 */
class WP_Booking_Email {

	/**
	 * Send booking confirmation email to user.
	 *
	 * @param int $booking_id The booking post ID.
	 */
	public static function send_booking_confirmation( $booking_id ) {
		$booking_data = self::get_booking_data( $booking_id );

		if ( ! $booking_data ) {
			return false;
		}

		$to      = $booking_data['email'];
		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Booking Confirmation - %s', 'wp-booking-plugin' ),
			get_bloginfo( 'name' )
		);

		$message = self::get_confirmation_email_template( $booking_data );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		return wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Send new booking notification to admin.
	 *
	 * @param int $booking_id The booking post ID.
	 */
	public static function send_admin_notification( $booking_id ) {
		$booking_data = self::get_booking_data( $booking_id );
		$settings     = get_option( 'wp_booking_settings', array() );

		if ( ! $booking_data ) {
			return false;
		}

		$admin_email = isset( $settings['admin_email'] ) ? $settings['admin_email'] : get_option( 'admin_email' );

		$subject = sprintf(
			/* translators: %s: site name */
			__( 'New Booking Pending Approval - %s', 'wp-booking-plugin' ),
			get_bloginfo( 'name' )
		);

		$message = self::get_admin_notification_template( $booking_data, $booking_id );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
		);

		return wp_mail( $admin_email, $subject, $message, $headers );
	}

	/**
	 * Send approval email to user.
	 *
	 * @param int $booking_id The booking post ID.
	 */
	public static function send_approval_email( $booking_id ) {
		$booking_data = self::get_booking_data( $booking_id );

		if ( ! $booking_data ) {
			return false;
		}

		$to      = $booking_data['email'];
		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Booking Approved - %s', 'wp-booking-plugin' ),
			get_bloginfo( 'name' )
		);

		$message = self::get_approval_email_template( $booking_data );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		return wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Send rejection email to user.
	 *
	 * @param int $booking_id The booking post ID.
	 */
	public static function send_rejection_email( $booking_id ) {
		$booking_data = self::get_booking_data( $booking_id );

		if ( ! $booking_data ) {
			return false;
		}

		$to      = $booking_data['email'];
		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Booking Update - %s', 'wp-booking-plugin' ),
			get_bloginfo( 'name' )
		);

		$message = self::get_rejection_email_template( $booking_data );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		return wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Get booking data from post ID.
	 *
	 * @param int $booking_id The booking post ID.
	 * @return array|false Booking data or false if not found.
	 */
	private static function get_booking_data( $booking_id ) {
		$post = get_post( $booking_id );

		if ( ! $post || 'wp_booking' !== $post->post_type ) {
			return false;
		}

		return array(
			'name'    => get_post_meta( $booking_id, '_booking_name', true ),
			'email'   => get_post_meta( $booking_id, '_booking_email', true ),
			'phone'   => get_post_meta( $booking_id, '_booking_phone', true ),
			'date'    => get_post_meta( $booking_id, '_booking_date', true ),
			'time'    => get_post_meta( $booking_id, '_booking_time', true ),
			'message' => get_post_meta( $booking_id, '_booking_message', true ),
		);
	}

	/**
	 * Get confirmation email template.
	 *
	 * @param array $data Booking data.
	 * @return string Email HTML content.
	 */
	private static function get_confirmation_email_template( $data ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #0073aa; color: #fff; padding: 20px; text-align: center; }
				.content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
				.booking-details { background: #fff; padding: 15px; margin: 15px 0; border-left: 4px solid #0073aa; }
				.footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php esc_html_e( 'Booking Received', 'wp-booking-plugin' ); ?></h1>
				</div>
				<div class="content">
					<p><?php printf( esc_html__( 'Hello %s,', 'wp-booking-plugin' ), esc_html( $data['name'] ) ); ?></p>
					<p><?php esc_html_e( 'Thank you for your booking request. We have received the following details:', 'wp-booking-plugin' ); ?></p>

					<div class="booking-details">
						<p><strong><?php esc_html_e( 'Date:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $data['date'] ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Time:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['time'] ); ?></p>
						<p><strong><?php esc_html_e( 'Email:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['email'] ); ?></p>
						<p><strong><?php esc_html_e( 'Phone:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['phone'] ); ?></p>
						<?php if ( ! empty( $data['message'] ) ) : ?>
							<p><strong><?php esc_html_e( 'Message:', 'wp-booking-plugin' ); ?></strong><br><?php echo nl2br( esc_html( $data['message'] ) ); ?></p>
						<?php endif; ?>
					</div>

					<p><?php esc_html_e( 'Your booking is currently pending approval. You will receive a confirmation email once it has been reviewed.', 'wp-booking-plugin' ); ?></p>
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
	 * Get admin notification email template.
	 *
	 * @param array $data       Booking data.
	 * @param int   $booking_id Booking ID.
	 * @return string Email HTML content.
	 */
	private static function get_admin_notification_template( $data, $booking_id ) {
		$approve_url = admin_url( 'admin-post.php?action=approve_booking&booking_id=' . $booking_id );
		$reject_url  = admin_url( 'admin-post.php?action=reject_booking&booking_id=' . $booking_id );
		$edit_url    = admin_url( 'post.php?post=' . $booking_id . '&action=edit' );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #0073aa; color: #fff; padding: 20px; text-align: center; }
				.content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
				.booking-details { background: #fff; padding: 15px; margin: 15px 0; border-left: 4px solid #0073aa; }
				.actions { text-align: center; margin: 20px 0; }
				.button { display: inline-block; padding: 12px 24px; margin: 5px; text-decoration: none; border-radius: 4px; font-weight: bold; }
				.approve { background: #46b450; color: #fff; }
				.reject { background: #dc3232; color: #fff; }
				.edit { background: #0073aa; color: #fff; }
				.footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php esc_html_e( 'New Booking Request', 'wp-booking-plugin' ); ?></h1>
				</div>
				<div class="content">
					<p><?php esc_html_e( 'A new booking request has been submitted and is pending your approval:', 'wp-booking-plugin' ); ?></p>

					<div class="booking-details">
						<p><strong><?php esc_html_e( 'Name:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['name'] ); ?></p>
						<p><strong><?php esc_html_e( 'Email:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['email'] ); ?></p>
						<p><strong><?php esc_html_e( 'Phone:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['phone'] ); ?></p>
						<p><strong><?php esc_html_e( 'Date:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $data['date'] ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Time:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['time'] ); ?></p>
						<?php if ( ! empty( $data['message'] ) ) : ?>
							<p><strong><?php esc_html_e( 'Message:', 'wp-booking-plugin' ); ?></strong><br><?php echo nl2br( esc_html( $data['message'] ) ); ?></p>
						<?php endif; ?>
					</div>

					<div class="actions">
						<a href="<?php echo esc_url( $approve_url ); ?>" class="button approve"><?php esc_html_e( 'Approve Booking', 'wp-booking-plugin' ); ?></a>
						<a href="<?php echo esc_url( $reject_url ); ?>" class="button reject"><?php esc_html_e( 'Reject Booking', 'wp-booking-plugin' ); ?></a>
						<a href="<?php echo esc_url( $edit_url ); ?>" class="button edit"><?php esc_html_e( 'View Details', 'wp-booking-plugin' ); ?></a>
					</div>
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
	 * Get approval email template.
	 *
	 * @param array $data Booking data.
	 * @return string Email HTML content.
	 */
	private static function get_approval_email_template( $data ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #46b450; color: #fff; padding: 20px; text-align: center; }
				.content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
				.booking-details { background: #fff; padding: 15px; margin: 15px 0; border-left: 4px solid #46b450; }
				.footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php esc_html_e( 'Booking Approved!', 'wp-booking-plugin' ); ?></h1>
				</div>
				<div class="content">
					<p><?php printf( esc_html__( 'Hello %s,', 'wp-booking-plugin' ), esc_html( $data['name'] ) ); ?></p>
					<p><?php esc_html_e( 'Great news! Your booking has been approved.', 'wp-booking-plugin' ); ?></p>

					<div class="booking-details">
						<p><strong><?php esc_html_e( 'Date:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $data['date'] ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Time:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['time'] ); ?></p>
					</div>

					<p><?php esc_html_e( 'We look forward to seeing you!', 'wp-booking-plugin' ); ?></p>
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
	 * Get rejection email template.
	 *
	 * @param array $data Booking data.
	 * @return string Email HTML content.
	 */
	private static function get_rejection_email_template( $data ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #dc3232; color: #fff; padding: 20px; text-align: center; }
				.content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
				.booking-details { background: #fff; padding: 15px; margin: 15px 0; border-left: 4px solid #dc3232; }
				.footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php esc_html_e( 'Booking Update', 'wp-booking-plugin' ); ?></h1>
				</div>
				<div class="content">
					<p><?php printf( esc_html__( 'Hello %s,', 'wp-booking-plugin' ), esc_html( $data['name'] ) ); ?></p>
					<p><?php esc_html_e( 'We regret to inform you that your booking request could not be accommodated at this time.', 'wp-booking-plugin' ); ?></p>

					<div class="booking-details">
						<p><strong><?php esc_html_e( 'Requested Date:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $data['date'] ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Requested Time:', 'wp-booking-plugin' ); ?></strong> <?php echo esc_html( $data['time'] ); ?></p>
					</div>

					<p><?php esc_html_e( 'Please feel free to try booking a different time slot that may better suit your needs.', 'wp-booking-plugin' ); ?></p>
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
}
