<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Review_Restaurant_Rating
 *
 * Obtains Rating data for posted restaurants.
 */
class WP_Review_Restaurant_Rating {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'comment_form_default_fields', array( $this, 'custom_fields' ) );
		add_action( 'comment_form_logged_in_after', array( $this, 'additional_fields' ) );
		add_action( 'comment_form_after_fields', array( $this, 'additional_fields' ) );
		add_action( 'comment_post', array( $this, 'save_comment_meta_data' ) );
		add_filter( 'preprocess_comment', array( $this, 'verify_comment_meta_data' ) );
		add_action( 'add_meta_boxes_comment', array( $this, 'extend_comment_add_meta_box' ) );
		add_action( 'edit_comment', array( $this, 'extend_comment_edit_metafields' ) );
		add_filter( 'comment_text', array( $this, 'modify_comment' ) );
	}

	/**
	 * Add custom meta (ratings) fields to the default comment form
	 * Default comment form includes name, email and URL
	 * Default comment form elements are hidden when user is logged in
	 */
	public function custom_fields($fields) {
		global $post;
		if ( $post->post_type == "restaurant_listing" ) {
			$commenter = wp_get_current_commenter();
			$req = get_option( 'require_name_email' );
			$aria_req = ( $req ? " aria-required='true'" : '' );

			$fields[ 'author' ] = '<p class="comment-form-author">'.
				'<label for="author">' . __( 'Name' ) . '</label>'.
				( $req ? '<span class="required">*</span>' : '' ).
				'<input id="author" name="author" type="text" value="'. esc_attr( $commenter['comment_author'] ) . 
				'" size="30" tabindex="1"' . $aria_req . ' /></p>';
			
			$fields[ 'email' ] = '<p class="comment-form-email">'.
				'<label for="email">' . __( 'Email' ) . '</label>'.
				( $req ? '<span class="required">*</span>' : '' ).
				'<input id="email" name="email" type="text" value="'. esc_attr( $commenter['comment_author_email'] ) . 
				'" size="30"  tabindex="2"' . $aria_req . ' /></p>';
						
			$fields[ 'url' ] = '<p class="comment-form-url">'.
				'<label for="url">' . __( 'Website' ) . '</label>'.
				'<input id="url" name="url" type="text" value="'. esc_attr( $commenter['comment_author_url'] ) . 
				'" size="30"  tabindex="3" /></p>';

			$fields[ 'phone' ] = '<p class="comment-form-phone">'.
				'<label for="phone">' . __( 'Phone' ) . '</label>'.
				'<input id="phone" name="phone" type="text" size="30"  tabindex="4" /></p>';
		}

		return $fields;
	}

	/**
	 * Add fields after default fields above the comment box, always visible
	 */
	public function additional_fields () {
		global $post;
		if ( $post->post_type == "restaurant_listing" ) {
			echo '<p class="comment-form-rating">'.
			'<label for="rating">'. __('Rating') . '<span class="required">*</span></label>
			<span class="commentratingbox">';
			
			for( $i=1; $i <= 5; $i++ )
			echo '<span class="commentrating"><input type="radio" name="rating" id="rating" value="'. $i .'"/>'. $i .'</span>';

			echo'</span></p>';
		}
	}

	/**
	 * Save the comment meta data along with comment
	 * @param  int  $comment_id
	 */
	public function save_comment_meta_data( $comment_id ) {
		if ( ( isset( $_POST['phone'] ) ) && ( $_POST['phone'] != '') )
		$phone = wp_filter_nohtml_kses($_POST['phone']);
		add_comment_meta( $comment_id, 'phone', $phone );

		if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') )
		$title = wp_filter_nohtml_kses($_POST['title']);
		add_comment_meta( $comment_id, 'title', $title );

		if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') )
		$rating = wp_filter_nohtml_kses($_POST['rating']);
		add_comment_meta( $comment_id, 'rating', $rating );
	}

	/**
	 * Add the filter to check if the comment meta data has been filled or not
	 * @param  string $commentdata
	 * @return string $commentdata
	 */
	public function verify_comment_meta_data( $commentdata ) {
		if ( ! isset( $_POST['rating'] ) && $post->post_type == "restaurant_listing" )
		wp_die( __( 'Error: You did not add your rating. Hit the BACK button of your Web browser and resubmit your comment with rating.' ) );
		return $commentdata;
	}

	/**
	 * Add an edit option in comment edit screen  
	 */
	public function extend_comment_add_meta_box() {
		add_meta_box( 'title', __( 'Comment Metadata - Extend Comment' ), 'extend_comment_meta_box', 'comment', 'normal', 'high' );
	}

	/**
	 * Save any returned data to post meta
	 * @param  int $comment_id
	 */
	public function extend_comment_edit_metafields( $comment_id ) {
		if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;

		if ( ( isset( $_POST['phone'] ) ) && ( $_POST['phone'] != '') ) : 
		$phone = wp_filter_nohtml_kses($_POST['phone']);
		update_comment_meta( $comment_id, 'phone', $phone );
		else :
		delete_comment_meta( $comment_id, 'phone');
		endif;
			
		if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ):
		$title = wp_filter_nohtml_kses($_POST['title']);
		update_comment_meta( $comment_id, 'title', $title );
		else :
		delete_comment_meta( $comment_id, 'title');
		endif;

		if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ):
		$rating = wp_filter_nohtml_kses($_POST['rating']);
		update_comment_meta( $comment_id, 'rating', $rating );
		else :
		delete_comment_meta( $comment_id, 'rating');
		endif;
	}

	/**
	 * Update comment meta data from comment edit screen
	 * @param  string $text
	 * @return string $text
	 */
	public function modify_comment( $text ){
		if( $commenttitle = get_comment_meta( get_comment_ID(), 'title', true ) ) {
			$commenttitle = '<strong>' . esc_attr( $commenttitle ) . '</strong><br/>';
			$text = $commenttitle . $text;
		} 

		if( $commentrating = get_comment_meta( get_comment_ID(), 'rating', true ) ) {
			$commentrating = '<p class="comment-rating">	<img src="'. REVIEW_RESTAURANT_PLUGIN_URL .
			'/assets/images/'. $commentrating . 'star.gif"/><br/>Rating: <strong>'. $commentrating .' / 5</strong></p>';
			$text = $text . $commentrating;
			return $text;		
		} else {
			return $text;		
		}	 
	}
}

new WP_Review_Restaurant_Rating();