<?php
/**
 * Admin settings page
 *
 * @package WP_Booking_Plugin
 */

/**
 * Admin settings page functionality.
 */
class WP_Booking_Settings {

	/**
	 * Add settings page to admin menu.
	 */
	public function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=wp_booking',
			__( 'Booking Settings', 'wp-booking-plugin' ),
			__( 'Settings', 'wp-booking-plugin' ),
			'manage_options',
			'wp-booking-settings',
			array( $this, 'render_settings_page' )
		);

		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'wp_booking_settings_group',
			'wp_booking_settings',
			array( $this, 'sanitize_settings' )
		);

		// General Settings Section
		add_settings_section(
			'wp_booking_general_section',
			__( 'General Settings', 'wp-booking-plugin' ),
			array( $this, 'render_general_section' ),
			'wp-booking-settings'
		);

		add_settings_field(
			'time_slot_duration',
			__( 'Time Slot Duration (minutes)', 'wp-booking-plugin' ),
			array( $this, 'render_time_slot_duration_field' ),
			'wp-booking-settings',
			'wp_booking_general_section'
		);

		add_settings_field(
			'start_time',
			__( 'Start Time', 'wp-booking-plugin' ),
			array( $this, 'render_start_time_field' ),
			'wp-booking-settings',
			'wp_booking_general_section'
		);

		add_settings_field(
			'end_time',
			__( 'End Time', 'wp-booking-plugin' ),
			array( $this, 'render_end_time_field' ),
			'wp-booking-settings',
			'wp_booking_general_section'
		);

		add_settings_field(
			'days_available',
			__( 'Available Days', 'wp-booking-plugin' ),
			array( $this, 'render_days_available_field' ),
			'wp-booking-settings',
			'wp_booking_general_section'
		);

		add_settings_field(
			'booking_buffer',
			__( 'Minimum Booking Notice (hours)', 'wp-booking-plugin' ),
			array( $this, 'render_booking_buffer_field' ),
			'wp-booking-settings',
			'wp_booking_general_section'
		);

		add_settings_field(
			'admin_email',
			__( 'Admin Email for Notifications', 'wp-booking-plugin' ),
			array( $this, 'render_admin_email_field' ),
			'wp-booking-settings',
			'wp_booking_general_section'
		);
	}

	/**
	 * Render general settings section.
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure your booking time slots and availability.', 'wp-booking-plugin' ) . '</p>';
	}

	/**
	 * Render time slot duration field.
	 */
	public function render_time_slot_duration_field() {
		$settings = get_option( 'wp_booking_settings', array() );
		$value    = isset( $settings['time_slot_duration'] ) ? $settings['time_slot_duration'] : 30;
		?>
		<select name="wp_booking_settings[time_slot_duration]">
			<option value="15" <?php selected( $value, 15 ); ?>>15 <?php esc_html_e( 'minutes', 'wp-booking-plugin' ); ?></option>
			<option value="30" <?php selected( $value, 30 ); ?>>30 <?php esc_html_e( 'minutes', 'wp-booking-plugin' ); ?></option>
			<option value="45" <?php selected( $value, 45 ); ?>>45 <?php esc_html_e( 'minutes', 'wp-booking-plugin' ); ?></option>
			<option value="60" <?php selected( $value, 60 ); ?>>1 <?php esc_html_e( 'hour', 'wp-booking-plugin' ); ?></option>
			<option value="90" <?php selected( $value, 90 ); ?>>1.5 <?php esc_html_e( 'hours', 'wp-booking-plugin' ); ?></option>
			<option value="120" <?php selected( $value, 120 ); ?>>2 <?php esc_html_e( 'hours', 'wp-booking-plugin' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Duration of each booking time slot.', 'wp-booking-plugin' ); ?></p>
		<?php
	}

	/**
	 * Render start time field.
	 */
	public function render_start_time_field() {
		$settings = get_option( 'wp_booking_settings', array() );
		$value    = isset( $settings['start_time'] ) ? $settings['start_time'] : '09:00';
		?>
		<input type="time" name="wp_booking_settings[start_time]" value="<?php echo esc_attr( $value ); ?>" />
		<p class="description"><?php esc_html_e( 'Daily start time for bookings.', 'wp-booking-plugin' ); ?></p>
		<?php
	}

	/**
	 * Render end time field.
	 */
	public function render_end_time_field() {
		$settings = get_option( 'wp_booking_settings', array() );
		$value    = isset( $settings['end_time'] ) ? $settings['end_time'] : '17:00';
		?>
		<input type="time" name="wp_booking_settings[end_time]" value="<?php echo esc_attr( $value ); ?>" />
		<p class="description"><?php esc_html_e( 'Daily end time for bookings.', 'wp-booking-plugin' ); ?></p>
		<?php
	}

	/**
	 * Render days available field.
	 */
	public function render_days_available_field() {
		$settings        = get_option( 'wp_booking_settings', array() );
		$days_available  = isset( $settings['days_available'] ) ? $settings['days_available'] : array( 1, 2, 3, 4, 5 );
		$days_of_week    = array(
			0 => __( 'Sunday', 'wp-booking-plugin' ),
			1 => __( 'Monday', 'wp-booking-plugin' ),
			2 => __( 'Tuesday', 'wp-booking-plugin' ),
			3 => __( 'Wednesday', 'wp-booking-plugin' ),
			4 => __( 'Thursday', 'wp-booking-plugin' ),
			5 => __( 'Friday', 'wp-booking-plugin' ),
			6 => __( 'Saturday', 'wp-booking-plugin' ),
		);

		foreach ( $days_of_week as $day_num => $day_name ) {
			$checked = in_array( $day_num, $days_available, true ) ? 'checked' : '';
			?>
			<label style="display: inline-block; margin-right: 15px;">
				<input type="checkbox" name="wp_booking_settings[days_available][]" value="<?php echo esc_attr( $day_num ); ?>" <?php echo esc_attr( $checked ); ?> />
				<?php echo esc_html( $day_name ); ?>
			</label>
			<?php
		}
		?>
		<p class="description"><?php esc_html_e( 'Select which days are available for bookings.', 'wp-booking-plugin' ); ?></p>
		<?php
	}

	/**
	 * Render booking buffer field.
	 */
	public function render_booking_buffer_field() {
		$settings = get_option( 'wp_booking_settings', array() );
		$value    = isset( $settings['booking_buffer'] ) ? $settings['booking_buffer'] : 24;
		?>
		<input type="number" name="wp_booking_settings[booking_buffer]" value="<?php echo esc_attr( $value ); ?>" min="0" step="1" />
		<p class="description"><?php esc_html_e( 'Minimum hours in advance that bookings must be made.', 'wp-booking-plugin' ); ?></p>
		<?php
	}

	/**
	 * Render admin email field.
	 */
	public function render_admin_email_field() {
		$settings = get_option( 'wp_booking_settings', array() );
		$value    = isset( $settings['admin_email'] ) ? $settings['admin_email'] : get_option( 'admin_email' );
		?>
		<input type="email" name="wp_booking_settings[admin_email]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Email address to receive booking notifications.', 'wp-booking-plugin' ); ?></p>
		<?php
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized data.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['time_slot_duration'] ) ) {
			$sanitized['time_slot_duration'] = absint( $input['time_slot_duration'] );
		}

		if ( isset( $input['start_time'] ) ) {
			$sanitized['start_time'] = sanitize_text_field( $input['start_time'] );
		}

		if ( isset( $input['end_time'] ) ) {
			$sanitized['end_time'] = sanitize_text_field( $input['end_time'] );
		}

		if ( isset( $input['days_available'] ) && is_array( $input['days_available'] ) ) {
			$sanitized['days_available'] = array_map( 'absint', $input['days_available'] );
		} else {
			$sanitized['days_available'] = array();
		}

		if ( isset( $input['booking_buffer'] ) ) {
			$sanitized['booking_buffer'] = absint( $input['booking_buffer'] );
		}

		if ( isset( $input['admin_email'] ) ) {
			$sanitized['admin_email'] = sanitize_email( $input['admin_email'] );
		}

		return $sanitized;
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php
			if ( isset( $_GET['settings-updated'] ) ) {
				add_settings_error(
					'wp_booking_messages',
					'wp_booking_message',
					__( 'Settings saved successfully.', 'wp-booking-plugin' ),
					'success'
				);
			}
			settings_errors( 'wp_booking_messages' );
			?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wp_booking_settings_group' );
				do_settings_sections( 'wp-booking-settings' );
				submit_button();
				?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Shortcode', 'wp-booking-plugin' ); ?></h2>
			<p><?php esc_html_e( 'Use this shortcode to display the booking form on any page or post:', 'wp-booking-plugin' ); ?></p>
			<code>[wp_booking_form]</code>
		</div>
		<?php
	}
}
