<?php
/**
 * Plugin Name: WP Booking Plugin
 * Plugin URI: https://example.com/wp-booking-plugin
 * Description: A Calendly-like booking system for WordPress with admin approval workflow
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-booking-plugin
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin version.
 */
define( 'WP_BOOKING_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'WP_BOOKING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'WP_BOOKING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_wp_booking_plugin() {
	require_once WP_BOOKING_PLUGIN_DIR . 'includes/class-wp-booking-activator.php';
	WP_Booking_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_booking_plugin() {
	require_once WP_BOOKING_PLUGIN_DIR . 'includes/class-wp-booking-deactivator.php';
	WP_Booking_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_booking_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_wp_booking_plugin' );

/**
 * The core plugin class.
 */
require WP_BOOKING_PLUGIN_DIR . 'includes/class-wp-booking.php';

/**
 * Begins execution of the plugin.
 */
function run_wp_booking_plugin() {
	$plugin = new WP_Booking();
	$plugin->run();
}

run_wp_booking_plugin();
