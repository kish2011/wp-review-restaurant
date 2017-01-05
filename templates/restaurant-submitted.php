<?php
switch ( $restaurant->post_status ) :
	case 'publish' :
		printf( __( 'Restaurant listed successfully. To view your restaurant listing <a href="%s">click here</a>.', 'wp-review-restaurant' ), get_permalink( $restaurant->ID ) );
	break;
	case 'pending' :
		printf( __( 'Restaurant submitted successfully. Your restaurant listing will be visible once approved.', 'wp-review-restaurant' ), get_permalink( $restaurant->ID ) );
	break;
	default :
		do_action( 'review_restaurant_review_submitted_content_' . str_replace( '-', '_', sanitize_title( $restaurant->post_status ) ), $restaurant );
	break;
endswitch;