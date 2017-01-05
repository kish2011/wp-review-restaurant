<?php
/**
 * Template Functions
 *
 * Template functions specifically created for restaurant listings
 *
 * @author 		Kishore
 * @category 	Core
 * @package 	Restaurant Manager/Template
 * @version     1.0.0
 */

/**
 * Get and include template files.
 *
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return void
 */
function get_review_restaurant_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array($args) )
		extract( $args );

	include( locate_review_restaurant_template( $template_name, $template_path, $default_path ) );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @param mixed $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function locate_review_restaurant_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path )
		$template_path = 'review_restaurant';
	if ( ! $default_path )
		$default_path = REVIEW_RESTAURANT_PLUGIN_DIR . '/templates/';

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters( 'review_restaurant_locate_template', $template, $template_name, $template_path );
}

/**
 * Get template part (for templates in loops).
 *
 * @param mixed $slug
 * @param string $name (default: '')
 * @return void
 */
function get_review_restaurant_template_part( $slug, $name = '', $template_path = '', $default_path = '' ) {
	if ( ! $template_path )
		$template_path = 'review_restaurant';
	if ( ! $default_path )
		$default_path = REVIEW_RESTAURANT_PLUGIN_DIR . '/templates/';

	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/review_restaurant/slug-name.php
	if ( $name )
		$template = locate_template( array ( "{$slug}-{$name}.php", "{$template_path}/{$slug}-{$name}.php" ) );

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( $default_path . "{$slug}-{$name}.php" ) )
		$template = $default_path . "{$slug}-{$name}.php";

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/review_restaurant/slug.php
	if ( ! $template )
		$template = locate_template( array( "{$slug}.php", "{$template_path}/{$slug}.php" ) );

	if ( $template )
		load_template( $template, false );
}

/**
 * Outputs the restaurants status
 *
 * @return void
 */
function the_restaurant_status( $post = null ) {
	echo get_the_restaurant_status( $post );
}

/**
 * Gets the restaurants status
 *
 * @return string
 */
function get_the_restaurant_status( $post = null ) {
	$post = get_post( $post );

	$status = $post->post_status;

	if ( $status == 'publish' )
		$status = __( 'Active', 'wp-review-restaurant' );
	elseif ( $status == 'expired' )
		$status = __( 'Expired', 'wp-review-restaurant' );
	elseif ( $status == 'pending' )
		$status = __( 'Pending Review', 'wp-review-restaurant' );
	else
		$status = __( 'Inactive', 'wp-review-restaurant' );

	return apply_filters( 'the_restaurant_status', $status, $post );
}

/**
 * Return whether or not the position has been marked as filled
 *
 * @param  object $post
 * @return boolean
 */
function is_restaurant_filled( $post = null ) {
	$post = get_post( $post );

	return $post->_filled ? true : false;
}

/**
 * Return whether or not the position has been featured
 *
 * @param  object $post
 * @return boolean
 */
function is_restaurant_featured( $post = null ) {
	$post = get_post( $post );

	return $post->_featured ? true : false;
}

/**
 * the_restaurant_permalink function.
 *
 * @access public
 * @return void
 */
function the_restaurant_permalink( $post = null ) {
	echo get_the_restaurant_permalink( $post );
}

/**
 * get_the_restaurant_permalink function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return string
 */
function get_the_restaurant_permalink( $post = null ) {
	$post = get_post( $post );
	$link = get_permalink( $post );

	return apply_filters( 'the_restaurant_permalink', $link, $post );
}

/**
 * get_the_restaurant_application_method function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return object
 */
function get_the_restaurant_application_method( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'restaurant_listing' )
		return;

	$method = new stdClass();
	$enquire  = $post->_application;

	if ( empty( $enquire ) )
		return false;

	if ( strstr( $enquire, '@' ) && is_email( $enquire ) ) {
		$method->type      = 'email';
		$method->raw_email = $enquire;
		$method->email     = antispambot( $enquire );
		$method->subject   = apply_filters( 'review_restaurant_application_email_subject', sprintf( __( 'Restaurant Application via "%s" listing on %s', 'wp-review-restaurant' ), $post->post_title, home_url() ) );
	} else {
		if ( strpos( $enquire, 'http' ) !== 0 )
			$enquire = 'http://' . $enquire;
		$method->type = 'url';
		$method->url  = $enquire;
	}

	return apply_filters( 'the_restaurant_application_method', $method, $post );
}
/**
 * the_restaurant_type function.
 *
 * @access public
 * @return void
 */
function the_restaurant_type( $post = null ) {
	if ( $restaurant_type = get_the_restaurant_type( $post ) )
		echo $restaurant_type->name;
}

/**
 * get_the_restaurant_type function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 */
function get_the_restaurant_type( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'restaurant_listing' )
		return;

	$types = wp_get_post_terms( $post->ID, 'restaurant_listing_type' );

	if ( $types )
		$type = current( $types );
	else
		$type = false;

	return apply_filters( 'the_restaurant_type', $type, $post );
}


/**
 * the_restaurant_location function.
 * @param  boolean $map_link whether or not to link to the map on google maps
 * @return [type]
 */
function the_restaurant_location( $map_link = true, $post = null ) {
	$location = get_the_restaurant_location( $post );

	if ( $location ) {
		if ( $map_link )
			echo apply_filters( 'the_restaurant_location_map_link', '<a class="google_map_link" href="http://maps.google.com/maps?q=' . urlencode( $location ) . '&zoom=14&size=512x512&maptype=roadmap&sensor=false" target="_blank">' . $location . '</a>', $location, $post );
		else
			echo $location;
	} else {
		echo apply_filters( 'the_restaurant_location_anywhere_text', __( 'Anywhere', 'wp-review-restaurant' ) );
	}
}

/**
 * get_the_restaurant_location function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return void
 */
function get_the_restaurant_location( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'restaurant_listing' )
		return;

	return apply_filters( 'the_restaurant_location', $post->_restaurant_location, $post );
}

/**
 * the_restaurant_logo function.
 *
 * @access public
 * @param string $size (default: 'full')
 * @param mixed $default (default: null)
 * @return void
 */
function the_restaurant_logo( $size = 'full', $default = null, $post = null ) {
	global $review_restaurant;

	$logo = get_the_restaurant_logo( $post );

	if ( ! empty( $logo ) && ( strstr( $logo, 'http' ) || file_exists( $logo ) ) ) {

		if ( $size !== 'full' )
			$logo = review_restaurant_get_resized_image( $logo, $size );

		echo '<img class="restaurant_logo" src="' . $logo . '" alt="Logo" />';

	} elseif ( $default )
		echo '<img class="restaurant_logo" src="' . $default . '" alt="Logo" />';
	else
		echo '<img class="restaurant_logo" src="' . REVIEW_RESTAURANT_PLUGIN_URL . '/assets/images/restaurant.png' . '" alt="Logo" />';
}

/**
 * get_the_restaurant_logo function.
 *
 * @access public
 * @param mixed $post (default: null)
 * @return string
 */
function get_the_restaurant_logo( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'restaurant_listing' )
		return;

	return apply_filters( 'the_restaurant_logo', $post->_restaurant_logo, $post );
}

/**
 * Resize and get url of the image
 *
 * @param  string $logo
 * @param  string $size
 * @return string
 */
function review_restaurant_get_resized_image( $logo, $size ) {
	global $_wp_additional_image_sizes;

	if ( $size !== 'full' && isset( $_wp_additional_image_sizes[ $size ] ) ) {

		$img_width  = $_wp_additional_image_sizes[ $size ]['width'];
		$img_height = $_wp_additional_image_sizes[ $size ]['height'];

		$upload_dir        = wp_upload_dir();
		$logo_path         = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $logo );
		$path_parts        = pathinfo( $logo_path );
		$resized_logo_path = str_replace( '.' . $path_parts['extension'], '-' . $size . '.' . $path_parts['extension'], $logo_path );

		if ( ! file_exists( $resized_logo_path ) ) {
			// Generate size
			$image = wp_get_image_editor( $logo_path );

			if ( ! is_wp_error( $image ) ) {
			    $image->resize( $_wp_additional_image_sizes[ $size ]['width'], $_wp_additional_image_sizes[ $size ]['height'], $_wp_additional_image_sizes[ $size ]['crop'] );

			    $image->save( $resized_logo_path );

			    $logo = dirname( $logo ) . '/' . basename( $resized_logo_path );
			}
		} else {
			$logo = dirname( $logo ) . '/' . basename( $resized_logo_path );
		}
	}

	return $logo;
}

/**
 * Display or retrieve the current restaurant name with optional content.
 *
 * @access public
 * @param mixed $id (default: null)
 * @return void
 */
function the_restaurant_name( $before = '', $after = '', $echo = true, $post = null ) {
	$restaurant_name = get_the_restaurant_name( $post );

	if ( strlen( $restaurant_name ) == 0 )
		return;

	$restaurant_name = esc_attr( strip_tags( $restaurant_name ) );
	$restaurant_name = $before . $restaurant_name . $after;

	if ( $echo )
		echo $restaurant_name;
	else
		return $restaurant_name;
}

/**
 * get_the_restaurant_name function.
 *
 * @access public
 * @param int $post (default: null)
 * @return void
 */
function get_the_restaurant_name( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'restaurant_listing' )
		return;

	return apply_filters( 'the_restaurant_name', $post->_restaurant_name, $post );
}

/**
 * get_the_restaurant_website function.
 *
 * @access public
 * @param int $post (default: null)
 * @return void
 */
function get_the_restaurant_website( $post = null ) {
	$post = get_post( $post );

	if ( $post->post_type !== 'restaurant_listing' )
		return;

	$website = $post->_restaurant_website;

	if ( $website && ! strstr( $website, 'http:' ) && ! strstr( $website, 'https:' ) ) {
		$website = 'http://' . $website;
	}

	return apply_filters( 'the_restaurant_website', $website, $post );
}

/**
 * Display or retrieve the current restaurant tagline with optional content.
 *
 * @access public
 * @param mixed $id (default: null)
 * @return void
 */
function the_restaurant_tagline( $before = '', $after = '', $echo = true, $post = null ) {
	$restaurant_tagline = get_the_restaurant_tagline( $post );

	if ( strlen( $restaurant_tagline ) == 0 )
		return;

	$restaurant_tagline = esc_attr( strip_tags( $restaurant_tagline ) );
	$restaurant_tagline = $before . $restaurant_tagline . $after;

	if ( $echo )
		echo $restaurant_tagline;
	else
		return $restaurant_tagline;
}

/**
 * get_the_restaurant_tagline function.
 *
 * @access public
 * @param int $post (default: 0)
 * @return void
 */
function get_the_restaurant_tagline( $post = null ) {
	$post = get_post( $post );

	if ( $post->post_type !== 'restaurant_listing' )
		return;

	return apply_filters( 'the_restaurant_tagline', $post->_restaurant_tagline, $post );
}

/**
 * Display or retrieve the current restaurant twitter link with optional content.
 *
 * @access public
 * @param mixed $id (default: null)
 * @return void
 */
function the_restaurant_twitter( $before = '', $after = '', $echo = true, $post = null ) {
	$restaurant_twitter = get_the_restaurant_twitter( $post );

	if ( strlen( $restaurant_twitter ) == 0 )
		return;

	$restaurant_twitter = esc_attr( strip_tags( $restaurant_twitter ) );
	$restaurant_twitter = $before . '<a href="http://twitter.com/' . $restaurant_twitter . '" class="restaurant_twitter">' . $restaurant_twitter . '</a>' . $after;

	if ( $echo )
		echo $restaurant_twitter;
	else
		return $restaurant_twitter;
}

/**
 * get_the_restaurant_twitter function.
 *
 * @access public
 * @param int $post (default: 0)
 * @return void
 */
function get_the_restaurant_twitter( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'restaurant_listing' )
		return;

	$restaurant_twitter = $post->_restaurant_twitter;

	if ( strlen( $restaurant_twitter ) == 0 )
		return;

	if ( strpos( $restaurant_twitter, '@' ) === 0 )
		$restaurant_twitter = substr( $restaurant_twitter, 1 );

	return apply_filters( 'the_restaurant_twitter', $restaurant_twitter, $post );
}

/**
 * restaurant_listing_class function.
 *
 * @access public
 * @param string $class (default: '')
 * @param mixed $post_id (default: null)
 * @return void
 */
function restaurant_listing_class( $class = '', $post_id = null ) {
	// Separates classes with a single space, collates classes for post DIV
	echo 'class="' . join( ' ', get_restaurant_listing_class( $class, $post_id ) ) . '"';
}

/**
 * get_restaurant_listing_class function.
 *
 * @access public
 * @return array
 */
function get_restaurant_listing_class( $class = '', $post_id = null ) {
	$post = get_post( $post_id );

	if ( $post->post_type !== 'restaurant_listing' ) {
		return array();
	}
	
	$classes = array();

	if ( empty( $post ) ) {
		return $classes;
	}

	$classes[] = 'restaurant_listing';
	if ( ! empty( get_the_restaurant_type()->name ) ) {
		$classes[] = 'restaurant-type-' . sanitize_title( get_the_restaurant_type()->name );
	}

	if ( is_restaurant_filled( $post ) ) {
		$classes[] = 'restaurant_position_filled';
	}

	if ( is_restaurant_featured( $post ) ) {
		$classes[] = 'restaurant_position_featured';
	}
	
	if ( ! empty( $class ) ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$classes = array_merge( $classes, $class );
	}

	return get_post_class( $classes, $post->ID );
}
