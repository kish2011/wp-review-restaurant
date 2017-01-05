<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Review_Restaurant_Geocode
 *
 * Obtains Geolocation data for posted restaurants from Google.
 */
class WP_Review_Restaurant_Geocode {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'review_restaurant_update_restaurant_data', array( $this, 'update_location_data' ), 20, 2 );
		add_action( 'review_restaurant_review_location_edited', array( $this, 'change_location_data' ), 20, 2 );
	}

	/**
	 * Update location data - when submitting a restaurant
	 */
	public function update_location_data( $restaurant_id, $values ) {
		if ( apply_filters( 'review_restaurant_geolocation_enabled', true ) ) {
			$address_data = self::get_location_data( $values['restaurant']['restaurant_location'] );
			self::save_location_data( $restaurant_id, $address_data );
		}
	}

	/**
	 * Change a restaurants location data upon editing
	 * @param  int $restaurant_id
	 * @param  string $new_location
	 */
	public function change_location_data( $restaurant_id, $new_location ) {
		if ( apply_filters( 'review_restaurant_geolocation_enabled', true ) ) {
			$address_data = self::get_location_data( $new_location );
			self::clear_location_data( $restaurant_id );
			self::save_location_data( $restaurant_id, $address_data );
		}
	}

	/**
	 * Checks if a restaurant has location data or not
	 * @param  int  $restaurant_id
	 * @return boolean
	 */
	public static function has_location_data( $restaurant_id ) {
		return get_post_meta( $restaurant_id, 'geolocated', true ) == 1;
	}

	/**
	 * Called manually to generate location data and save to a post
	 * @param  int $restaurant_id
	 * @param  string $location
	 */
	public static function generate_location_data( $restaurant_id, $location ) {
		$address_data = self::get_location_data( $location );
		self::save_location_data( $restaurant_id, $address_data );
	}

	/**
	 * Delete a restaurant's location data
	 * @param  int $restaurant_id
	 */
	public static function clear_location_data( $restaurant_id ) {
		delete_post_meta( $restaurant_id, 'geolocated' );
		delete_post_meta( $restaurant_id, 'geolocation_city' );
		delete_post_meta( $restaurant_id, 'geolocation_country_long' );
		delete_post_meta( $restaurant_id, 'geolocation_country_short' );
		delete_post_meta( $restaurant_id, 'geolocation_formatted_address' );
		delete_post_meta( $restaurant_id, 'geolocation_lat' );
		delete_post_meta( $restaurant_id, 'geolocation_long' );
		delete_post_meta( $restaurant_id, 'geolocation_state_long' );
		delete_post_meta( $restaurant_id, 'geolocation_state_short' );
		delete_post_meta( $restaurant_id, 'geolocation_street' );
		delete_post_meta( $restaurant_id, 'geolocation_zipcode' );
	}

	/**
	 * Save any returned data to post meta
	 * @param  int $restaurant_id
	 * @param  array $address_data
	 */
	public static function save_location_data( $restaurant_id, $address_data ) {
		if ( ! is_wp_error( $address_data ) && $address_data ) {
			foreach ( $address_data as $key => $value ) {
				if ( $value ) {
					update_post_meta( $restaurant_id, 'geolocation_' . $key, $value );
				}
			}
			update_post_meta( $restaurant_id, 'geolocated', 1 );
		}
	}

	/**
	 * Get Location Data from Google
	 *
	 * Based on code by Eyal Fitoussi.
	 * 
	 * @param string $raw_address
	 * @return array location data
	 */
	public static function get_location_data( $raw_address ) {
		$invalid_chars = array( " " => "+", "," => "", "?" => "", "&" => "", "=" => "" , "#" => "" );
		$raw_address   = trim( strtolower( str_replace( array_keys( $invalid_chars ), array_values( $invalid_chars ), $raw_address ) ) );

		if ( empty( $raw_address ) ) {
			return false;
		}

		$transient_name   = 'geocode_' . md5( $raw_address );
		$geocoded_address = get_transient( $transient_name );
		$jm_geocode_over_query_limit = get_transient( 'jm_geocode_over_query_limit' );

		// Query limit reached - don't geocode for a while
		if ( $jm_geocode_over_query_limit && false === $geocoded_address ) {
			return false;
		}

		try {
			if ( false === $geocoded_address || empty( $geocoded_address->results[0] ) ) {
				$result = wp_remote_get( 
					"http://maps.googleapis.com/maps/api/geocode/json?address=" . $raw_address . "&sensor=false", 
					array(
						'timeout'     => 5,
					    'redirection' => 1,
					    'httpversion' => '1.1',
					    'user-agent'  => 'WordPress/wp-review-restaurant-' . REVIEW_RESTAURANT_VERSION . '; ' . get_bloginfo( 'url' ),
					    'sslverify'   => false
				    )
				);
				$result           = wp_remote_retrieve_body( $result );
				$geocoded_address = json_decode( $result );

				if ( $geocoded_address->status ) {
					switch ( $geocoded_address->status ) {
						case 'ZERO_RESULTS' :
							throw new Exception( __( "No results found", 'wp-review-restaurant' ) );
						break;
						case 'OVER_QUERY_LIMIT' :
							set_transient( 'jm_geocode_over_query_limit', 1, HOUR_IN_SECONDS );
							throw new Exception( __( "Query limit reached", 'wp-review-restaurant' ) );
						break;
						case 'OK' :
							if ( ! empty( $geocoded_address->results[0] ) ) {
								set_transient( $transient_name, $geocoded_address, 24 * HOUR_IN_SECONDS * 365 );
							} else {
								throw new Exception( __( "Geocoding error", 'wp-review-restaurant' ) );
							}
						break;
						default :
							throw new Exception( __( "Geocoding error", 'wp-review-restaurant' ) );
						break;
					}
				} else {
					throw new Exception( __( "Geocoding error", 'wp-review-restaurant' ) );
				}
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'error', $e->getMessage() );
		}
		
		$address                      = array();
		$address['lat']               = sanitize_text_field( $geocoded_address->results[0]->geometry->location->lat );
		$address['long']              = sanitize_text_field( $geocoded_address->results[0]->geometry->location->lng );
		$address['formatted_address'] = sanitize_text_field( $geocoded_address->results[0]->formatted_address );
		
		if ( ! empty( $geocoded_address->results[0]->address_components ) ) {
			$address_data             = $geocoded_address->results[0]->address_components;
			$street_number            = false;
			$address['street']        = false;
			$address['city']          = false;
			$address['state_short']   = false;
			$address['state_long']    = false;
			$address['zipcode']       = false;
			$address['country_short'] = false;
			$address['country_long']  = false;
			
			foreach ( $address_data as $data ) {
				switch ( $data->types[0] ) {
					case 'street_number' :
						$address['street']        = sanitize_text_field( $data->long_name ); 
					break;
					case 'route' :
						$route = sanitize_text_field( $data->long_name );

						if ( ! empty( $address['street'] ) )	
							$address['street'] = $address['street'] . ' ' . $route;
						else
							$address['street'] = $route;
					break;
					case 'locality' :
						$address['city']          = sanitize_text_field( $data->long_name ); 
					break;
					case 'administrative_area_level_1' :
						$address['state_short']   = sanitize_text_field( $data->short_name ); 
						$address['state_long']    = sanitize_text_field( $data->long_name );
					break;
					case 'postal_code' :
						$address['postcode']      = sanitize_text_field( $data->long_name ); 
					break;
					case 'country' :
						$address['country_short'] = sanitize_text_field( $data->short_name ); 
						$address['country_long']  = sanitize_text_field( $data->long_name );
					break;
				}
			}
		}

		return $address;
	}
}

new WP_Review_Restaurant_Geocode();