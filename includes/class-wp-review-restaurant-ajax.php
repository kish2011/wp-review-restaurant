<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Review_Restaurant_Ajax class.
 */
class WP_Review_Restaurant_Ajax {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_nopriv_review_restaurant_get_listings', array( $this, 'get_listings' ) );
		add_action( 'wp_ajax_review_restaurant_get_listings', array( $this, 'get_listings' ) );
	}

	/**
	 * Get listings via ajax
	 */
	public function get_listings() {
		global $review_restaurant, $wpdb;

		$result             = array();
		$search_location    = sanitize_text_field( stripslashes( $_POST['search_location'] ) );
		$search_keywords    = sanitize_text_field( stripslashes( $_POST['search_keywords'] ) );
		$search_categories  = isset( $_POST['search_categories'] ) ? $_POST['search_categories'] : '';
		$filter_restaurant_types   = isset( $_POST['filter_restaurant_type'] ) ? array_filter( array_map( 'sanitize_title', (array) $_POST['filter_restaurant_type'] ) ) : null;
		$filter_restaurant_types_cuisine   = isset( $_POST['filter_restaurant_type_cuisine'] ) ? array_filter( array_map( 'sanitize_title', (array) $_POST['filter_restaurant_type_cuisine'] ) ) : null;
		$filter_restaurant_types_advanced   = isset( $_POST['filter_restaurant_type_advanced'] ) ? array_filter( array_map( 'sanitize_title', (array) $_POST['filter_restaurant_type_advanced'] ) ) : null;

		if ( is_array( $search_categories ) ) {
			$search_categories = array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $search_categories ) ) );
		} else {
			$search_categories = array_filter( array( sanitize_text_field( stripslashes( $search_categories ) ) ) );
		}

		$args = array(
			'search_location'    => $search_location,
			'search_keywords'    => $search_keywords,
			'search_categories'  => $search_categories,
			'restaurant_types'   => is_null( $filter_restaurant_types ) ? '' : $filter_restaurant_types + array( 0 ),
			'restaurant_types_cuisine'   => is_null( $filter_restaurant_types_cuisine ) ? '' : $filter_restaurant_types_cuisine + array( 0 ),
			'restaurant_types_advanced'   => is_null( $filter_restaurant_types_advanced ) ? '' : $filter_restaurant_types_advanced + array( 0 ),
			'orderby'            => sanitize_text_field( $_POST['orderby'] ),
			'order'              => sanitize_text_field( $_POST['order'] ),
			'offset'             => ( absint( $_POST['page'] ) - 1 ) * absint( $_POST['per_page'] ),
			'posts_per_page'     => absint( $_POST['per_page'] )
		);

		if ( isset( $_POST['featured'] ) && ( $_POST['featured'] === 'true' || $_POST['featured'] === 'false' ) ) {
			$args['featured'] = $_POST['featured'] === 'true' ? true : false;
		}

		ob_start();
		
		$restaurants = get_restaurant_listings( apply_filters( 'review_restaurant_get_listings_args', $args ) );

		$result['found_restaurants'] = false;

		if ( $restaurants->have_posts() ) : $result['found_restaurants'] = true; ?>

			<?php while ( $restaurants->have_posts() ) : $restaurants->the_post(); ?>

				<?php get_review_restaurant_template_part( 'content', 'restaurant_listing' ); ?>

			<?php endwhile; ?>

		<?php else : ?>

			<?php get_review_restaurant_template_part( 'content', 'no-restaurants-found' ); ?>

		<?php endif;

		$result['html'] = ob_get_clean();

		// Generate 'showing' text
		$types = get_restaurant_listing_types();

		if ( sizeof( $filter_restaurant_types ) > 0 && ( sizeof( $filter_restaurant_types ) !== sizeof( $types ) || $search_keywords || $search_location || $search_categories || apply_filters( 'review_restaurant_get_listings_custom_filter', false ) ) ) {
			$showing_types = array();
			$unmatched     = false;

			foreach ( $types as $type ) {
				if ( in_array( $type->slug, $filter_restaurant_types ) )
					$showing_types[] = $type->name;
				else
					$unmatched = true;
			}

			if ( ! $unmatched )
				$showing_types  = '';
			elseif ( sizeof( $showing_types ) == 1 ) {
				$showing_types  = implode( ', ', $showing_types ) . ' ';
			} else {
				$last           = array_pop( $showing_types );
				$showing_types  = implode( ', ', $showing_types );
				$showing_types .= " &amp; $last ";
			}

			$showing_categories = array();

			if ( $search_categories ) {
				foreach ( $search_categories as $category ) {
					if ( ! is_numeric( $category ) ) {
						$category_object = get_term_by( 'slug', $category, 'restaurant_listing_category' );
					} 
					if ( is_numeric( $category ) || is_wp_error( $category_object ) || ! $category_object ) {
						$category_object = get_term_by( 'id', $category, 'restaurant_listing_category' );
					}
					if ( ! is_wp_error( $category_object ) ) {
						$showing_categories[] = $category_object->name;
					}
				}
			}

			if ( $search_keywords ) {
				$showing_restaurants  = sprintf( __( 'Showing %s&ldquo;%s&rdquo; %srestaurants', 'wp-review-restaurant' ), $showing_types, $search_keywords, implode( ', ', $showing_categories ) );
			} else {
				$showing_restaurants  = sprintf( __( 'Showing all %s%srestaurants', 'wp-review-restaurant' ), $showing_types, implode( ', ', $showing_categories ) . ' ' );
			}

			$showing_location  = $search_location ? sprintf( ' ' . __( 'located in &ldquo;%s&rdquo;', 'wp-review-restaurant' ), $search_location ) : '';

			$result['showing'] = apply_filters( 'review_restaurant_get_listings_custom_filter_text', $showing_restaurants . $showing_location );

		} else {
			$result['showing'] = '';
		}

		// Generate RSS link
		$result['showing_links'] = review_restaurant_get_filtered_links( array(
			'filter_restaurant_types'  => $filter_restaurant_types,
			'filter_restaurant_types_cuisine'  => $filter_restaurant_types_cuisine,
			'filter_restaurant_types_advanced'  => $filter_restaurant_types_advanced,
			'search_location'   => $search_location,
			'search_categories' => $search_categories,
			'search_keywords'   => $search_keywords
		) );

		$result['max_num_pages'] = $restaurants->max_num_pages;

		echo '<!--WPRR-->';
		echo json_encode( apply_filters( 'review_restaurant_get_listings_result', $result ) );
		echo '<!--WPRR_END-->';

		die();
	}
}

new WP_Review_Restaurant_Ajax();