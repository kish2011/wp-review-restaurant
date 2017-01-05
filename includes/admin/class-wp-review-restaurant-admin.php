<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Review_Restaurant_Admin class.
 */
class WP_Review_Restaurant_Admin {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		include_once( 'class-wp-review-restaurant-cpt.php' );
		include_once( 'class-wp-review-restaurant-settings.php' );
		include_once( 'class-wp-review-restaurant-writepanels.php' );

		$this->settings_page = new WP_Review_Restaurant_Settings();

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * admin_enqueue_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		global $review_restaurant, $wp_scripts;

		$screen = get_current_screen();

		if ( in_array( $screen->id, array( 'edit-restaurant_listing', 'restaurant_listing', 'restaurant_listing_page_review-restaurant-settings', 'restaurant_listing_page_review-restaurant-addons' ) ) ) {
			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

			wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'review_restaurant_admin_css', REVIEW_RESTAURANT_PLUGIN_URL . '/assets/css/admin.css' );
			wp_register_script( 'jquery-tiptip', REVIEW_RESTAURANT_PLUGIN_URL. '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), REVIEW_RESTAURANT_VERSION, true );
			wp_enqueue_script( 'review_restaurant_admin_js', REVIEW_RESTAURANT_PLUGIN_URL. '/assets/js/admin.min.js', array( 'jquery', 'jquery-tiptip', 'jquery-ui-datepicker' ), REVIEW_RESTAURANT_VERSION, true );
		}

		wp_enqueue_style( 'review_restaurant_admin_menu_css', REVIEW_RESTAURANT_PLUGIN_URL . '/assets/css/menu.css' );
	}

	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu() {
		add_submenu_page( 'edit.php?post_type=restaurant_listing', __( 'Settings', 'wp-review-restaurant' ), __( 'Settings', 'wp-review-restaurant' ), 'manage_options', 'review-restaurant-settings', array( $this->settings_page, 'output' ) );

		/* if ( apply_filters( 'review_restaurant_show_addons_page', true ) )
			add_submenu_page(  'edit.php?post_type=restaurant_listing', __( 'WP Review Restaurant Add-ons', 'wp-review-restaurant' ),  __( 'Add-ons', 'wp-review-restaurant' ) , 'manage_options', 'review-restaurant-addons', array( $this, 'addons_page' ) );
		*/
	}

	/**
	 * Output addons page
	 */
	public function addons_page() {
		$addons = include( 'class-wp-review-restaurant-addons.php' );
		$addons->output();
	}
}

new WP_Review_Restaurant_Admin();