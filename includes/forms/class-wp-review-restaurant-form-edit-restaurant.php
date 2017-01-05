<?php

include_once( 'class-wp-review-restaurant-form-submit-restaurant.php' );

/**
 * WP_Review_Restaurant_Form_Edit_Restaurant class.
 */
class WP_Review_Restaurant_Form_Edit_Restaurant extends WP_Review_Restaurant_Form_Submit_Restaurant {

	public static $form_name = 'edit-restaurant';

	/**
	 * Constructor
	 */
	public static function init() {
		self::$restaurant_id = ! empty( $_REQUEST['restaurant_id'] ) ? absint( $_REQUEST[ 'restaurant_id' ] ) : 0;

		if  ( ! review_restaurant_user_can_edit_restaurant( self::$restaurant_id ) ) {
			self::$restaurant_id = 0;
		}
	}

	/**
	 * output function.
	 *
	 * @access public
	 * @return void
	 */
	public static function output() {
		self::submit_handler();
		self::submit();
	}

	/**
	 * Submit Step
	 */
	public static function submit() {
		global $review_restaurant, $post;

		$restaurant = get_post( self::$restaurant_id );

		if ( empty( self::$restaurant_id  ) || $restaurant->post_status !== 'publish' ) {
			echo wpautop( __( 'Invalid restaurant', 'wp-review-restaurant' ) );
			return;
		}

		self::init_fields();

		foreach ( self::$fields as $group_key => $fields ) {
			foreach ( $fields as $key => $field ) {
				switch ( $key ) {
					case 'restaurant_title' :
						if ( ! isset( self::$fields[ $group_key ][ $key ]['value'] ) )
							self::$fields[ $group_key ][ $key ]['value'] = $restaurant->post_title;
					break;
					case 'restaurant_description' :
						if ( ! isset( self::$fields[ $group_key ][ $key ]['value'] ) )
							self::$fields[ $group_key ][ $key ]['value'] = $restaurant->post_content;
					break;
					case 'restaurant_type' :
						if ( ! isset( self::$fields[ $group_key ][ $key ]['value'] ) )
							self::$fields[ $group_key ][ $key ]['value'] = current( wp_get_object_terms( $restaurant->ID, 'restaurant_listing_type', array( 'fields' => 'slugs' ) ) );
					break;
					case 'restaurant_category' :
						if ( ! isset( self::$fields[ $group_key ][ $key ]['value'] ) )
							self::$fields[ $group_key ][ $key ]['value'] = current( wp_get_object_terms( $restaurant->ID, 'restaurant_listing_category', array( 'fields' => 'slugs' ) ) );
					break;
					default:
						if ( ! isset( self::$fields[ $group_key ][ $key ]['value'] ) )
							self::$fields[ $group_key ][ $key ]['value'] = get_post_meta( $restaurant->ID, '_' . $key, true );
					break;
				}
			}
		}

		self::$fields = apply_filters( 'submit_restaurant_form_fields_get_restaurant_data', self::$fields, $restaurant );

		wp_enqueue_script( 'wp-review-restaurant-restaurant-submission' );
		
		get_review_restaurant_template( 'restaurant-submit.php', array(
			'form'               => self::$form_name,
			'restaurant_id'             => self::get_restaurant_id(),
			'action'             => self::get_action(),
			'restaurant_fields'         => self::get_fields( 'restaurant' ),
			'restaurant_fields'     => self::get_fields( 'restaurant' ),
			'submit_button_text' => __( 'Update restaurant listing', 'wp-review-restaurant' )
			) );
	}

	/**
	 * Submit Step is posted
	 */
	public static function submit_handler() {
		if ( empty( $_POST['submit_restaurant'] ) ) {
			return;
		}

		try {

			// Get posted values
			$values = self::get_posted_fields();

			// Validate required
			if ( is_wp_error( ( $return = self::validate_fields( $values ) ) ) ) {
				throw new Exception( $return->get_error_message() );
			}

			// Update the restaurant
			self::save_restaurant( $values['restaurant']['restaurant_title'], $values['restaurant']['restaurant_description'], 'publish', $values );
			self::update_restaurant_data( $values );

			// Successful
			echo '<div class="review-restaurant-message">' . __( 'Your changes have been saved.', 'wp-review-restaurant' ), ' <a href="' . get_permalink( self::$restaurant_id ) . '">' . __( 'View Restaurant Listing &rarr;', 'wp-review-restaurant' ) . '</a>' . '</div>';

		} catch ( Exception $e ) {
			echo '<div class="review-restaurant-error">' . $e->getMessage() . '</div>';
			return;
		}
	}
}