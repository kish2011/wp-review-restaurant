<?php

/**
 * WP_Review_Restaurant_Form_Submit_Restaurant class.
 */
class WP_Review_Restaurant_Form_Submit_Restaurant extends WP_Review_Restaurant_Form {

	public    static $form_name = 'submit-restaurant';
	protected static $restaurant_id;
	protected static $preview_restaurant;
	protected static $steps;
	protected static $step = 0;

	/**
	 * Init form
	 */
	public static function init() {
		add_action( 'wp', array( __CLASS__, 'process' ) );

		self::$steps  = (array) apply_filters( 'submit_restaurant_steps', array(
			'submit' => array(
				'name'     => __( 'Submit Details', 'wp-review-restaurant' ),
				'view'     => array( __CLASS__, 'submit' ),
				'handler'  => array( __CLASS__, 'submit_handler' ),
				'priority' => 10
				),
			'preview' => array(
				'name'     => __( 'Preview', 'wp-review-restaurant' ),
				'view'     => array( __CLASS__, 'preview' ),
				'handler'  => array( __CLASS__, 'preview_handler' ),
				'priority' => 20
			),
			'done' => array(
				'name'     => __( 'Done', 'wp-review-restaurant' ),
				'view'     => array( __CLASS__, 'done' ),
				'priority' => 30
			)
		) );

		uasort( self::$steps, array( __CLASS__, 'sort_by_priority' ) );

		// Get step/restaurant
		if ( isset( $_POST['step'] ) ) {
			self::$step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( $_POST['step'], array_keys( self::$steps ) );
		} elseif ( ! empty( $_GET['step'] ) ) {
			self::$step = is_numeric( $_GET['step'] ) ? max( absint( $_GET['step'] ), 0 ) : array_search( $_GET['step'], array_keys( self::$steps ) );
		}
		self::$restaurant_id = ! empty( $_REQUEST['restaurant_id'] ) ? absint( $_REQUEST[ 'restaurant_id' ] ) : 0;

		// Validate restaurant ID if set
		if ( self::$restaurant_id && ! in_array( get_post_status( self::$restaurant_id ), apply_filters( 'review_restaurant_valid_submit_restaurant_statuses', array( 'preview' ) ) ) ) {
			self::$restaurant_id = 0;
			self::$step   = 0;
		}
	}

	/**
	 * Get step from outside of the class
	 */
	public static function get_step() {
		return self::$step;
	}

	/**
	 * Increase step from outside of the class
	 */
	public static function next_step() {
		self::$step ++;
	}

	/**
	 * Decrease step from outside of the class
	 */
	public static function previous_step() {
		self::$step --;
	}

	/**
	 * Sort array by priority value
	 */
	protected static function sort_by_priority( $a, $b ) {
		return $a['priority'] - $b['priority'];
	}

	/**
	 * Get the submitted restaurant ID
	 * @return int
	 */
	public static function get_restaurant_id() {
		return absint( self::$restaurant_id );
	}

	/**
	 * init_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public static function init_fields() {
		if ( self::$fields ) {
			return;
		}

		$allowed_application_method = get_option( 'review_restaurant_allowed_application_method', '' );
		switch ( $allowed_application_method ) {
			case 'email' :
				$application_method_label       = __( 'Application email', 'wp-review-restaurant' );
				$application_method_placeholder = __( 'you@yourdomain.com', 'wp-review-restaurant' );
			break;
			case 'url' :
				$application_method_label       = __( 'Application URL', 'wp-review-restaurant' );
				$application_method_placeholder = __( 'http://', 'wp-review-restaurant' );
			break;
			default :
				$application_method_label       = __( 'Application email/URL', 'wp-review-restaurant' );
				$application_method_placeholder = __( 'Enter an email address or website URL', 'wp-review-restaurant' );
			break;
		}

		self::$fields = apply_filters( 'submit_restaurant_form_fields', array(
			'restaurant' => array(
				'restaurant_title' => array(
					'label'       => __( 'Restaurant title', 'wp-review-restaurant' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1
				),
				'restaurant_location' => array(
					'label'       => __( 'Restaurant location', 'wp-review-restaurant' ),
					'description' => __( 'Leave this blank if the restaurant can be access from anywhere (i.e. home delivery)', 'wp-review-restaurant' ),
					'type'        => 'text',
					'required'    => false,
					'placeholder' => __( 'e.g. "London, UK", "New York", "Houston, TX"', 'wp-review-restaurant' ),
					'priority'    => 2
				),
				'restaurant_type' => array(
					'label'       => __( 'Restaurant type', 'wp-review-restaurant' ),
					'type'        => 'select',
					'required'    => true,
					'options'     => self::restaurant_types(),
					'placeholder' => '',
					'priority'    => 3,
					'default'     => 'restaurants-bars'
				),
				'restaurant_types_cuisine' => array(
					'label'       => __( 'Restaurant type cuisine', 'wp-review-restaurant' ),
					'type'        => 'select',
					'required'    => true,
					'options'     => self::restaurant_types_cuisine(),
					'placeholder' => '',
					'priority'    => 3,
					'default'     => 'chinese'
				),
				'restaurant_category' => array(
					'label'       => __( 'Restaurant category', 'wp-review-restaurant' ),
					'type'        => 'select',
					'required'    => true,
					'options'     => self::restaurant_categories(),
					'placeholder' => '',
					'priority'    => 4,
					'default'     => ''
				),
				'restaurant_description' => array(
					'label'       => __( 'Description', 'wp-review-restaurant' ),
					'type'        => 'wp-editor',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 5
				),
				'application' => array(
					'label'       => $application_method_label,
					'type'        => 'text',
					'required'    => true,
					'placeholder' => $application_method_placeholder,
					'priority'    => 6
				)
			),
			'restaurant' => array(
				'restaurant_name' => array(
					'label'       => __( 'Restaurant name', 'wp-review-restaurant' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => __( 'Enter the name of the restaurant', 'wp-review-restaurant' ),
					'priority'    => 1
				),
				'restaurant_website' => array(
					'label'       => __( 'Website', 'wp-review-restaurant' ),
					'type'        => 'text',
					'required'    => false,
					'placeholder' => __( 'http://', 'wp-review-restaurant' ),
					'priority'    => 2
				),
				'restaurant_tagline' => array(
					'label'       => __( 'Tagline', 'wp-review-restaurant' ),
					'type'        => 'text',
					'required'    => false,
					'placeholder' => __( 'Briefly describe your restaurant', 'wp-review-restaurant' ),
					'maxlength'   => 64,
					'priority'    => 3
				),
				'restaurant_twitter' => array(
					'label'       => __( 'Twitter username', 'wp-review-restaurant' ),
					'type'        => 'text',
					'required'    => false,
					'placeholder' => __( '@yourrestaurant', 'wp-review-restaurant' ),
					'priority'    => 4
				),
				'restaurant_logo' => array(
					'label'       => __( 'Logo', 'wp-review-restaurant' ),
					'type'        => 'file',
					'required'    => false,
					'placeholder' => '',
					'priority'    => 5,
					'allowed_mime_types' => array(
						'jpg' => 'image/jpeg',
						'gif' => 'image/gif',
						'png' => 'image/png'
					)
				)
			)
		) );

		if ( ! get_option( 'review_restaurant_enable_categories' ) || wp_count_terms( 'restaurant_listing_category' ) == 0 ) {
			unset( self::$fields['restaurant']['restaurant_category'] );
		}
	}

	/**
	 * Get post data for fields
	 *
	 * @return array of data
	 */
	protected static function get_posted_fields() {
		
		self::init_fields();

		$values = array();

		foreach ( self::$fields as $group_key => $fields ) {
			foreach ( $fields as $key => $field ) {
				// Get the value
				$field_type = str_replace( '-', '_', $field['type'] );
				
				if ( method_exists( __CLASS__, "get_posted_{$field_type}_field" ) ) {
					$values[ $group_key ][ $key ] = call_user_func( __CLASS__ . "::get_posted_{$field_type}_field", $key, $field );
				} else {
					$values[ $group_key ][ $key ] = self::get_posted_field( $key, $field );
				}

				// Set fields value
				self::$fields[ $group_key ][ $key ]['value'] = $values[ $group_key ][ $key ];
			}
		}

		return $values;
	}

	/**
	 * Get the value of a posted field
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	protected static function get_posted_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? sanitize_text_field( trim( stripslashes( $_POST[ $key ] ) ) ) : '';
	}

	/**
	 * Get the value of a posted multiselect field
	 * @param  string $key
	 * @param  array $field
	 * @return array
	 */
	protected static function get_posted_multiselect_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? array_map( 'sanitize_text_field',  $_POST[ $key ] ) : array();
	}

	/**
	 * Get the value of a posted file field
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	protected static function get_posted_file_field( $key, $field ) {
		$file = self::upload_file( $key, $field );
		
		if ( ! $file )
			$file = self::get_posted_field( 'current_' . $key, $field );

		return $file;
	}

	/**
	 * Get the value of a posted textarea field
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	protected static function get_posted_textarea_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? wp_kses_post( trim( stripslashes( $_POST[ $key ] ) ) ) : '';
	}

	/**
	 * Get the value of a posted textarea field
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	protected static function get_posted_wp_editor_field( $key, $field ) {
		return self::get_posted_textarea_field( $key, $field );
	}

	/**
	 * Validate the posted fields
	 *
	 * @return bool on success, WP_ERROR on failure
	 */
	protected static function validate_fields( $values ) {
		foreach ( self::$fields as $group_key => $fields ) {
			foreach ( $fields as $key => $field ) {
				if ( $field['required'] && empty( $values[ $group_key ][ $key ] ) ) {
					return new WP_Error( 'validation-error', sprintf( __( '%s is a required field', 'wp-review-restaurant' ), $field['label'] ) );
				}
			}
		}

		// Application method
		if ( isset( $values['restaurant']['application'] ) ) {
			$allowed_application_method = get_option( 'review_restaurant_allowed_application_method', '' );
			switch ( $allowed_application_method ) {
				case 'email' :
					if ( ! is_email( $values['restaurant']['application'] ) ) {
						throw new Exception( __( 'Please enter a valid application email address', 'wp-review-restaurant' ) );
					}
				break;
				case 'url' :
					if ( ! strstr( $values['restaurant']['application'], 'http:' ) && ! strstr( $values['restaurant']['application'], 'https:' ) ) {
						throw new Exception( __( 'Please enter a valid application URL', 'wp-review-restaurant' ) );
					}
				break;
				default :
					if ( ! is_email( $values['restaurant']['application'] ) && ! strstr( $values['restaurant']['application'], 'http:' ) && ! strstr( $values['restaurant']['application'], 'https:' ) ) {
						throw new Exception( __( 'Please enter a valid application email address or URL', 'wp-review-restaurant' ) );
					}
				break;
			}
		}

		return apply_filters( 'submit_restaurant_form_validate_fields', true, self::$fields, $values );
	}

	/**
	 * restaurant_types function.
	 *
	 * @access private
	 * @return void
	 */
	private static function restaurant_types() {
		$options = array();
		$terms   = get_restaurant_listing_types();
		foreach ( $terms as $term )
			$options[ $term->slug ] = $term->name;
		return $options;
	}
	
	/**
	 * restaurant_types_cuisine function.
	 *
	 * @access private
	 * @return void
	 */
	private static function restaurant_types_cuisine() {
		$options = array();
		$terms   = get_restaurant_listing_types_cuisine();
		foreach ( $terms as $term )
			$options[ $term->slug ] = $term->name;
		return $options;
	}

	/**
	 * restaurant_types function.
	 *
	 * @access private
	 * @return void
	 */
	private static function restaurant_categories() {
		$options = array();
		$terms   = get_restaurant_listing_categories();
		foreach ( $terms as $term )
			$options[ $term->slug ] = $term->name;
		return $options;
	}

	/**
	 * Process function. all processing code if needed - can also change view if step is complete
	 */
	public static function process() {
		$keys = array_keys( self::$steps );

		if ( isset( $keys[ self::$step ] ) && is_callable( self::$steps[ $keys[ self::$step ] ]['handler'] ) ) {
			call_user_func( self::$steps[ $keys[ self::$step ] ]['handler'] );
		}
	}

	/**
	 * output function. Call the view handler.
	 */
	public static function output() {
		$keys = array_keys( self::$steps );

		self::show_errors();

		if ( isset( $keys[ self::$step ] ) && is_callable( self::$steps[ $keys[ self::$step ] ]['view'] ) ) {
			call_user_func( self::$steps[ $keys[ self::$step ] ]['view'] );
		}
	}

	/**
	 * Submit Step
	 */
	public static function submit() {
		global $review_restaurant, $post;

		self::init_fields();

		// Load data if neccessary
		if ( ! empty( $_POST['edit_restaurant'] ) && self::$restaurant_id ) {
			$restaurant = get_post( self::$restaurant_id );
			foreach ( self::$fields as $group_key => $fields ) {
				foreach ( $fields as $key => $field ) {
					switch ( $key ) {
						case 'restaurant_title' :
							self::$fields[ $group_key ][ $key ]['value'] = $restaurant->post_title;
						break;
						case 'restaurant_description' :
							self::$fields[ $group_key ][ $key ]['value'] = $restaurant->post_content;
						break;
						case 'restaurant_type' :
							self::$fields[ $group_key ][ $key ]['value'] = current( wp_get_object_terms( $restaurant->ID, 'restaurant_listing_type', array( 'fields' => 'slugs' ) ) );
						break;
						case 'restaurant_category' :
							self::$fields[ $group_key ][ $key ]['value'] = current( wp_get_object_terms( $restaurant->ID, 'restaurant_listing_category', array( 'fields' => 'slugs' ) ) );
						break;
						default:
							self::$fields[ $group_key ][ $key ]['value'] = get_post_meta( $restaurant->ID, '_' . $key, true );
						break;
					}
				}
			}

			self::$fields = apply_filters( 'submit_restaurant_form_fields_get_restaurant_data', self::$fields, $restaurant );

		// Get user meta
		} elseif ( is_user_logged_in() && empty( $_POST ) ) {
			if ( ! empty( self::$fields['restaurant'] ) ) {
				foreach ( self::$fields['restaurant'] as $key => $field ) {
					self::$fields['restaurant'][ $key ]['value'] = get_user_meta( get_current_user_id(), '_' . $key, true );
				}
			}
			if ( ! empty( self::$fields['restaurant']['application'] ) ) {
				$allowed_application_method = get_option( 'review_restaurant_allowed_application_method', '' );
				if ( $allowed_application_method !== 'url' ) {
					$current_user = wp_get_current_user();
					self::$fields['restaurant']['application']['value'] = $current_user->user_email;
				}
			}
			self::$fields = apply_filters( 'submit_restaurant_form_fields_get_user_data', self::$fields, get_current_user_id() );
		}

		wp_enqueue_script( 'wp-review-restaurant-restaurant-submission' );

		get_review_restaurant_template( 'restaurant-submit.php', array(
			'form'               => self::$form_name,
			'restaurant_id'             => self::get_restaurant_id(),
			'action'             => self::get_action(),
			'restaurant_fields'         => self::get_fields( 'restaurant' ),
			'restaurant_fields'     => self::get_fields( 'restaurant' ),
			'submit_button_text' => __( 'Preview restaurant listing &rarr;', 'wp-review-restaurant' )
			) );
	}

	/**
	 * Submit Step is posted
	 */
	public static function submit_handler() {
		try {
				
			// Init fields
			self::init_fields();
			
			// Get posted values
			$values = self::get_posted_fields();

			if ( empty( $_POST['submit_restaurant'] ) ) {
				return;
			}

			// Validate required
			if ( is_wp_error( ( $return = self::validate_fields( $values ) ) ) ) {
				throw new Exception( $return->get_error_message() );
			}

			// Account creation
			if ( ! is_user_logged_in() ) {
				$create_account = false;

				if ( review_restaurant_enable_registration() && ! empty( $_POST['create_account_email'] ) )
					$create_account = wp_review_restaurant_create_account( $_POST['create_account_email'], get_option( 'review_restaurant_registration_role' ) );

				if ( is_wp_error( $create_account ) )
					throw new Exception( $create_account->get_error_message() );
			}

			if ( review_restaurant_user_requires_account() && ! is_user_logged_in() )
				throw new Exception( __( 'You must be signed in to post a new restaurant listing.' ) );

			// Update the restaurant
			self::save_restaurant( $values['restaurant']['restaurant_title'], $values['restaurant']['restaurant_description'], self::$restaurant_id ? '' : 'preview', $values );
			self::update_restaurant_data( $values );

			// Successful, show next step
			self::$step ++;

		} catch ( Exception $e ) {
			self::add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Update or create a restaurant listing from posted data
	 *
	 * @param  string $post_title
	 * @param  string $post_content
	 * @param  string $status
	 */
	protected static function save_restaurant( $post_title, $post_content, $status = 'preview', $values = array() ) {
			
		$restaurant_slug   = array();

		// Prepend with restaurant name
		if ( ! empty( $values['restaurant']['restaurant_name'] ) )
			$restaurant_slug[] = $values['restaurant']['restaurant_name'];

		// Prepend location
		if ( ! empty( $values['restaurant']['restaurant_location'] ) )
			$restaurant_slug[] = $values['restaurant']['restaurant_location'];

		// Prepend with restaurant type
		if ( ! empty( $values['restaurant']['restaurant_type'] ) )
			$restaurant_slug[] = $values['restaurant']['restaurant_type'];

		$restaurant_slug[] = $post_title;

		$restaurant_data  = apply_filters( 'submit_restaurant_form_save_restaurant_data', array(
			'post_title'     => $post_title,
			'post_name'      => sanitize_title( implode( '-', $restaurant_slug ) ),
			'post_content'   => $post_content,
			'post_type'      => 'restaurant_listing',
			'comment_status' => 'closed'
		), $post_title, $post_content, $status, $values );

		if ( $status )
			$restaurant_data['post_status'] = $status;

		if ( self::$restaurant_id ) {
			$restaurant_data['ID'] = self::$restaurant_id;
			wp_update_post( $restaurant_data );
		} else {
			self::$restaurant_id = wp_insert_post( $restaurant_data );
		}
	}

	/**
	 * Set restaurant meta + terms based on posted values
	 *
	 * @param  array $values
	 */
	protected static function update_restaurant_data( $values ) {

		wp_set_object_terms( self::$restaurant_id, array( $values['restaurant']['restaurant_type'] ), 'restaurant_listing_type', false );

		if ( get_option( 'review_restaurant_enable_categories' ) && isset( $values['restaurant']['restaurant_category'] ) ) {
			wp_set_object_terms( self::$restaurant_id, ( is_array( $values['restaurant']['restaurant_category'] ) ? $values['restaurant']['restaurant_category'] : array( $values['restaurant']['restaurant_category'] ) ), 'restaurant_listing_category', false );
		}

		update_post_meta( self::$restaurant_id, '_application', $values['restaurant']['application'] );
		update_post_meta( self::$restaurant_id, '_restaurant_location', $values['restaurant']['restaurant_location'] );
		update_post_meta( self::$restaurant_id, '_restaurant_name', $values['restaurant']['restaurant_name'] );
		update_post_meta( self::$restaurant_id, '_restaurant_website', $values['restaurant']['restaurant_website'] );
		update_post_meta( self::$restaurant_id, '_restaurant_tagline', $values['restaurant']['restaurant_tagline'] );
		update_post_meta( self::$restaurant_id, '_restaurant_twitter', $values['restaurant']['restaurant_twitter'] );
		update_post_meta( self::$restaurant_id, '_restaurant_logo', $values['restaurant']['restaurant_logo'] );
		add_post_meta( self::$restaurant_id, '_filled', 0, true );
		add_post_meta( self::$restaurant_id, '_featured', 0, true );

		// And user meta to save time in future
		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), '_restaurant_name', $values['restaurant']['restaurant_name'] );
			update_user_meta( get_current_user_id(), '_restaurant_website', $values['restaurant']['restaurant_website'] );
			update_user_meta( get_current_user_id(), '_restaurant_tagline', $values['restaurant']['restaurant_tagline'] );
			update_user_meta( get_current_user_id(), '_restaurant_twitter', $values['restaurant']['restaurant_twitter'] );
			update_user_meta( get_current_user_id(), '_restaurant_logo', $values['restaurant']['restaurant_logo'] );
		}

		do_action( 'review_restaurant_update_restaurant_data', self::$restaurant_id, $values );
	}

	/**
	 * Preview Step
	 */
	public static function preview() {
		global $review_restaurant, $post;

		if ( self::$restaurant_id ) {

			$post = get_post( self::$restaurant_id );
			setup_postdata( $post );

			?>
			<form method="post" id="restaurant_preview">
				<div class="restaurant_listing_preview_title">
					<input type="submit" name="continue" id="restaurant_preview_submit_button" class="button" value="<?php echo apply_filters( 'submit_restaurant_step_preview_submit_text', __( 'Submit Listing &rarr;', 'wp-review-restaurant' ) ); ?>" />
					<input type="submit" name="edit_restaurant" class="button" value="<?php _e( '&larr; Edit listing', 'wp-review-restaurant' ); ?>" />
					<input type="hidden" name="restaurant_id" value="<?php echo esc_attr( self::$restaurant_id ); ?>" />
					<input type="hidden" name="step" value="<?php echo esc_attr( self::$step ); ?>" />
					<input type="hidden" name="review_restaurant_form" value="<?php echo self::$form_name; ?>" />
					<h2>
						<?php _e( 'Preview', 'wp-review-restaurant' ); ?>
					</h2>
				</div>
				<div class="restaurant_listing_preview single_restaurant_listing">
					<h1><?php the_title(); ?></h1>
					<?php get_review_restaurant_template_part( 'content-single', 'restaurant_listing' ); ?>
				</div>
			</form>
			<?php

			wp_reset_postdata();
		}
	}

	/**
	 * Preview Step Form handler
	 */
	public static function preview_handler() {
		if ( ! $_POST )
			return;

		// Edit = show submit form again
		if ( ! empty( $_POST['edit_restaurant'] ) ) {
			self::$step --;
		}
		// Continue = change restaurant status then show next screen
		if ( ! empty( $_POST['continue'] ) ) {

			$restaurant = get_post( self::$restaurant_id );

			if ( $restaurant->post_status == 'preview' ) {
				$update_restaurant                = array();
				$update_restaurant['ID']          = $restaurant->ID;
				$update_restaurant['post_status'] = get_option( 'review_restaurant_submission_requires_approval' ) ? 'pending' : 'publish';
				wp_update_post( $update_restaurant );
			}

			self::$step ++;
		}
	}

	/**
	 * Done Step
	 */
	public static function done() {
		do_action( 'review_restaurant_review_submitted', self::$restaurant_id );

		get_review_restaurant_template( 'restaurant-submitted.php', array( 'restaurant' => get_post( self::$restaurant_id ) ) );
	}

	/**
	 * Upload an image
	 */
	public static function upload_image( $field_key, $field = '' ) {
		return self::upload_file( $field_key, $field );
	}

	/**
	 * Upload a file
	 */
	public static function upload_file( $field_key, $field ) {

		/** WordPress Administration File API */
		include_once( ABSPATH . 'wp-admin/includes/file.php' );

		/** WordPress Media Administration API */
		include_once( ABSPATH . 'wp-admin/includes/media.php' );

		if ( isset( $_FILES[ $field_key ] ) && ! empty( $_FILES[ $field_key ] ) && ! empty( $_FILES[ $field_key ]['name'] ) ) {
			$file   = $_FILES[ $field_key ];

			if ( ! empty( $field['allowed_mime_types'] ) ) {
				$allowed_mime_types = $field['allowed_mime_types'];
			} else {
				$allowed_mime_types = get_allowed_mime_types();
			}

			if ( ! in_array( $_FILES[ $field_key ]["type"], $allowed_mime_types ) )
    			throw new Exception( sprintf( __( '"%s" needs to be one of the following file types: %s', 'wp-review-restaurant' ), $field['label'], implode( ', ', array_keys( $allowed_mime_types ) ) ) );

			add_filter( 'upload_dir',  array( __CLASS__, 'upload_dir' ) );

			$upload = wp_handle_upload( $file, array( 'test_form' => false ) );

			remove_filter('upload_dir', array( __CLASS__, 'upload_dir' ) );

			if ( ! empty( $upload['error'] ) ) {
				throw new Exception( $upload['error'] );
			} else {
				return $upload['url'];
			}
		}
	}

	/**
	 * Filter the upload directory
	 */
	public static function upload_dir( $pathdata ) {
		$subdir             = '/restaurant_listings';
		$pathdata['path']   = str_replace( $pathdata['subdir'], $subdir, $pathdata['path'] );
		$pathdata['url']    = str_replace( $pathdata['subdir'], $subdir, $pathdata['url'] );
		$pathdata['subdir'] = str_replace( $pathdata['subdir'], $subdir, $pathdata['subdir'] );
		return $pathdata;
	}
}