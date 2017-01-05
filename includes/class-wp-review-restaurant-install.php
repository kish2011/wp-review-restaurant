<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Review_Restaurant_Install
 */
class WP_Review_Restaurant_Install {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->init_user_roles();
		$this->default_terms();
		$this->cron();
		delete_transient( 'wp_review_restaurant_addons_html' );
		update_option( 'wp_review_restaurant_version', REVIEW_RESTAURANT_VERSION );
	}

	/**
	 * Init user roles
	 *
	 * @access public
	 * @return void
	 */
	public function init_user_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles();

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'administrator', 'manage_restaurant_listings' );

			add_role( 'restaurant_administrator', __( 'Restaurant Administrator', 'wp-review-restaurant' ), array(
			    'read' 						=> true,
			    'edit_posts' 				=> false,
			    'delete_posts' 				=> false
			) );
		}
	}

	/**
	 * default_terms function.
	 *
	 * @access public
	 * @return void
	 */
	public function default_terms() {
		if ( get_option( 'review_restaurant_installed_terms' ) == 1 ) {
			return;
		}

		$taxonomies = array(
			'restaurant_listing_type' => array(
				'Restaurants & Bars',
				'Desserts',
				'Coffee & Tea',
				'Specialty Foods',
				'Street Foods',
				'Others'
			),
			'restaurant_listing_type_cuisine' => array(
				'North Indian',
				'South Indian',
				'Chinese',
				'Fast Food',
				'Desserts'
			),
			'restaurant_listing_type_location' => array(
				'Bangalore',
				'Mumbai',
				'Delhi NCR',
				'Hyderabad',
				'Bhubaneswar'
			),
			'restaurant_listing_type_advanced' => array(
				'Luxury Dining',
				'Bar present',
				'Pure veg',
				'Credit cards',
				'Buffet',
				'Happy hours',
				'Wifi',
				'Breakfast',
				'Sunday Brunch',
				'Desserts and Bakes'
			)
		);

		foreach ( $taxonomies as $taxonomy => $terms ) {
			foreach ( $terms as $term ) {
				if ( ! get_term_by( 'slug', sanitize_title( $term ), $taxonomy ) ) {
					wp_insert_term( $term, $taxonomy );
				}
			}
		}

		update_option( 'review_restaurant_installed_terms', 1 );
	}

	/**
	 * Setup cron restaurants
	 */
	public function cron() {
		wp_clear_scheduled_hook( 'review_restaurant_check_for_expired_restaurants' );
		wp_clear_scheduled_hook( 'review_restaurant_delete_old_previews' );
		wp_schedule_event( time(), 'hourly', 'review_restaurant_check_for_expired_restaurants' );
		wp_schedule_event( time(), 'daily', 'review_restaurant_delete_old_previews' );
	}
}

new WP_Review_Restaurant_Install();