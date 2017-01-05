<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Review_Restaurant_Shortcodes class.
 */
class WP_Review_Restaurant_Shortcodes {

	private $restaurant_dashboard_message = '';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'shortcode_action_handler' ) );
		add_action( 'review_restaurant_review_dashboard_content_edit', array( $this, 'edit_restaurant' ) );

		add_shortcode( 'submit_restaurant_form', array( $this, 'submit_restaurant_form' ) );
		add_shortcode( 'restaurant_dashboard', array( $this, 'restaurant_dashboard' ) );
		add_shortcode( 'restaurants', array( $this, 'output_restaurants' ) );
		add_shortcode( 'restaurant', array( $this, 'output_restaurant' ) );
		add_shortcode( 'restaurant_summary', array( $this, 'output_restaurant_summary' ) );
	}

	/**
	 * Handle actions which need to be run before the shortcode e.g. post actions
	 */
	public function shortcode_action_handler() {
		global $post;

		if ( is_page() && strstr( $post->post_content, '[restaurant_dashboard' ) ) {
			$this->restaurant_dashboard_handler();
		}
	}

	/**
	 * Show the restaurant submission form
	 */
	public function submit_restaurant_form() {
		return $GLOBALS['review_restaurant']->forms->get_form( 'submit-restaurant' );
	}

	/**
	 * Handles actions on restaurant dashboard
	 */
	public function restaurant_dashboard_handler() {
		if ( ! empty( $_REQUEST['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'review_restaurant_my_restaurant_actions' ) ) {

			$action = sanitize_title( $_REQUEST['action'] );
			$restaurant_id = absint( $_REQUEST['restaurant_id'] );

			try {
				// Get Restaurant
				$restaurant    = get_post( $restaurant_id );

				// Check ownership
				if ( $restaurant->post_author != get_current_user_id() ) {
					throw new Exception( __( 'Invalid Restaurant ID', 'wp-review-restaurant' ) );
				}

				switch ( $action ) {
					case 'mark_filled' :
						// Check status
						if ( $restaurant->_filled == 1 )
							throw new Exception( __( 'This restaurant is already filled', 'wp-review-restaurant' ) );

						// Update
						update_post_meta( $restaurant_id, '_filled', 1 );

						// Message
						$this->restaurant_dashboard_message = '<div class="review-restaurant-message">' . sprintf( __( '%s has been filled', 'wp-review-restaurant' ), $restaurant->post_title ) . '</div>';
						break;
					case 'mark_not_filled' :
						// Check status
						if ( $restaurant->_filled != 1 )
							throw new Exception( __( 'This restaurant is already not filled', 'wp-review-restaurant' ) );

						// Update
						update_post_meta( $restaurant_id, '_filled', 0 );

						// Message
						$this->restaurant_dashboard_message = '<div class="review-restaurant-message">' . sprintf( __( '%s has been marked as not filled', 'wp-review-restaurant' ), $restaurant->post_title ) . '</div>';
						break;
					case 'delete' :
						// Trash it
						wp_trash_post( $restaurant_id );

						// Message
						$this->restaurant_dashboard_message = '<div class="review-restaurant-message">' . sprintf( __( '%s has been deleted', 'wp-review-restaurant' ), $restaurant->post_title ) . '</div>';

						break;
					default :
						do_action( 'review_restaurant_review_dashboard_do_action_' . $action );
						break;
				}

				do_action( 'review_restaurant_my_restaurant_do_action', $action, $restaurant_id );

			} catch ( Exception $e ) {
				$this->restaurant_dashboard_message = '<div class="review-restaurant-error">' . $e->getMessage() . '</div>';
			}
		}
	}

	/**
	 * Shortcode which lists the logged in user's restaurants
	 */
	public function restaurant_dashboard( $atts ) {
		global $review_restaurant;

		if ( ! is_user_logged_in() ) {
			return __( 'You need to be signed in to manage your restaurant listings.', 'wp-review-restaurant' );
		}

		extract( shortcode_atts( array(
			'posts_per_page' => '25',
		), $atts ) );

		wp_enqueue_script( 'wp-review-restaurant-restaurant-dashboard' );

		ob_start();

		// If doing an action, show conditional content if needed....
		if ( ! empty( $_REQUEST['action'] ) ) {
			$action = sanitize_title( $_REQUEST['action'] );

			// Show alternative content if a plugin wants to
			if ( has_action( 'review_restaurant_review_dashboard_content_' . $action ) ) {
				do_action( 'review_restaurant_review_dashboard_content_' . $action, $atts );

				return ob_get_clean();
			}
		}

		// ....If not show the restaurant dashboard
		$args     = apply_filters( 'review_restaurant_get_dashboard_restaurants_args', array(
			'post_type'           => 'restaurant_listing',
			'post_status'         => array( 'publish', 'expired', 'pending' ),
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $posts_per_page,
			'offset'              => ( max( 1, get_query_var('paged') ) - 1 ) * $posts_per_page,
			'orderby'             => 'date',
			'order'               => 'desc',
			'author'              => get_current_user_id()
		) );

		$restaurants = new WP_Query;

		echo $this->restaurant_dashboard_message;

		$restaurant_dashboard_columns = apply_filters( 'review_restaurant_review_dashboard_columns', array(
			'restaurant_title' => __( 'Restaurant Title', 'wp-review-restaurant' ),
			'filled'    => __( 'Filled?', 'wp-review-restaurant' ),
			'date'      => __( 'Date Posted', 'wp-review-restaurant' ),
			'expires'   => __( 'Date Expires', 'wp-review-restaurant' )
		) );

		get_review_restaurant_template( 'restaurant-dashboard.php', array( 'restaurants' => $restaurants->query( $args ), 'max_num_pages' => $restaurants->max_num_pages, 'restaurant_dashboard_columns' => $restaurant_dashboard_columns ) );

		return ob_get_clean();
	}

	/**
	 * Edit restaurant form
	 */
	public function edit_restaurant() {
		global $review_restaurant;

		echo $review_restaurant->forms->get_form( 'edit-restaurant' );
	}

	/**
	 * output_restaurants function.
	 *
	 * @access public
	 * @param mixed $args
	 * @return void
	 */
	public function output_restaurants( $atts ) {
		global $review_restaurant;

		ob_start();

		extract( $atts = shortcode_atts( apply_filters( 'review_restaurant_output_restaurants_defaults', array(
			'per_page'           => get_option( 'review_restaurant_per_page' ),
			'orderby'            => 'featured',
			'order'              => 'DESC',
			
			// Filters + cats
			'show_filters'       => true,
			'show_categories'    => true,
			
			// Limit what restaurants are shown based on category and type
			'categories'         => '',
			'restaurant_types'   => '',
			'restaurant_types_cuisine'   => '',
			'featured'           => null, // True to show only featured, false to hide featuref, leave null to show both.
			'show_featured_only' => false, // Deprecated
			
			// Default values for filters
			'location'           => '', 
			'keywords'           => '',
			'selected_category'  => '',
			'selected_restaurant_types' => implode( ',', array_values( get_restaurant_listing_types( 'id=>slug' ) ) ),
		) ), $atts ) );

		if ( ! get_option( 'review_restaurant_enable_categories' ) ) {
			$show_categories = false;
		}

		// String and bool handling
		$show_filters       = ( is_bool( $show_filters ) && $show_filters ) || in_array( $show_filters, array( '1', 'true', 'yes' ) ) ? true : false;
		$show_categories    = ( is_bool( $show_categories ) && $show_categories ) || in_array( $show_categories, array( '1', 'true', 'yes' ) ) ? true : false;
		$show_featured_only = ( is_bool( $show_featured_only ) && $show_featured_only ) || in_array( $show_featured_only, array( '1', 'true', 'yes' ) ) ? true : false;

		if ( ! is_null( $featured ) ) {
			$featured = ( is_bool( $featured ) && $featured ) || in_array( $featured, array( '1', 'true', 'yes' ) ) ? true : false;
		} elseif( $show_featured_only ) {
			$featured = true;
		}

		// Array handling
		$categories         	   = array_filter( array_map( 'trim', explode( ',', $categories ) ) );
		$restaurant_types          = array_filter( array_map( 'trim', explode( ',', $restaurant_types ) ) );
		$restaurant_types_cuisine  = array_filter( array_map( 'trim', explode( ',', $restaurant_types_cuisine ) ) );
		$restaurant_types_advanced  = array_filter( array_map( 'trim', explode( ',', $restaurant_types_advanced ) ) );
		$selected_restaurant_types = array_filter( array_map( 'trim', explode( ',', $selected_restaurant_types ) ) );

		// Get keywords and location from querystring if set
		if ( ! empty( $_GET['search_keywords'] ) ) {
			$keywords = sanitize_text_field( $_GET['search_keywords'] );
		}
		if ( ! empty( $_GET['search_location'] ) ) {
			$location = sanitize_text_field( $_GET['search_location'] );
		}
		if ( ! empty( $_GET['search_category'] ) ) {
			$selected_category = sanitize_text_field( $_GET['search_category'] );
		}

		if ( $show_filters ) {

			get_review_restaurant_template( 'restaurant-filters.php', array( 'per_page' => $per_page, 'orderby' => $orderby, 'order' => $order, 'show_categories' => $show_categories, 'categories' => $categories, 'selected_category' => $selected_category, 'restaurant_types' => $restaurant_types, 'restaurant_types_cuisine' => $restaurant_types_cuisine,'restaurant_types_advanced' => $restaurant_types_advanced, 'atts' => $atts, 'location' => $location, 'keywords' => $keywords, 'selected_restaurant_types' => $selected_restaurant_types ) );

			?><ul class="restaurant_listings"></ul><a class="load_more_restaurants" href="#" style="display:none;"><strong><?php _e( 'Load more restaurant listings', 'wp-review-restaurant' ); ?></strong></a><?php

		} else {

			$restaurants = get_restaurant_listings( apply_filters( 'review_restaurant_output_restaurants_args', array(
				'search_location'   => $location,
				'search_keywords'   => $keywords,
				'search_categories' => $categories,
				'restaurant_types'  => $restaurant_types,
				'restaurant_types_cuisine'  => $restaurant_types_cuisine,
				'orderby'           => $orderby,
				'order'             => $order,
				'posts_per_page'    => $per_page,
				'featured'          => $featured
			) ) );

			if ( $restaurants->have_posts() ) : ?>

				<ul class="restaurant_listings">

					<?php while ( $restaurants->have_posts() ) : $restaurants->the_post(); ?>

						<?php get_review_restaurant_template_part( 'content', 'restaurant_listing' ); ?>

					<?php endwhile; ?>

				</ul>

				<?php if ( $restaurants->found_posts > $per_page ) : ?>

					<?php wp_enqueue_script( 'wp-review-restaurant-ajax-filters' ); ?>

					<a class="load_more_restaurants" href="#"><strong><?php _e( 'Load more restaurant listings', 'wp-review-restaurant' ); ?></strong></a>

				<?php endif; ?>

			<?php endif;

			wp_reset_postdata();
		}

		$data_attributes_string = '';
		$data_attributes        = array(
			'location'     => $location,
			'keywords'     => $keywords,
			'show_filters' => $show_filters ? 'true' : 'false',
			'per_page'     => $per_page,
			'orderby'      => $orderby,
			'order'        => $order,
			'categories'   => implode( ',', $categories )
		);
		if ( ! is_null( $featured ) ) {
			$data_attributes[ 'featured' ] = $featured ? 'true' : 'false';
		}
		foreach ( $data_attributes as $key => $value ) {
			$data_attributes_string .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
		}

		return '<div class="restaurant_listings" ' . $data_attributes_string . '>' . ob_get_clean() . '</div>';
	}

	/**
	 * output_restaurant function.
	 *
	 * @access public
	 * @param array $args
	 * @return string
	 */
	public function output_restaurant( $atts ) {
		global $review_restaurant;

		extract( shortcode_atts( array(
			'id' => '',
		), $atts ) );

		if ( ! $id )
			return;

		ob_start();

		$args = array(
			'post_type'   => 'restaurant_listing',
			'post_status' => 'publish',
			'p'           => $id
		);

		$restaurants = new WP_Query( $args );

		if ( $restaurants->have_posts() ) : ?>

			<?php while ( $restaurants->have_posts() ) : $restaurants->the_post(); ?>

				<h1><?php the_title(); ?></h1>

				<?php get_review_restaurant_template_part( 'content-single', 'restaurant_listing' ); ?>

			<?php endwhile; ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="restaurant_shortcode single_restaurant_listing">' . ob_get_clean() . '</div>';
	}

	/**
	 * Restaurant Summary shortcode
	 *
	 * @access public
	 * @param array $args
	 * @return string
	 */
	public function output_restaurant_summary( $atts ) {
		global $review_restaurant;

		extract( shortcode_atts( array(
			'id'    => '',
			'width' => '250px',
			'align' => 'left'
		), $atts ) );

		if ( ! $id )
			return;

		ob_start();

		$args = array(
			'post_type'   => 'restaurant_listing',
			'post_status' => 'publish',
			'p'           => $id
		);

		$restaurants = new WP_Query( $args );

		if ( $restaurants->have_posts() ) : ?>

			<?php while ( $restaurants->have_posts() ) : $restaurants->the_post(); ?>

				<div class="restaurant_summary_shortcode align<?php echo $align ?>" style="width: <?php echo $width ? $width : auto; ?>">

					<?php get_review_restaurant_template_part( 'content-summary', 'restaurant_listing' ); ?>

				</div>

			<?php endwhile; ?>

		<?php endif;

		wp_reset_postdata();

		return ob_get_clean();
	}
}

new WP_Review_Restaurant_Shortcodes();