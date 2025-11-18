<?php
/**
 * Fired during plugin deactivation
 *
 * @package WP_Booking_Plugin
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class WP_Booking_Deactivator {

	/**
	 * Plugin deactivation handler.
	 *
	 * Flushes rewrite rules to remove custom post type permalinks.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
