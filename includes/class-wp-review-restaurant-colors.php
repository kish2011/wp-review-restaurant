<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WP_Review_Restaurant_Colors class.
 */
class WP_Review_Restaurant_Colors {

	private static $instance;

	public static function instance() {
		if ( ! isset ( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		$this->setup_actions();
	}

	private function setup_actions() {

		if ( is_admin() ) {
			add_filter( 'review_restaurant_settings', array( $this, 'review_restaurant_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'colorpickers' ) );
			add_action( 'admin_footer', array( $this, 'colorpickersjs' ) );
		} else {
			add_action( 'wp_head', array( $this, 'output_colors' ) );
		}
		
	}

	public function review_restaurant_settings( $settings ) {
		$settings[ 'restaurant_colors' ] = array(
			__( 'Restaurant Colors', 'wp-review-restaurant' ),
			$this->create_options()
		);

		return $settings;
	}

	private function create_options() {
		$terms   = get_terms( 'restaurant_listing_type', array( 'hide_empty' => false ) );
		$options = array();

		$options[] = array(
			'name' 		  => 'review_restaurant_restaurant_type_what_color',
			'std' 		  => 'background',
			'placeholder' => '',
			'label' 	  => __( 'What', 'wp-review-restaurant' ),
			'desc'        => __( 'Should these colors be applied to the text color, or background color?', 'wp-review-restaurant' ),
			'type'        => 'select',
			'options'     => array(
				'background' => __( 'Background', 'wp-review-restaurant' ),
				'text'       => __( 'Text', 'wp-review-restaurant' )
			)
		);

		foreach ( $terms as $term ) {
			$options[] = array(
				'name' 		  => 'review_restaurant_restaurant_type_' . $term->term_id . '_color',
				'std' 		  => '',
				'placeholder' => '#',
				'label' 	  => '<strong>' . $term->name . '</strong>',
				'desc'		  => __( 'Hex value for the color of this restaurant type.', 'wp-review-restaurant' ),
				'attributes'  => array(
					'data-default-color' => '#fff',
					'data-type'          => 'colorpicker'
				)
			);
		}

		return $options;
	}

	public function output_colors() {
		$terms   = get_terms( 'restaurant_listing_type', array( 'hide_empty' => false ) );

		echo "<style id='review_restaurant_colors'>\n";

		foreach ( $terms as $term ) {
			$what = 'background' == get_option( 'review_restaurant_restaurant_type_what_color' ) ? 'background-color' : 'color';

			printf( ".restaurant-type.term-%s, .restaurant-type.%s { %s: %s !important; } \n", $term->term_id, $term->slug, $what, get_option( 'review_restaurant_restaurant_type_' . $term->term_id . '_color', '#fff' ) );
		}

		echo "</style>\n";
	}

	public function colorpickers( $hook ) {
		$screen = get_current_screen();

		if ( 'restaurant_listing_page_review-restaurant-settings' != $screen->id )
			return;

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
	}

	public function colorpickersjs() {
		$screen = get_current_screen();

		if ( 'restaurant_listing_page_review-restaurant-settings' != $screen->id )
			return;
		?>
			<script>
				jQuery(document).ready(function($){
					$( 'input[data-type="colorpicker"]' ).wpColorPicker();
				});
			</script>
		<?php
	}
}

add_action( 'init', array( 'WP_Review_Restaurant_Colors', 'instance' ) );