<?php
/**
 * WP_Review_Restaurant_Forms class.
 */
class WP_Review_Restaurant_Forms {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_posted_form' ) );
	}

	/**
	 * If a form was posted, load its class so that it can be processed before display.
	 */
	public function load_posted_form() {
		if ( ! empty( $_POST['review_restaurant_form'] ) ) {
			$this->load_form_class( sanitize_title( $_POST['review_restaurant_form'] ) );
		}
	}

	/**
	 * Load a form's class
	 *
	 * @param  string $form_name
	 * @return string class name on success, false on failure
	 */
	private function load_form_class( $form_name ) {
		global $review_restaurant;

		// Load the form abtract
		if ( ! class_exists( 'WP_Review_Restaurant_Form' ) )
			include( 'abstracts/abstract-wp-review-restaurant-form.php' );

		// Now try to load the form_name
		$form_class  = 'WP_Review_Restaurant_Form_' . str_replace( '-', '_', $form_name );
		$form_file   = REVIEW_RESTAURANT_PLUGIN_DIR . '/includes/forms/class-wp-review-restaurant-form-' . $form_name . '.php';

		if ( class_exists( $form_class ) )
			return $form_class;

		if ( ! file_exists( $form_file ) )
			return false;

		if ( ! class_exists( $form_class ) )
			include $form_file;

		// Init the form
		call_user_func( array( $form_class, "init" ) );

		return $form_class;
	}

	/**
	 * get_form function.
	 *
	 * @access public
	 * @param mixed $form_name
	 * @return string
	 */
	public function get_form( $form_name ) {
		if ( $form = $this->load_form_class( $form_name ) ) {
			ob_start();
			call_user_func( array( $form, "output" ) );
			return ob_get_clean();
		}
	}

}