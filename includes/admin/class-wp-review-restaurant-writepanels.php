<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WP_Review_Restaurant_Writepanels {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 1, 2 );
		add_action( 'review_restaurant_save_restaurant_listing', array( $this, 'save_restaurant_listing_data' ), 20, 2 );
	}

	/**
	 * restaurant_listing_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public function restaurant_listing_fields() {
		return apply_filters( 'review_restaurant_restaurant_listing_data_fields', array(
			'_restaurant_location' => array(
				'label' => __( 'Restaurant location', 'wp-review-restaurant' ),
				'placeholder' => __( 'e.g. "London, UK", "New York", "Houston, TX"', 'wp-review-restaurant' ),
				'description' => __( 'Leave this blank if the restaurant can be access from anywhere (i.e. home delivery)', 'wp-review-restaurant' )
			),
			'_application' => array(
				'label' => __( 'Application email/URL', 'wp-review-restaurant' ),
				'placeholder' => __( 'URL or email which applicants use to apply', 'wp-review-restaurant' )
			),
			'_restaurant_name' => array(
				'label' => __( 'Restaurant name', 'wp-review-restaurant' ),
				'placeholder' => ''
			),
			'_restaurant_website' => array(
				'label' => __( 'Restaurant website', 'wp-review-restaurant' ),
				'placeholder' => ''
			),
			'_restaurant_tagline' => array(
				'label' => __( 'Restaurant tagline', 'wp-review-restaurant' ),
				'placeholder' => __( 'Brief description about the restaurant', 'wp-review-restaurant' )
			),
			'_restaurant_twitter' => array(
				'label' => __( 'Restaurant Twitter', 'wp-review-restaurant' ),
				'placeholder' => '@yourrestaurant'
			),
			'_restaurant_logo' => array(
				'label' => __( 'Restaurant logo', 'wp-review-restaurant' ),
				'placeholder' => __( 'URL to the restaurant logo', 'wp-review-restaurant' ),
				'type'  => 'file'
			),
			'_filled' => array(
				'label' => __( 'Restaurant filled?', 'wp-review-restaurant' ),
				'type'  => 'checkbox'
			),
			'_featured' => array(
				'label' => __( 'Feature this restaurant listing?', 'wp-review-restaurant' ),
				'type'  => 'checkbox',
				'description' => __( 'Featured listings will be sticky during searches, and can be styled differently.', 'wp-review-restaurant' )
			),
			'_restaurant_expires' => array(
				'label'       => __( 'Restaurant Expires', 'wp-review-restaurant' ),
				'placeholder' => __( 'yyyy-mm-dd', 'wp-review-restaurant' )
			),
			'_restaurant_author' => array(
				'label' => __( 'Posted by', 'wp-review-restaurant' ),
				'type'  => 'author'
			)
		) );
	}

	/**
	 * add_meta_boxes function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box( 'restaurant_listing_data', __( 'Restaurant Listing Data', 'wp-review-restaurant' ), array( $this, 'restaurant_listing_data' ), 'restaurant_listing', 'normal', 'high' );
	}

	/**
	 * input_text function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public function input_file( $key, $field ) {
		global $thepostid;

		if ( empty( $field['value'] ) )
			$field['value'] = get_post_meta( $thepostid, $key, true );
		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>:</label>
			<input type="text" class="file_url" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />
			<?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?> <button class="button upload_image_button" data-uploader_button_text="<?php _e( 'Use file', 'wp-review-restaurant' ); ?>"><?php _e( 'Upload', 'wp-review-restaurant' ); ?></button>
		</p>
		<script type="text/javascript">
			// Uploading files
			var file_frame;
			var file_target_input;

			jQuery('.upload_image_button').live('click', function( event ){

			    event.preventDefault();

			    file_target_input = jQuery( this ).closest('.form-field').find('.file_url');

			    // If the media frame already exists, reopen it.
			    if ( file_frame ) {
					file_frame.open();
					return;
			    }

			    // Create the media frame.
			    file_frame = wp.media.frames.file_frame = wp.media({
					title: jQuery( this ).data( 'uploader_title' ),
					button: {
						text: jQuery( this ).data( 'uploader_button_text' ),
					},
					multiple: false  // Set to true to allow multiple files to be selected
			    });

			    // When an image is selected, run a callback.
			    file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();

					jQuery( file_target_input ).val( attachment.url );
			    });

			    // Finally, open the modal
			    file_frame.open();
			});
		</script>
		<?php
	}

	/**
	 * input_text function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public function input_text( $key, $field ) {
		global $thepostid;

		if ( empty( $field['value'] ) )
			$field['value'] = get_post_meta( $thepostid, $key, true );
		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>:</label>
			<input type="text" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />
			<?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
		</p>
		<?php
	}

	/**
	 * input_text function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public function input_textarea( $key, $field ) {
		global $thepostid;

		if ( empty( $field['value'] ) )
			$field['value'] = get_post_meta( $thepostid, $key, true );
		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>:</label>
			<textarea name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"><?php echo esc_html( $field['value'] ); ?></textarea>
			<?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
		</p>
		<?php
	}
	
	/**
	 * input_select function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public function input_select( $key, $field ) {
		global $thepostid;

		if ( empty( $field['value'] ) )
			$field['value'] = get_post_meta( $thepostid, $key, true );
		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>:</label>
			<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>">
				<?php foreach ( $field['options'] as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php if ( isset( $field['value'] ) ) selected( $field['value'], $key ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
		</p>
		<?php
	}

	/**
	 * input_select function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public function input_multiselect( $key, $field ) {
		global $thepostid;

		if ( empty( $field['value'] ) )
			$field['value'] = get_post_meta( $thepostid, $key, true );
		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>:</label>
			<select multiple="multiple" name="<?php echo esc_attr( $key ); ?>[]" id="<?php echo esc_attr( $key ); ?>">
				<?php foreach ( $field['options'] as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php if ( ! empty( $field['value'] ) && is_array( $field['value'] ) ) selected( in_array( $key, $field['value'] ), true ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
		</p>
		<?php
	}

	/**
	 * input_checkbox function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public function input_checkbox( $key, $field ) {
		global $thepostid;

		if ( empty( $field['value'] ) )
			$field['value'] = get_post_meta( $thepostid, $key, true );
		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?></label>
			<input type="checkbox" class="checkbox" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $field['value'], 1 ); ?> />
			<?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
		</p>
		<?php
	}

	/**
	 * Box to choose who posted the restaurant
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public function input_author( $key, $field ) {
		global $thepostid, $post;

		if ( empty( $field['value'] ) )
			$field['value'] = get_post_meta( $thepostid, $key, true );
		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) ; ?>:</label>
			<?php
				wp_dropdown_users( array(
					'who'              => '',
					'show_option_none' => __( 'Guest user', 'wp-review-restaurant' ),
					'name'             => $key,
					'selected'         => $post->post_author,
					'include_selected' => true
				) );
			?>
			<?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><?php echo $field['description']; ?></span><?php endif; ?>
		</p>
		<?php	
	}

	/**
	 * restaurant_listing_data function.
	 *
	 * @access public
	 * @param mixed $post
	 * @return void
	 */
	public function restaurant_listing_data( $post ) {
		global $post, $thepostid;

		$thepostid = $post->ID;

		echo '<div class="wp_review_restaurant_meta_data">';

		wp_nonce_field( 'save_meta_data', 'review_restaurant_nonce' );

		do_action( 'review_restaurant_restaurant_listing_data_start', $thepostid );

		foreach ( $this->restaurant_listing_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : 'text';

			if ( method_exists( $this, 'input_' . $type ) )
				call_user_func( array( $this, 'input_' . $type ), $key, $field );
			else
				do_action( 'review_restaurant_input_' . $type, $key, $field );
		}

		do_action( 'review_restaurant_restaurant_listing_data_end', $thepostid );

		echo '</div>';
	}

	/**
	 * save_post function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public function save_post( $post_id, $post ) {
		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) return;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		if ( is_int( wp_is_post_revision( $post ) ) ) return;
		if ( is_int( wp_is_post_autosave( $post ) ) ) return;
		if ( empty($_POST['review_restaurant_nonce']) || ! wp_verify_nonce( $_POST['review_restaurant_nonce'], 'save_meta_data' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( $post->post_type != 'restaurant_listing' ) return;

		do_action( 'review_restaurant_save_restaurant_listing', $post_id, $post );
	}

	/**
	 * save_restaurant_listing_data function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public function save_restaurant_listing_data( $post_id, $post ) {
		global $wpdb;

		// These need to exist
		add_post_meta( $post_id, '_filled', 0, true );
		add_post_meta( $post_id, '_featured', 0, true );

		// Save fields
		foreach ( $this->restaurant_listing_fields() as $key => $field ) {
			// Expirey date
			if ( '_restaurant_expires' === $key ) {
				if ( ! empty( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, $key, date( 'Y-m-d', strtotime( sanitize_text_field( $_POST[ $key ] ) ) ) );
				} else {
					update_post_meta( $post_id, $key, '' );
				}
			}

			// Locations
			elseif ( '_restaurant_location' === $key ) {
				if ( update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) ) ) {
					do_action( 'review_restaurant_review_location_edited', $post_id, sanitize_text_field( $_POST[ $key ] ) );
				} elseif ( apply_filters( 'review_restaurant_geolocation_enabled', true ) && ! WP_Review_Restaurant_Geocode::has_location_data( $post_id ) ) {
					WP_Review_Restaurant_Geocode::generate_location_data( $post_id, sanitize_text_field( $_POST[ $key ] ) );
				}
			}

			elseif( '_restaurant_author' === $key ) {
				$wpdb->update( $wpdb->posts, array( 'post_author' => $_POST[ $key ] > 0 ? absint( $_POST[ $key ] ) : 0 ), array( 'ID' => $post_id ) );
			}

			// Everything else
			else {
				$type = ! empty( $field['type'] ) ? $field['type'] : '';

				switch ( $type ) {
					case 'textarea' :
						update_post_meta( $post_id, $key, wp_kses_post( stripslashes( $_POST[ $key ] ) ) );
					break;
					case 'checkbox' :
						if ( isset( $_POST[ $key ] ) ) {
							update_post_meta( $post_id, $key, 1 );
						} else {
							update_post_meta( $post_id, $key, 0 );
						}
					break;
					default : 
						if ( is_array( $_POST[ $key ] ) ) {
							update_post_meta( $post_id, $key, array_map( 'sanitize_text_field', $_POST[ $key ] ) );
						} else {
							update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
						}
					break;
				}
			}
		}
	}
}

new WP_Review_Restaurant_Writepanels();