<?php
/**
 * WP_Review_Restaurant_Content class.
 */
class WP_Review_Restaurant_Post_Types {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_filter( 'admin_head', array( $this, 'admin_head' ) );
		add_filter( 'the_content', array( $this, 'restaurant_content' ) );
		add_action( 'review_restaurant_check_for_expired_restaurants', array( $this, 'check_for_expired_restaurants' ) );
		add_action( 'review_restaurant_delete_old_previews', array( $this, 'delete_old_previews' ) );
		add_action( 'pending_to_publish', array( $this, 'set_expirey' ) );
		add_action( 'preview_to_publish', array( $this, 'set_expirey' ) );
		add_action( 'draft_to_publish', array( $this, 'set_expirey' ) );
		add_action( 'auto-draft_to_publish', array( $this, 'set_expirey' ) );

		add_filter( 'the_restaurant_description', 'wptexturize'        );
		add_filter( 'the_restaurant_description', 'convert_smilies'    );
		add_filter( 'the_restaurant_description', 'convert_chars'      );
		add_filter( 'the_restaurant_description', 'wpautop'            );
		add_filter( 'the_restaurant_description', 'shortcode_unautop'  );
		add_filter( 'the_restaurant_description', 'prepend_attachment' );

		add_action( 'review_restaurant_application_details_email', array( $this, 'application_details_email' ) );
		add_action( 'review_restaurant_application_details_url', array( $this, 'application_details_url' ) );
	}

	/**
	 * register_post_types function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_post_types() {
		if ( post_type_exists( "restaurant_listing" ) )
			return;

		$admin_capability = 'manage_restaurant_listings';

		/**
		 * Taxonomies
		 */
		if ( get_option( 'review_restaurant_enable_categories' ) ) {
			$singular  = __( 'Restaurant Category', 'wp-review-restaurant' );
			$plural    = __( 'Restaurant Categories', 'wp-review-restaurant' );

			if ( current_theme_supports( 'review-restaurant-templates' ) ) {
				$rewrite     = array(
					'slug'         => _x( 'restaurant-category', 'Restaurant category slug - resave permalinks after changing this', 'wp-review-restaurant' ),
					'with_front'   => false,
					'hierarchical' => false
				);
			} else {
				$rewrite = false;
			}

			register_taxonomy( "restaurant_listing_category",
		        array( "restaurant_listing" ),
		        array(
		            'hierarchical' 			=> true,
		            'update_count_callback' => '_update_post_term_count',
		            'label' 				=> $plural,
		            'labels' => array(
	                    'name' 				=> $plural,
	                    'singular_name' 	=> $singular,
	                    'search_items' 		=> sprintf( __( 'Search %s', 'wp-review-restaurant' ), $plural ),
	                    'all_items' 		=> sprintf( __( 'All %s', 'wp-review-restaurant' ), $plural ),
	                    'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-review-restaurant' ), $singular ),
	                    'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-review-restaurant' ), $singular ),
	                    'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-review-restaurant' ), $singular ),
	                    'update_item' 		=> sprintf( __( 'Update %s', 'wp-review-restaurant' ), $singular ),
	                    'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-review-restaurant' ), $singular ),
	                    'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-review-restaurant' ),  $singular )
	            	),
		            'show_ui' 				=> true,
		            'query_var' 			=> true,
		            'capabilities'			=> array(
		            	'manage_terms' 		=> $admin_capability,
		            	'edit_terms' 		=> $admin_capability,
		            	'delete_terms' 		=> $admin_capability,
		            	'assign_terms' 		=> $admin_capability,
		            ),
		            'rewrite' 				=> $rewrite,
		        )
		    );
		}
		
		if ( current_theme_supports( 'review-restaurant-templates' ) ) {
			$rewrite     = array(
				'slug'         => _x( 'restaurant-type', 'Restaurant type slug - resave permalinks after changing this', 'wp-review-restaurant' ),
				'with_front'   => false,
				'hierarchical' => false
			);
		} else {
			$rewrite = false;
		}
		
		$singular  = __( 'Restaurant Type', 'wp-review-restaurant' );
		$plural    = __( 'Restaurant Types', 'wp-review-restaurant' );

		register_taxonomy( "restaurant_listing_type",
	        array( "restaurant_listing" ),
	        array(
	            'hierarchical' 			=> true,
	            'label' 				=> $plural,
	            'labels' => array(
                    'name' 				=> $plural,
                    'singular_name' 	=> $singular,
                    'search_items' 		=> sprintf( __( 'Search %s', 'wp-review-restaurant' ), $plural ),
                    'all_items' 		=> sprintf( __( 'All %s', 'wp-review-restaurant' ), $plural ),
                    'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-review-restaurant' ), $singular ),
                    'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-review-restaurant' ), $singular ),
                    'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-review-restaurant' ), $singular ),
                    'update_item' 		=> sprintf( __( 'Update %s', 'wp-review-restaurant' ), $singular ),
                    'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-review-restaurant' ), $singular ),
                    'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-review-restaurant' ),  $singular )
            	),
	            'show_ui' 				=> true,
	            'query_var' 			=> true,
	            'capabilities'			=> array(
	            	'manage_terms' 		=> $admin_capability,
	            	'edit_terms' 		=> $admin_capability,
	            	'delete_terms' 		=> $admin_capability,
	            	'assign_terms' 		=> $admin_capability,
	            ),
	           'rewrite' 				=> $rewrite,
	        )
	    );
		
		$singular  = __( 'Cuisine Type', 'wp-review-restaurant' );
		$plural    = __( 'Cuisine Types', 'wp-review-restaurant' );
		
		register_taxonomy( "restaurant_listing_type_cuisine",
	        array( "restaurant_listing" ),
	        array(
	            'hierarchical' 			=> true,
	            'label' 				=> $plural,
	            'labels' => array(
                    'name' 				=> $plural,
                    'singular_name' 	=> $singular,
                    'search_items' 		=> sprintf( __( 'Search %s', 'wp-review-restaurant' ), $plural ),
                    'all_items' 		=> sprintf( __( 'All %s', 'wp-review-restaurant' ), $plural ),
                    'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-review-restaurant' ), $singular ),
                    'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-review-restaurant' ), $singular ),
                    'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-review-restaurant' ), $singular ),
                    'update_item' 		=> sprintf( __( 'Update %s', 'wp-review-restaurant' ), $singular ),
                    'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-review-restaurant' ), $singular ),
                    'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-review-restaurant' ),  $singular )
            	),
	            'show_ui' 				=> true,
	            'query_var' 			=> true,
	            'capabilities'			=> array(
	            	'manage_terms' 		=> $admin_capability,
	            	'edit_terms' 		=> $admin_capability,
	            	'delete_terms' 		=> $admin_capability,
	            	'assign_terms' 		=> $admin_capability,
	            ),
	           'rewrite' 				=> $rewrite,
	        )
	    );
		
		$singular  = __( 'Location', 'wp-review-restaurant' );  // need to think about it
		$plural    = __( 'Locations', 'wp-review-restaurant' ); // need to think about it
		
		register_taxonomy( "restaurant_listing_type_location",
	        array( "restaurant_listing" ),
	        array(
	            'hierarchical' 			=> true,
	            'label' 				=> $plural,
	            'labels' => array(
                    'name' 				=> $plural,
                    'singular_name' 	=> $singular,
                    'search_items' 		=> sprintf( __( 'Search %s', 'wp-review-restaurant' ), $plural ),
                    'all_items' 		=> sprintf( __( 'All %s', 'wp-review-restaurant' ), $plural ),
                    'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-review-restaurant' ), $singular ),
                    'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-review-restaurant' ), $singular ),
                    'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-review-restaurant' ), $singular ),
                    'update_item' 		=> sprintf( __( 'Update %s', 'wp-review-restaurant' ), $singular ),
                    'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-review-restaurant' ), $singular ),
                    'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-review-restaurant' ),  $singular )
            	),
	            'show_ui' 				=> true,
	            'query_var' 			=> true,
	            'capabilities'			=> array(
	            	'manage_terms' 		=> $admin_capability,
	            	'edit_terms' 		=> $admin_capability,
	            	'delete_terms' 		=> $admin_capability,
	            	'assign_terms' 		=> $admin_capability,
	            ),
	           'rewrite' 				=> $rewrite,
	        )
	    );
		
		$singular  = __( 'Additional Option', 'wp-review-restaurant' );  // need to think about it
		$plural    = __( 'Additional Options', 'wp-review-restaurant' ); // need to think about it
		
		register_taxonomy( "restaurant_listing_type_advanced",
	        array( "restaurant_listing" ),
	        array(
	            'hierarchical' 			=> true,
	            'label' 				=> $plural,
	            'labels' => array(
                    'name' 				=> $plural,
                    'singular_name' 	=> $singular,
                    'search_items' 		=> sprintf( __( 'Search %s', 'wp-review-restaurant' ), $plural ),
                    'all_items' 		=> sprintf( __( 'All %s', 'wp-review-restaurant' ), $plural ),
                    'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-review-restaurant' ), $singular ),
                    'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-review-restaurant' ), $singular ),
                    'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-review-restaurant' ), $singular ),
                    'update_item' 		=> sprintf( __( 'Update %s', 'wp-review-restaurant' ), $singular ),
                    'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-review-restaurant' ), $singular ),
                    'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-review-restaurant' ),  $singular )
            	),
	            'show_ui' 				=> true,
	            'query_var' 			=> true,
	            'capabilities'			=> array(
	            	'manage_terms' 		=> $admin_capability,
	            	'edit_terms' 		=> $admin_capability,
	            	'delete_terms' 		=> $admin_capability,
	            	'assign_terms' 		=> $admin_capability,
	            ),
	           'rewrite' 				=> $rewrite,
	        )
	    );

	    /**
		 * Post types
		 */
		$singular  = __( 'Restaurant Listing', 'wp-review-restaurant' );
		$plural    = __( 'Restaurant Listings', 'wp-review-restaurant' );

		if ( current_theme_supports( 'review-restaurant-templates' ) ) {
			$has_archive = _x( 'restaurants', 'Post type archive slug - resave permalinks after changing this', 'wp-review-restaurant' );
		} else {
			$has_archive = false;
		}

		$rewrite     = array(
			'slug'       => _x( 'restaurant', 'Restaurant permalink - resave permalinks after changing this', 'wp-review-restaurant' ),
			'with_front' => false,
			'feeds'      => true,
			'pages'      => false
		);

		register_post_type( "restaurant_listing",
			apply_filters( "register_post_type_restaurant_listing", array(
				'labels' => array(
					'name' 					=> $plural,
					'singular_name' 		=> $singular,
					'menu_name'             => $plural,
					'all_items'             => sprintf( __( 'All %s', 'wp-review-restaurant' ), $plural ),
					'add_new' 				=> __( 'Add New', 'wp-review-restaurant' ),
					'add_new_item' 			=> sprintf( __( 'Add %s', 'wp-review-restaurant' ), $singular ),
					'edit' 					=> __( 'Edit', 'wp-review-restaurant' ),
					'edit_item' 			=> sprintf( __( 'Edit %s', 'wp-review-restaurant' ), $singular ),
					'new_item' 				=> sprintf( __( 'New %s', 'wp-review-restaurant' ), $singular ),
					'view' 					=> sprintf( __( 'View %s', 'wp-review-restaurant' ), $singular ),
					'view_item' 			=> sprintf( __( 'View %s', 'wp-review-restaurant' ), $singular ),
					'search_items' 			=> sprintf( __( 'Search %s', 'wp-review-restaurant' ), $plural ),
					'not_found' 			=> sprintf( __( 'No %s found', 'wp-review-restaurant' ), $plural ),
					'not_found_in_trash' 	=> sprintf( __( 'No %s found in trash', 'wp-review-restaurant' ), $plural ),
					'parent' 				=> sprintf( __( 'Parent %s', 'wp-review-restaurant' ), $singular )
				),
				'description' => __( 'This is where you can create and manage restaurant listings.', 'wp-review-restaurant' ),
				'public' 				=> true,
				'show_ui' 				=> true,
				'capability_type' 		=> 'post',
				'capabilities' => array(
					'publish_posts' 		=> $admin_capability,
					'edit_posts' 			=> $admin_capability,
					'edit_others_posts' 	=> $admin_capability,
					'delete_posts' 			=> $admin_capability,
					'delete_others_posts'	=> $admin_capability,
					'read_private_posts'	=> $admin_capability,
					'edit_post' 			=> $admin_capability,
					'delete_post' 			=> $admin_capability,
					'read_post' 			=> 'read_restaurant_listing'
				),
				'publicly_queryable' 	=> true,
				'exclude_from_search' 	=> false,
				'hierarchical' 			=> false,
				'rewrite' 				=> $rewrite,
				'query_var' 			=> true,
				'supports' 				=> array( 'title', 'editor', 'custom-fields', 'comments' ),
				'has_archive' 			=> $has_archive,
				'show_in_nav_menus' 	=> false
			) )
		);

		/**
		 * Feeds
		 */
		add_feed( 'restaurant_feed', array( $this, 'restaurant_feed' ) );

		/**
		 * Post status
		 */
		register_post_status( 'expired', array(
			'label'                     => _x( 'Expired', 'restaurant_listing', 'wp-review-restaurant' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'wp-review-restaurant' ),
		) );
		register_post_status( 'preview', array(
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false,
		) );
	}

	/**
	 * Change label
	 */
	public function admin_head() {
		global $menu;

		$plural     = __( 'Restaurant Listings', 'wp-review-restaurant' );
		$count_restaurants = wp_count_posts( 'restaurant_listing', 'readable' );

		if ( ! empty( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $key => $menu_item ) {
				if ( strpos( $menu_item[0], $plural ) === 0 ) {
					if ( $order_count = $count_restaurants->pending ) {
						$menu[ $key ][0] .= " <span class='awaiting-mod update-plugins count-$order_count'><span class='pending-count'>" . number_format_i18n( $count_restaurants->pending ) . "</span></span>" ;
					}
					break;
				}
			}
		}
	}

	/**
	 * Add extra content when showing restaurant content
	 */
	public function restaurant_content( $content ) {
		global $post;

		if ( ! is_singular( 'restaurant_listing' ) || ! in_the_loop() ) {
			return $content;
		}

		remove_filter( 'the_content', array( $this, 'restaurant_content' ) );

		if ( 'restaurant_listing' === $post->post_type ) {
			ob_start();

			do_action( 'restaurant_content_start' );

			get_review_restaurant_template_part( 'content-single', 'restaurant_listing' );

			do_action( 'restaurant_content_end' );

			$content = ob_get_clean();
		}

		add_filter( 'the_content', array( $this, 'restaurant_content' ) );

		return $content;
	}

	/**
	 * Restaurant listing feeds
	 */
	public function restaurant_feed() {
		$args = array(
			'post_type'           => 'restaurant_listing',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => 10,
			's'                   => isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '',
			'meta_query'          => array(),
			'tax_query'           => array()
		);

		if ( ! empty( $_GET['location'] ) ) {
			$args['meta_query'][] = array(
				'key'     => '_restaurant_location',
				'value'   => sanitize_text_field( $_GET['location'] ),
				'compare' => 'LIKE'
			);
		}

		if ( ! empty( $_GET['type'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'restaurant_listing_type',
				'field'    => 'slug',
				'terms'    => explode( ',', sanitize_text_field( $_GET['type'] ) ) + array( 0 )
			);
		}

		if ( ! empty( $_GET['restaurant_categories'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'restaurant_listing_category',
				'field'    => 'id',
				'terms'    => explode( ',', sanitize_text_field( $_GET['restaurant_categories'] ) ) + array( 0 )
			);
		}

		query_posts( apply_filters( 'restaurant_feed_args', $args ) );

		add_action( 'rss2_ns', array( $this, 'restaurant_feed_namespace' ) );
		add_action( 'rss2_item', array( $this, 'restaurant_feed_item' ) );

		do_feed_rss2( false );
	}

	/**
	 * Add a custom namespace to the restaurant feed
	 */
	public function restaurant_feed_namespace() {
		echo 'xmlns:restaurant_listing="' .  site_url() . '"' . "\n";
	}

	/**
	 * Add custom data to the restaurant feed
	 */
	public function restaurant_feed_item() {
		$post_id  = get_the_ID();
		$location = get_the_restaurant_location( $post_id );
		$restaurant_type = get_the_restaurant_type( $post_id );
		$restaurant  = get_the_restaurant_name( $post_id );

		if ( $location ) {
			echo "<restaurant_listing:location>{$location}</restaurant_listing:location>\n";
		}
		if ( $restaurant_type ) {
			echo "<restaurant_listing:restaurant_type>{$restaurant_type->name}</restaurant_listing:restaurant_type>\n";
		}
		if ( $restaurant ) {
			echo "<restaurant_listing:restaurant>{$restaurant}</restaurant_listing:restaurant>\n";
		}
	}

	/**
	 * Expire restaurants
	 */
	public function check_for_expired_restaurants() {
		global $wpdb;

		// Change status to expired
		$restaurant_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->posts} as posts ON postmeta.post_id = posts.ID
			WHERE postmeta.meta_key = '_restaurant_expires'
			AND postmeta.meta_value > 0
			AND postmeta.meta_value < %s
			AND posts.post_status = 'publish'
			AND posts.post_type = 'restaurant_listing'
		", date( 'Y-m-d', current_time( 'timestamp' ) ) ) );

		if ( $restaurant_ids ) {
			foreach ( $restaurant_ids as $restaurant_id ) {
				$restaurant_data       = array();
				$restaurant_data['ID'] = $restaurant_id;
				$restaurant_data['post_status'] = 'expired';
				wp_update_post( $restaurant_data );
			}
		}

		// Delete old expired restaurants
		$restaurant_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT posts.ID FROM {$wpdb->posts} as posts
			WHERE posts.post_type = 'restaurant_listing'
			AND posts.post_modified < %s
			AND posts.post_status = 'expired'
		", date( 'Y-m-d', strtotime( '-30 days', current_time( 'timestamp' ) ) ) ) );

		if ( $restaurant_ids ) {
			foreach ( $restaurant_ids as $restaurant_id ) {
				wp_trash_post( $restaurant_id );
			}
		}
	}

	/**
	 * Delete old previewed restaurants after 30 days to keep the DB clean
	 */
	public function delete_old_previews() {
		global $wpdb;

		// Delete old expired restaurants
		$restaurant_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT posts.ID FROM {$wpdb->posts} as posts
			WHERE posts.post_type = 'restaurant_listing'
			AND posts.post_modified < %s
			AND posts.post_status = 'preview'
		", date( 'Y-m-d', strtotime( '-30 days', current_time( 'timestamp' ) ) ) ) );

		if ( $restaurant_ids ) {
			foreach ( $restaurant_ids as $restaurant_id ) {
				wp_delete_post( $restaurant_id, true );
			}
		}
	}

	/**
	 * Set expirey date when restaurant status changes
	 */
	public function set_expirey( $post ) {
		if ( $post->post_type !== 'restaurant_listing' )
			return;

		// See if it is already set
		$expires  = get_post_meta( $post->ID, '_restaurant_expires', true );

		if ( ! empty( $expires ) )
			return;

		// Get duration from the product if set...
		$duration = get_post_meta( $post->ID, '_restaurant_duration', true );

		// ...otherwise use the global option
		if ( ! $duration )
			$duration = absint( get_option( 'review_restaurant_submission_duration' ) );

		if ( $duration ) {
			$expires = date( 'Y-m-d', strtotime( "+{$duration} days", current_time( 'timestamp' ) ) );
			update_post_meta( $post->ID, '_restaurant_expires', $expires );

			// In case we are saving a post, ensure post data is updated so the field is not overridden
			if ( isset( $_POST[ '_restaurant_expires' ] ) )
				$_POST[ '_restaurant_expires' ] = $expires;

		} else {
			update_post_meta( $post->ID, '_restaurant_expires', '' );
		}
	}

	/**
	 * The application content when the application method is an email
	 */
	public function application_details_email( $enquire ) {
		get_review_restaurant_template( 'restaurant-application-email.php', array( 'enquire' => $enquire ) );
	}

	/**
	 * The application content when the application method is a url
	 */
	public function application_details_url( $enquire ) {
		get_review_restaurant_template( 'restaurant-application-url.php', array( 'enquire' => $enquire ) );
	}
}