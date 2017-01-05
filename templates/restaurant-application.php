<?php if ( $enquire = get_the_restaurant_application_method() ) :
	wp_enqueue_script( 'wp-review-restaurant-restaurant-application' );
	?>
	<div class="application">
		<input class="application_button" type="button" value="<?php _e( 'Enquire for restaurant', 'wp-review-restaurant' ); ?>" />

		<div class="application_details">
			<?php
				/**
				 * review_restaurant_application_details_email or review_restaurant_application_details_url hook
				 */
				do_action( 'review_restaurant_application_details_' . $enquire->type, $enquire );
			?>
		</div>
	</div>
<?php endif; ?>