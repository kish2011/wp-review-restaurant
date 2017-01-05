<?php
/*
Plugin Name: WP Review Restaurant
Plugin URI: http://opentuteplus.com/wp-review-restaurant
Description: Manage restaurant review from the WordPress admin panel, and allow users to post reviews directly to your listed restaurant.
Version: 1.4
Author: Kishore
Author URI: http://blog.kishorechandra.co.in/
Requires at least: 3.8
Tested up to: 4.6
Text Domain: wp-review-restaurant
Domain Path: /languages

Copyright: 2014 Kishore Sahoo
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * WP_Review_Restaurant class.
 */
class WP_Review_Restaurant {

	/**
	 * Constructor - get the plugin hooked in and ready
	 */
	public function __construct() {
		// Define constants
		define( 'REVIEW_RESTAURANT_VERSION', '1.4' );
		define( 'REVIEW_RESTAURANT_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'REVIEW_RESTAURANT_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

		// Includes
		include( 'wp-review-restaurant-functions.php' );
		include( 'wp-review-restaurant-template.php' );
		include( 'includes/class-wp-review-restaurant-post-types.php' );
		include( 'includes/class-wp-review-restaurant-ajax.php' );
		include( 'includes/class-wp-review-restaurant-shortcodes.php' );
		include( 'includes/class-wp-review-restaurant-api.php' );
		include( 'includes/class-wp-review-restaurant-forms.php' );
		include( 'includes/class-wp-review-restaurant-geocode.php' );
		include( 'includes/class-wp-review-restaurant-rating.php' );
		include( 'includes/class-wp-review-restaurant-colors.php' );

		if ( is_admin() ) {
			include( 'includes/admin/class-wp-review-restaurant-admin.php' );
		}

		// Init classes
		$this->forms      = new WP_Review_Restaurant_Forms();
		$this->post_types = new WP_Review_Restaurant_Post_Types();

		// Activation - works with symlinks
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this->post_types, 'register_post_types' ), 10 );
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), create_function( "", "include_once( 'includes/class-wp-review-restaurant-install.php' );" ), 10 );
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), 'flush_rewrite_rules', 15 );

		// Actions
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'switch_theme', array( $this->post_types, 'register_post_types' ), 10 );
		add_action( 'switch_theme', 'flush_rewrite_rules', 15 );
		add_action( 'widgets_init', create_function( "", "include_once( 'includes/class-wp-review-restaurant-widgets.php' );" ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'admin_init', array( $this, 'updater' ) );
	}

	/**
	 * Handle Updates
	 */
	public function updater() {
		if ( version_compare( REVIEW_RESTAURANT_VERSION, get_option( 'wp_review_restaurant_version' ), '>' ) )
			include_once( 'includes/class-wp-review-restaurant-install.php' );
	}

	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wp-review-restaurant', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue scripts and css
	 */
	public function frontend_scripts() {
		wp_register_script( 'wp-review-restaurant-ajax-filters', REVIEW_RESTAURANT_PLUGIN_URL . '/assets/js/ajax-filters.min.js', array( 'jquery' ), REVIEW_RESTAURANT_VERSION, true );
		wp_register_script( 'wp-review-restaurant-restaurant-dashboard', REVIEW_RESTAURANT_PLUGIN_URL . '/assets/js/restaurant-dashboard.min.js', array( 'jquery' ), REVIEW_RESTAURANT_VERSION, true );
		wp_register_script( 'wp-review-restaurant-restaurant-application', REVIEW_RESTAURANT_PLUGIN_URL . '/assets/js/restaurant-application.min.js', array( 'jquery' ), REVIEW_RESTAURANT_VERSION, true );
		wp_register_script( 'wp-review-restaurant-restaurant-submission', REVIEW_RESTAURANT_PLUGIN_URL . '/assets/js/restaurant-submission.min.js', array( 'jquery' ), REVIEW_RESTAURANT_VERSION, true );

		wp_localize_script( 'wp-review-restaurant-ajax-filters', 'review_restaurant_ajax_filters', array(
			'ajax_url' => admin_url('admin-ajax.php')
		) );
		wp_localize_script( 'wp-review-restaurant-restaurant-dashboard', 'review_restaurant_review_dashboard', array(
			'i18n_confirm_delete' => __( 'Are you sure you want to delete this restaurant?', 'wp-review-restaurant' )
		) );

		wp_enqueue_style( 'wp-review-restaurant-frontend', REVIEW_RESTAURANT_PLUGIN_URL . '/assets/css/frontend.css' );
	}
}

$GLOBALS['review_restaurant'] = new WP_Review_Restaurant();