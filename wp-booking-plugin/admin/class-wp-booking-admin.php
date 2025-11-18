<?php
/**
 * The admin-specific functionality of the plugin
 *
 * @package WP_Booking_Plugin
 */

/**
 * The admin-specific functionality of the plugin.
 */
class WP_Booking_Admin {

	/**
	 * Enqueue admin styles.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'wp-booking-admin',
			WP_BOOKING_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			WP_BOOKING_VERSION,
			'all'
		);
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'wp-booking-admin',
			WP_BOOKING_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			WP_BOOKING_VERSION,
			false
		);
	}

	/**
	 * Set custom columns for booking list.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function set_custom_columns( $columns ) {
		$new_columns = array();

		// Keep checkbox
		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
		}

		// Add custom columns
		$new_columns['booking_name']   = __( 'Name', 'wp-booking-plugin' );
		$new_columns['booking_date']   = __( 'Date', 'wp-booking-plugin' );
		$new_columns['booking_time']   = __( 'Time', 'wp-booking-plugin' );
		$new_columns['booking_email']  = __( 'Email', 'wp-booking-plugin' );
		$new_columns['booking_phone']  = __( 'Phone', 'wp-booking-plugin' );
		$new_columns['booking_status'] = __( 'Status', 'wp-booking-plugin' );
		$new_columns['booking_actions'] = __( 'Actions', 'wp-booking-plugin' );
		$new_columns['date']           = __( 'Submitted', 'wp-booking-plugin' );

		return $new_columns;
	}

	/**
	 * Display custom column content.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function custom_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'booking_name':
				echo esc_html( get_post_meta( $post_id, '_booking_name', true ) );
				break;

			case 'booking_date':
				$date = get_post_meta( $post_id, '_booking_date', true );
				if ( $date ) {
					echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) );
				}
				break;

			case 'booking_time':
				echo esc_html( get_post_meta( $post_id, '_booking_time', true ) );
				break;

			case 'booking_email':
				$email = get_post_meta( $post_id, '_booking_email', true );
				if ( $email ) {
					echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
				}
				break;

			case 'booking_phone':
				echo esc_html( get_post_meta( $post_id, '_booking_phone', true ) );
				break;

			case 'booking_status':
				$status = get_post_status( $post_id );
				$class  = '';
				$label  = '';

				switch ( $status ) {
					case 'pending_approval':
						$class = 'warning';
						$label = __( 'Pending', 'wp-booking-plugin' );
						break;
					case 'approved':
						$class = 'success';
						$label = __( 'Approved', 'wp-booking-plugin' );
						break;
					case 'rejected':
						$class = 'error';
						$label = __( 'Rejected', 'wp-booking-plugin' );
						break;
					case 'cancelled':
						$class = 'error';
						$label = __( 'Cancelled', 'wp-booking-plugin' );
						break;
					default:
						$label = ucfirst( $status );
						break;
				}

				echo '<span class="booking-status status-' . esc_attr( $class ) . '">' . esc_html( $label ) . '</span>';
				break;

			case 'booking_actions':
				$status = get_post_status( $post_id );
				if ( 'pending_approval' === $status ) {
					$approve_url = wp_nonce_url(
						admin_url( 'admin-post.php?action=approve_booking&booking_id=' . $post_id ),
						'approve_booking_' . $post_id
					);
					$reject_url  = wp_nonce_url(
						admin_url( 'admin-post.php?action=reject_booking&booking_id=' . $post_id ),
						'reject_booking_' . $post_id
					);

					echo '<a href="' . esc_url( $approve_url ) . '" class="button button-small button-primary">' . esc_html__( 'Approve', 'wp-booking-plugin' ) . '</a> ';
					echo '<a href="' . esc_url( $reject_url ) . '" class="button button-small">' . esc_html__( 'Reject', 'wp-booking-plugin' ) . '</a>';
				}
				break;
		}
	}

	/**
	 * Add meta boxes for booking details.
	 */
	public function add_booking_meta_boxes() {
		add_meta_box(
			'booking_details',
			__( 'Booking Details', 'wp-booking-plugin' ),
			array( $this, 'render_booking_details_meta_box' ),
			'wp_booking',
			'normal',
			'high'
		);

		add_meta_box(
			'booking_actions',
			__( 'Booking Actions', 'wp-booking-plugin' ),
			array( $this, 'render_booking_actions_meta_box' ),
			'wp_booking',
			'side',
			'high'
		);
	}

	/**
	 * Render booking details meta box.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_booking_details_meta_box( $post ) {
		$name    = get_post_meta( $post->ID, '_booking_name', true );
		$email   = get_post_meta( $post->ID, '_booking_email', true );
		$phone   = get_post_meta( $post->ID, '_booking_phone', true );
		$date    = get_post_meta( $post->ID, '_booking_date', true );
		$time    = get_post_meta( $post->ID, '_booking_time', true );
		$message = get_post_meta( $post->ID, '_booking_message', true );

		wp_nonce_field( 'save_booking_meta', 'booking_meta_nonce' );
		?>
		<table class="form-table">
			<tr>
				<th><label for="booking_name"><?php esc_html_e( 'Name', 'wp-booking-plugin' ); ?></label></th>
				<td><input type="text" id="booking_name" name="booking_name" value="<?php echo esc_attr( $name ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="booking_email"><?php esc_html_e( 'Email', 'wp-booking-plugin' ); ?></label></th>
				<td><input type="email" id="booking_email" name="booking_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="booking_phone"><?php esc_html_e( 'Phone', 'wp-booking-plugin' ); ?></label></th>
				<td><input type="tel" id="booking_phone" name="booking_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="booking_date"><?php esc_html_e( 'Date', 'wp-booking-plugin' ); ?></label></th>
				<td><input type="date" id="booking_date" name="booking_date" value="<?php echo esc_attr( $date ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="booking_time"><?php esc_html_e( 'Time', 'wp-booking-plugin' ); ?></label></th>
				<td><input type="time" id="booking_time" name="booking_time" value="<?php echo esc_attr( $time ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="booking_message"><?php esc_html_e( 'Message', 'wp-booking-plugin' ); ?></label></th>
				<td><textarea id="booking_message" name="booking_message" rows="5" class="large-text"><?php echo esc_textarea( $message ); ?></textarea></td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render booking actions meta box.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_booking_actions_meta_box( $post ) {
		$status = get_post_status( $post->ID );
		?>
		<div class="booking-actions-meta-box">
			<p><strong><?php esc_html_e( 'Current Status:', 'wp-booking-plugin' ); ?></strong></p>
			<p>
				<?php
				switch ( $status ) {
					case 'pending_approval':
						echo '<span class="status-pending">' . esc_html__( 'Pending Approval', 'wp-booking-plugin' ) . '</span>';
						break;
					case 'approved':
						echo '<span class="status-approved">' . esc_html__( 'Approved', 'wp-booking-plugin' ) . '</span>';
						break;
					case 'rejected':
						echo '<span class="status-rejected">' . esc_html__( 'Rejected', 'wp-booking-plugin' ) . '</span>';
						break;
				}
				?>
			</p>

			<?php if ( 'pending_approval' === $status ) : ?>
				<p>
					<?php
					$approve_url = wp_nonce_url(
						admin_url( 'admin-post.php?action=approve_booking&booking_id=' . $post->ID ),
						'approve_booking_' . $post->ID
					);
					$reject_url  = wp_nonce_url(
						admin_url( 'admin-post.php?action=reject_booking&booking_id=' . $post->ID ),
						'reject_booking_' . $post->ID
					);
					?>
					<a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary button-large" style="width: 100%; text-align: center; margin-bottom: 10px;">
						<?php esc_html_e( 'Approve Booking', 'wp-booking-plugin' ); ?>
					</a>
					<br>
					<a href="<?php echo esc_url( $reject_url ); ?>" class="button button-large" style="width: 100%; text-align: center;">
						<?php esc_html_e( 'Reject Booking', 'wp-booking-plugin' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Save booking meta data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_booking_meta( $post_id, $post ) {
		// Check nonce
		if ( ! isset( $_POST['booking_meta_nonce'] ) || ! wp_verify_nonce( $_POST['booking_meta_nonce'], 'save_booking_meta' ) ) {
			return;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save meta fields
		$fields = array( 'booking_name', 'booking_email', 'booking_phone', 'booking_date', 'booking_time', 'booking_message' );

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
			}
		}
	}

	/**
	 * Handle booking approval.
	 */
	public function approve_booking() {
		if ( ! isset( $_GET['booking_id'] ) ) {
			wp_die( esc_html__( 'Invalid booking ID.', 'wp-booking-plugin' ) );
		}

		$booking_id = absint( $_GET['booking_id'] );

		// Verify nonce
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'approve_booking_' . $booking_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'wp-booking-plugin' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $booking_id ) ) {
			wp_die( esc_html__( 'You do not have permission to approve this booking.', 'wp-booking-plugin' ) );
		}

		// Update post status
		wp_update_post(
			array(
				'ID'          => $booking_id,
				'post_status' => 'approved',
			)
		);

		// Send approval email
		WP_Booking_Email::send_approval_email( $booking_id );

		// Redirect back
		wp_safe_redirect( admin_url( 'edit.php?post_type=wp_booking&approved=1' ) );
		exit;
	}

	/**
	 * Handle booking rejection.
	 */
	public function reject_booking() {
		if ( ! isset( $_GET['booking_id'] ) ) {
			wp_die( esc_html__( 'Invalid booking ID.', 'wp-booking-plugin' ) );
		}

		$booking_id = absint( $_GET['booking_id'] );

		// Verify nonce
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'reject_booking_' . $booking_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'wp-booking-plugin' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $booking_id ) ) {
			wp_die( esc_html__( 'You do not have permission to reject this booking.', 'wp-booking-plugin' ) );
		}

		// Update post status
		wp_update_post(
			array(
				'ID'          => $booking_id,
				'post_status' => 'rejected',
			)
		);

		// Send rejection email
		WP_Booking_Email::send_rejection_email( $booking_id );

		// Redirect back
		wp_safe_redirect( admin_url( 'edit.php?post_type=wp_booking&rejected=1' ) );
		exit;
	}
}
