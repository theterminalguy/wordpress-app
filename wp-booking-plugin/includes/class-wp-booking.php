<?php
/**
 * The core plugin class
 *
 * @package WP_Booking_Plugin
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class WP_Booking {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @var WP_Booking_Loader
	 */
	protected $loader;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		// The class responsible for orchestrating the actions and filters
		require_once WP_BOOKING_PLUGIN_DIR . 'includes/class-wp-booking-loader.php';

		// The class responsible for defining internationalization
		require_once WP_BOOKING_PLUGIN_DIR . 'includes/class-wp-booking-i18n.php';

		// The class responsible for the custom post type
		require_once WP_BOOKING_PLUGIN_DIR . 'includes/class-wp-booking-post-type.php';

		// Admin-specific functionality
		require_once WP_BOOKING_PLUGIN_DIR . 'admin/class-wp-booking-admin.php';
		require_once WP_BOOKING_PLUGIN_DIR . 'admin/class-wp-booking-settings.php';

		// Public-facing functionality
		require_once WP_BOOKING_PLUGIN_DIR . 'public/class-wp-booking-public.php';
		require_once WP_BOOKING_PLUGIN_DIR . 'public/class-wp-booking-form.php';

		// Email notifications
		require_once WP_BOOKING_PLUGIN_DIR . 'includes/class-wp-booking-email.php';

		$this->loader = new WP_Booking_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 */
	private function set_locale() {
		$plugin_i18n = new WP_Booking_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new WP_Booking_Admin();
		$plugin_settings = new WP_Booking_Settings();
		$plugin_post_type = new WP_Booking_Post_Type();

		// Register custom post type
		$this->loader->add_action( 'init', $plugin_post_type, 'register_post_type' );

		// Enqueue admin styles and scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Admin menu
		$this->loader->add_action( 'admin_menu', $plugin_settings, 'add_settings_page' );

		// Custom columns in booking list
		$this->loader->add_filter( 'manage_wp_booking_posts_columns', $plugin_admin, 'set_custom_columns' );
		$this->loader->add_action( 'manage_wp_booking_posts_custom_column', $plugin_admin, 'custom_column_content', 10, 2 );

		// Meta boxes
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_booking_meta_boxes' );
		$this->loader->add_action( 'save_post_wp_booking', $plugin_admin, 'save_booking_meta', 10, 2 );

		// Booking approval actions
		$this->loader->add_action( 'admin_post_approve_booking', $plugin_admin, 'approve_booking' );
		$this->loader->add_action( 'admin_post_reject_booking', $plugin_admin, 'reject_booking' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 */
	private function define_public_hooks() {
		$plugin_public = new WP_Booking_Public();
		$plugin_form = new WP_Booking_Form();

		// Enqueue public styles and scripts
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Register shortcode
		$this->loader->add_shortcode( 'wp_booking_form', $plugin_form, 'render_form' );

		// AJAX handlers for public form
		$this->loader->add_action( 'wp_ajax_get_available_slots', $plugin_form, 'ajax_get_available_slots' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_available_slots', $plugin_form, 'ajax_get_available_slots' );
		$this->loader->add_action( 'wp_ajax_submit_booking', $plugin_form, 'ajax_submit_booking' );
		$this->loader->add_action( 'wp_ajax_nopriv_submit_booking', $plugin_form, 'ajax_submit_booking' );
	}

	/**
	 * Run the loader to execute all of the hooks.
	 */
	public function run() {
		$this->loader->run();
	}
}
