<?php
if ( ! function_exists( 'get_restaurant_listings' ) ) :
/**
 * Queries restaurant listings with certain criteria and returns them
 *
 * @access public
 * @return void
 */
function get_restaurant_listings( $args = array() ) {
	global $wpdb;

	$args = wp_parse_args( $args, array(
		'search_location'   => '',
		'search_keywords'   => '',
		'search_categories' => array(),
		'restaurant_types'  => array(),
		'restaurant_types_cuisine'  => array(),
		'offset'            => '',
		'posts_per_page'    => '-1',
		'orderby'           => 'date',
		'order'             => 'DESC',
		'featured'          => null
	) );

	$query_args = array(
		'post_type'           => 'restaurant_listing',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => 1,
		'offset'              => absint( $args['offset'] ),
		'posts_per_page'      => intval( $args['posts_per_page'] ),
		'orderby'             => $args['orderby'],
		'order'               => $args['order'],
		'tax_query'           => array(),
		'meta_query'          => array()
	);

	if ( ! empty( $args['restaurant_types'] ) ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => 'restaurant_listing_type',
			'field'    => 'slug',
			'terms'    => $args['restaurant_types'],
			'operator' => 'AND',
		);
	}

	if ( ! empty( $args['search_categories'] ) ) {
		$field = is_numeric( $args['search_categories'][0] ) ? 'term_id' : 'slug';
		
		$query_args['tax_query'][] = array(
			'taxonomy' => 'restaurant_listing_category',
			'field'    => $field,
			'terms'    => $args['search_categories']
		);
		// $query_args['tax_query'] = array_merge($query_args['tax_query'], array( 'relation' => 'AND', ) );
		$relation = 1;
	}
	
	if ( ! empty( $args['restaurant_types_cuisine'] ) ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => 'restaurant_listing_type_cuisine',
			'field'    => 'slug',
			'terms'    => $args['restaurant_types_cuisine'],
			'operator' => 'AND',
		);
		$relation = 1;
		// $query_args['tax_query'] = array_merge($query_args['tax_query'], array( 'relation' => 'AND', ) );
	}
	
	if ( ! empty( $args['restaurant_types_advanced'] ) ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => 'restaurant_listing_type_advanced',
			'field'    => 'slug',
			'terms'    => $args['restaurant_types_advanced'],
			'operator' => 'AND',
		);
		$relation = 1;
		// $query_args['tax_query'] = array_merge($query_args['tax_query'], array( 'relation' => 'AND', ) );
	}
	
	if ( $relation == 1 ) {
		 $query_args['tax_query'] = array_merge($query_args['tax_query'], array( 'relation' => 'AND', ) );
	}

	if ( get_option( 'review_restaurant_hide_filled_positions' ) == 1 ) {
		$query_args['meta_query'][] = array(
			'key'     => '_filled',
			'value'   => '1',
			'compare' => '!='
		);
	}

	if ( ! is_null( $args['featured'] ) ) {
		$query_args['meta_query'][] = array(
			'key'     => '_featured',
			'value'   => '1',
			'compare' => $args['featured'] ? '=' : '!='
		);
	}

	// Location search - search geolocation data and location meta
	if ( $args['search_location'] ) {
		$location_post_ids = array_merge( $wpdb->get_col( $wpdb->prepare( "
		    SELECT DISTINCT post_id FROM {$wpdb->postmeta}
		    WHERE meta_key IN ( 'geolocation_city', 'geolocation_country_long', 'geolocation_country_short', 'geolocation_formatted_address', 'geolocation_state_long', 'geolocation_state_short', 'geolocation_street', 'geolocation_zipcode', '_restaurant_location' ) 
		    AND meta_value LIKE '%%%s%%'
		", $args['search_location'] ) ), array( 0 ) );
	} else {
		$location_post_ids = array();
	}

	// Keyword search - search meta as well as post content
	if ( $args['search_keywords'] ) {
		$search_keywords              = array_map( 'trim', explode( ',', $args['search_keywords'] ) );
		$posts_search_keywords_sql    = array();
		$postmeta_search_keywords_sql = array();

		foreach ( $search_keywords as $keyword ) {
			$postmeta_search_keywords_sql[] = " meta_value LIKE '%" . esc_sql( $keyword ) . "%' ";
			$posts_search_keywords_sql[]    = " 
				post_title LIKE '%" . esc_sql( $keyword ) . "%' 
				OR post_content LIKE '%" . esc_sql( $keyword ) . "%' 
			";
		}

		$keyword_post_ids = $wpdb->get_col( "
		    SELECT DISTINCT post_id FROM {$wpdb->postmeta}
		    WHERE " . implode( ' OR ', $postmeta_search_keywords_sql ) . "
		" );

		$keyword_post_ids = array_merge( $keyword_post_ids, $wpdb->get_col( "
		    SELECT ID FROM {$wpdb->posts}
		    WHERE ( " . implode( ' OR ', $posts_search_keywords_sql ) . " )
		    AND post_type = 'restaurant_listing'
		    AND post_status = 'publish'
		" ), array( 0 ) );
	} else {
		$keyword_post_ids = array();
	}

	// Merge post ids
	if ( ! empty( $location_post_ids ) && ! empty( $keyword_post_ids ) ) {
		$query_args['post__in'] = array_intersect( $location_post_ids, $keyword_post_ids );
	} elseif ( ! empty( $location_post_ids ) || ! empty( $keyword_post_ids ) ) {
		$query_args['post__in'] = array_merge( $location_post_ids, $keyword_post_ids );
	}

	$query_args = apply_filters( 'review_restaurant_get_listings', $query_args );

	if ( empty( $query_args['meta_query'] ) )
		unset( $query_args['meta_query'] );

	if ( empty( $query_args['tax_query'] ) )
		unset( $query_args['tax_query'] );

	if ( $args['orderby'] == 'featured' ) {
		$query_args['orderby'] = 'meta_key';
		$query_args['meta_key'] = '_featured';
		add_filter( 'posts_clauses', 'order_featured_restaurant_listing' );
	}

	// Filter args
	$query_args = apply_filters( 'get_restaurant_listings_query_args', $query_args );

	do_action( 'before_get_restaurant_listings', $query_args );

	$result = new WP_Query( $query_args );

	do_action( 'after_get_restaurant_listings', $query_args );

	remove_filter( 'posts_clauses', 'order_featured_restaurant_listing' );

	return $result;
}
endif;

if ( ! function_exists( 'order_featured_restaurant_listing' ) ) :
	/**
	 * WP Core doens't let us change the sort direction for invidual orderby params - http://core.trac.wordpress.org/ticket/17065
	 *
	 * @access public
	 * @param array $args
	 * @return array
	 */
	function order_featured_restaurant_listing( $args ) {
		global $wpdb;

		$args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_date DESC";

		return $args;
	}
endif;

if ( ! function_exists( 'get_featured_restaurant_ids' ) ) :
/**
 * Gets the ids of featured restaurants.
 *
 * @access public
 * @return array
 */
function get_featured_restaurant_ids() {
	return get_posts( array(
		'posts_per_page' => -1,
		'post_type'      => 'restaurant_listing',
		'post_status'    => 'publish',
		'meta_key'       => '_featured',
		'meta_value'     => '1',
		'fields'         => 'ids'
	) );
}
endif;

if ( ! function_exists( 'get_restaurant_listing_types' ) ) :
/**
 * Outputs a form to submit a new restaurant to the site from the frontend.
 *
 * @access public
 * @return array
 */
function get_restaurant_listing_types( $fields = 'all' ) {
	return get_terms( "restaurant_listing_type", array(
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => false,
		'fields'     => $fields
	) );
}
endif;

if ( ! function_exists( 'get_restaurant_listing_types_cuisine' ) ) :
/**
 * Outputs a form to submit a new restaurant to the site from the frontend.
 *
 * @access public
 * @return array
 */
function get_restaurant_listing_types_cuisine( $fields = 'all' ) {
	return get_terms( "restaurant_listing_type_cuisine", array(
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => false,
		'fields'     => $fields
	) );
}
endif;

if ( ! function_exists( 'get_restaurant_listing_types_advanced' ) ) :
/**
 * Outputs a form to submit a new restaurant to the site from the frontend.
 *
 * @access public
 * @return array
 */
function get_restaurant_listing_types_advanced( $fields = 'all' ) {
	return get_terms( "restaurant_listing_type_advanced", array(
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => false,
		'fields'     => $fields
	) );
}
endif;

if ( ! function_exists( 'get_restaurant_listing_categories' ) ) :
/**
 * Outputs a form to submit a new restaurant to the site from the frontend.
 *
 * @access public
 * @return array
 */
function get_restaurant_listing_categories() {
	if ( ! get_option( 'review_restaurant_enable_categories' ) )
		return array();

	return get_terms( "restaurant_listing_category", array(
		'orderby'       => 'name',
	    'order'         => 'ASC',
	    'hide_empty'    => false,
	) );
}
endif;

if ( ! function_exists( 'review_restaurant_get_filtered_links' ) ) :
/**
 * Shows links after filtering restaurants
 */
function review_restaurant_get_filtered_links( $args = array() ) {

	$links = apply_filters( 'review_restaurant_review_filters_showing_restaurants_links', array(
		'reset' => array(
			'name' => __( 'Reset', 'wp-review-restaurant' ),
			'url'  => '#'
		),
		'rss_link' => array(
			'name' => __( 'RSS', 'wp-review-restaurant' ),
			'url'  => get_restaurant_listing_rss_link( apply_filters( 'review_restaurant_get_listings_custom_filter_rss_args', array(
				'type'           => isset( $args['filter_restaurant_types'] ) ? implode( ',', $args['filter_restaurant_types'] ) : '',
				'location'       => $args['search_location'],
				'restaurant_categories' => implode( ',', $args['search_categories'] ),
				's'              => $args['search_keywords'],
			) ) )
		)
	), $args );

	$return = '';

	foreach ( $links as $key => $link ) {
		$return .= '<a href="' . esc_url( $link['url'] ) . '" class="' . esc_attr( $key ) . '">' . $link['name'] . '</a>';
	}

	return $return;
}
endif;

if ( ! function_exists( 'get_restaurant_listing_rss_link' ) ) :
/**
 * Get the Restaurant Listing RSS link
 *
 * @return string
 */
function get_restaurant_listing_rss_link( $args = array() ) {
	$rss_link = add_query_arg( array_merge( array( 'feed' => 'restaurant_feed' ), $args ), home_url() );

	return $rss_link;
}
endif;

if ( ! function_exists( 'review_restaurant_create_account' ) ) :
/**
 * Handle account creation.
 *
 * @param  string $account_email
 * @param  string $role 
 * @return WP_error | bool was an account created?
 */
function wp_review_restaurant_create_account( $account_email, $role = '' ) {
	global  $current_user;

	$user_email = apply_filters( 'user_registration_email', sanitize_email( $account_email ) );

	if ( empty( $user_email ) )
		return false;

	if ( ! is_email( $user_email ) )
		return new WP_Error( 'validation-error', __( 'Your email address isn&#8217;t correct.', 'wp-review-restaurant' ) );

	if ( email_exists( $user_email ) )
		return new WP_Error( 'validation-error', __( 'This email is already registered, please choose another one.', 'wp-review-restaurant' ) );

	// Email is good to go - use it to create a user name
	$username = sanitize_user( current( explode( '@', $user_email ) ) );
	$password = wp_generate_password();

	// Ensure username is unique
	$append     = 1;
	$o_username = $username;

	while( username_exists( $username ) ) {
		$username = $o_username . $append;
		$append ++;
	}

	// Final error check
	$reg_errors = new WP_Error();
	do_action( 'register_post', $username, $user_email, $reg_errors );
	$reg_errors = apply_filters( 'registration_errors', $reg_errors, $username, $user_email );

	if ( $reg_errors->get_error_code() )
		return $reg_errors;

	// Create account
	$new_user = array(
		'user_login' => $username,
		'user_pass'  => $password,
		'user_email' => $user_email,
		'role'       => $role ? $role : get_option( 'default_role' )
    );

    $user_id = wp_insert_user( apply_filters( 'review_restaurant_create_account_data', $new_user ) );

    if ( is_wp_error( $user_id ) )
    	return $user_id;

    // Notify
    wp_new_user_notification( $user_id, $password );

	// Login
    wp_set_auth_cookie( $user_id, true, is_ssl() );
    $current_user = get_user_by( 'id', $user_id );

    return true;
}
endif;

/**
 * True if an the user can post a restaurant. If accounts are required, and reg is enabled, users can post (they signup at the same time).
 *
 * @return bool
 */
function review_restaurant_user_can_post_restaurant() {
	$can_post = true;

	if ( ! is_user_logged_in() ) {
		if ( review_restaurant_user_requires_account() && ! review_restaurant_enable_registration() ) {
			$can_post = false;
		}
	}

	return apply_filters( 'review_restaurant_user_can_post_restaurant', $can_post );
}

/**
 * True if an the user can edit a restaurant.
 *
 * @return bool
 */
function review_restaurant_user_can_edit_restaurant( $restaurant_id ) {
	$can_edit = true;
	$restaurant      = get_post( $restaurant_id );

	if ( ! is_user_logged_in() ) {
		$can_edit = false;
	} elseif ( $restaurant->post_author != get_current_user_id() ) {
		$can_edit = false;
	}

	return apply_filters( 'review_restaurant_user_can_edit_restaurant', $can_edit, $restaurant_id );
}

/**
 * True if registration is enabled.
 *
 * @return bool
 */
function review_restaurant_enable_registration() {
	return apply_filters( 'review_restaurant_enable_registration', get_option( 'review_restaurant_enable_registration' ) == 1 ? true : false );
}

/**
 * True if an account is required to post a restaurant.
 *
 * @return bool
 */
function review_restaurant_user_requires_account() {
	return apply_filters( 'review_restaurant_user_requires_account', get_option( 'review_restaurant_user_requires_account' ) == 1 ? true : false );
}