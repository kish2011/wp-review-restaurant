<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Review_Restaurant_CPT class.
 */
class WP_Review_Restaurant_CPT {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );
		add_filter( 'manage_edit-restaurant_listing_columns', array( $this, 'columns' ) );
		add_action( 'manage_restaurant_listing_posts_custom_column', array( $this, 'custom_columns' ), 2 );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_action( 'admin_footer-edit.php', array( $this, 'add_bulk_actions' ) );
		add_action( 'load-edit.php', array( $this, 'do_bulk_actions' ) );
		add_action( 'admin_init', array( $this, 'approve_restaurant' ) );
		add_action( 'admin_notices', array( $this, 'approved_notice' ) );
		add_action( 'admin_notices', array( $this, 'expired_notice' ) );

		if ( get_option( 'review_restaurant_enable_categories' ) )
			add_action( "restrict_manage_posts", array( $this, "restaurants_by_category" ) );

		foreach ( array( 'post', 'post-new' ) as $hook )
			add_action( "admin_footer-{$hook}.php", array( $this,'extend_submitdiv_post_status' ) );
	}

	/**
	 * Edit bulk actions
	 */
	public function add_bulk_actions() {
		global $post_type;

		if ( $post_type == 'restaurant_listing' ) {
			?>
			<script type="text/javascript">
		      jQuery(document).ready(function() {
		        jQuery('<option>').val('approve_restaurants').text('<?php _e( 'Approve Restaurants', 'wp-review-restaurant' )?>').appendTo("select[name='action']");
		        jQuery('<option>').val('approve_restaurants').text('<?php _e( 'Approve Restaurants', 'wp-review-restaurant' )?>').appendTo("select[name='action2']");

		        jQuery('<option>').val('expire_restaurants').text('<?php _e( 'Expire Restaurants', 'wp-review-restaurant' )?>').appendTo("select[name='action']");
		        jQuery('<option>').val('expire_restaurants').text('<?php _e( 'Expire Restaurants', 'wp-review-restaurant' )?>').appendTo("select[name='action2']");
		      });
		    </script>
		    <?php
		}
	}

	/**
	 * Do custom bulk actions
	 */
	public function do_bulk_actions() {
		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action        = $wp_list_table->current_action();

		switch( $action ) {
			case 'approve_restaurants' :
				check_admin_referer( 'bulk-posts' );

				$post_ids      = array_map( 'absint', array_filter( (array) $_GET['post'] ) );
				$approved_restaurants = array();

				if ( ! empty( $post_ids ) )
					foreach( $post_ids as $post_id ) {
						$restaurant_data = array(
							'ID'          => $post_id,
							'post_status' => 'publish'
						);
						if ( get_post_status( $post_id ) == 'pending' && wp_update_post( $restaurant_data ) )
							$approved_restaurants[] = $post_id;
					}

				wp_redirect( add_query_arg( 'approve_restaurants', $approved_restaurants, remove_query_arg( array( 'approved_restaurants', 'expired_restaurants' ), admin_url( 'edit.php?post_type=restaurant_listing' ) ) ) );
				exit;
			break;
			case 'expire_restaurants' :
				check_admin_referer( 'bulk-posts' );

				$post_ids     = array_map( 'absint', array_filter( (array) $_GET['post'] ) );
				$expired_restaurants = array();

				if ( ! empty( $post_ids ) )
					foreach( $post_ids as $post_id ) {
						$restaurant_data = array(
							'ID'          => $post_id,
							'post_status' => 'expired'
						);
						if ( wp_update_post( $restaurant_data ) )
							$expired_restaurants[] = $post_id;
					}

				wp_redirect( add_query_arg( 'expired_restaurants', $expired_restaurants, remove_query_arg( array( 'approved_restaurants', 'expired_restaurants' ), admin_url( 'edit.php?post_type=restaurant_listing' ) ) ) );
				exit;
			break;
		}

		return;
	}

	/**
	 * Approve a single restaurant
	 */
	public function approve_restaurant() {
		if ( ! empty( $_GET['approve_restaurant'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'approve_restaurant' ) && current_user_can( 'edit_post', $_GET['approve_restaurant'] ) ) {
			$post_id = absint( $_GET['approve_restaurant'] );
			$restaurant_data = array(
				'ID'          => $post_id,
				'post_status' => 'publish'
			);
			wp_update_post( $restaurant_data );
			wp_redirect( remove_query_arg( 'approve_restaurant', add_query_arg( 'approved_restaurants', $post_id, admin_url( 'edit.php?post_type=restaurant_listing' ) ) ) );
			exit;
		}
	}

	/**
	 * Show a notice if we did a bulk action or approval
	 */
	public function approved_notice() {
		 global $post_type, $pagenow;

		if ( $pagenow == 'edit.php' && $post_type == 'restaurant_listing' && ! empty( $_REQUEST['approved_restaurants'] ) ) {
			$approved_restaurants = $_REQUEST['approved_restaurants'];
			if ( is_array( $approved_restaurants ) ) {
				$approved_restaurants = array_map( 'absint', $approved_restaurants );
				$titles        = array();
				foreach ( $approved_restaurants as $restaurant_id )
					$titles[] = get_the_title( $restaurant_id );
				echo '<div class="updated"><p>' . sprintf( __( '%s approved', 'wp-review-restaurant' ), '&quot;' . implode( '&quot;, &quot;', $titles ) . '&quot;' ) . '</p></div>';
			} else {
				echo '<div class="updated"><p>' . sprintf( __( '%s approved', 'wp-review-restaurant' ), '&quot;' . get_the_title( $approved_restaurants ) . '&quot;' ) . '</p></div>';
			}
		}
	}

	/**
	 * Show a notice if we did a bulk action or approval
	 */
	public function expired_notice() {
		 global $post_type, $pagenow;

		if ( $pagenow == 'edit.php' && $post_type == 'restaurant_listing' && ! empty( $_REQUEST['expired_restaurants'] ) ) {
			$expired_restaurants = $_REQUEST['expired_restaurants'];
			if ( is_array( $expired_restaurants ) ) {
				$expired_restaurants = array_map( 'absint', $expired_restaurants );
				$titles        = array();
				foreach ( $expired_restaurants as $restaurant_id )
					$titles[] = get_the_title( $restaurant_id );
				echo '<div class="updated"><p>' . sprintf( __( '%s expired', 'wp-review-restaurant' ), '&quot;' . implode( '&quot;, &quot;', $titles ) . '&quot;' ) . '</p></div>';
			} else {
				echo '<div class="updated"><p>' . sprintf( __( '%s expired', 'wp-review-restaurant' ), '&quot;' . get_the_title( $expired_restaurants ) . '&quot;' ) . '</p></div>';
			}
		}
	}

	/**
	 * restaurants_by_category function.
	 *
	 * @access public
	 * @param int $show_counts (default: 1)
	 * @param int $hierarchical (default: 1)
	 * @param int $show_uncategorized (default: 1)
	 * @param string $orderby (default: '')
	 * @return void
	 */
	public function restaurants_by_category( $show_counts = 1, $hierarchical = 1, $show_uncategorized = 1, $orderby = '' ) {
		global $typenow, $wp_query;

	    if ( $typenow != 'restaurant_listing' || ! taxonomy_exists( 'restaurant_listing_category' ) )
	    	return;

		include_once( 'class-wp-review-restaurant-category-walker.php' );

		$r = array();
		$r['pad_counts'] 	= 1;
		$r['hierarchical'] 	= $hierarchical;
		$r['hide_empty'] 	= 0;
		$r['show_count'] 	= $show_counts;
		$r['selected'] 		= ( isset( $wp_query->query['restaurant_listing_category'] ) ) ? $wp_query->query['restaurant_listing_category'] : '';

		$r['menu_order'] = false;

		if ( $orderby == 'order' )
			$r['menu_order'] = 'asc';
		elseif ( $orderby )
			$r['orderby'] = $orderby;

		$terms = get_terms( 'restaurant_listing_category', $r );

		if ( ! $terms )
			return;

		$output  = "<select name='restaurant_listing_category' id='dropdown_restaurant_listing_category'>";
		$output .= '<option value="" ' .  selected( isset( $_GET['restaurant_listing_category'] ) ? $_GET['restaurant_listing_category'] : '', '', false ) . '>'.__( 'Select a category', 'wp-review-restaurant' ).'</option>';
		$output .= $this->walk_category_dropdown_tree( $terms, 0, $r );
		$output .="</select>";

		echo $output;
	}

	/**
	 * Walk the Product Categories.
	 *
	 * @access public
	 * @return void
	 */
	private function walk_category_dropdown_tree() {
		$args = func_get_args();

		// the user's options are the third parameter
		if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
			$walker = new WP_Review_Restaurant_Category_Walker;
		else
			$walker = $args[2]['walker'];

		return call_user_func_array( array( $walker, 'walk' ), $args );
	}

	/**
	 * enter_title_here function.
	 *
	 * @access public
	 * @return void
	 */
	public function enter_title_here( $text, $post ) {
		if ( $post->post_type == 'restaurant_listing' )
			return __( 'Restaurant title', 'wp-review-restaurant' );
		return $text;
	}

	/**
	 * post_updated_messages function.
	 *
	 * @access public
	 * @param mixed $messages
	 * @return void
	 */
	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['restaurant_listing'] = array(
			0 => '',
			1 => sprintf( __( 'Restaurant listing updated. <a href="%s">View Restaurant</a>', 'wp-review-restaurant' ), esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', 'wp-review-restaurant' ),
			3 => __( 'Custom field deleted.', 'wp-review-restaurant' ),
			4 => __( 'Restaurant listing updated.', 'wp-review-restaurant' ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'Restaurant listing restored to revision from %s', 'wp-review-restaurant' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Restaurant listing published. <a href="%s">View Restaurant</a>', 'wp-review-restaurant' ), esc_url( get_permalink( $post_ID ) ) ),
			7 => __('Restaurant listing saved.', 'wp-review-restaurant' ),
			8 => sprintf( __( 'Restaurant listing submitted. <a target="_blank" href="%s">Preview Restaurant</a>', 'wp-review-restaurant' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Restaurant listing scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Restaurant</a>', 'wp-review-restaurant' ),
			  date_i18n( __( 'M j, Y @ G:i', 'wp-review-restaurant' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Restaurant listing draft updated. <a target="_blank" href="%s">Preview Restaurant</a>', 'wp-review-restaurant' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}

	/**
	 * columns function.
	 *
	 * @access public
	 * @param mixed $columns
	 * @return void
	 */
	public function columns( $columns ) {
		if ( ! is_array( $columns ) )
			$columns = array();

		unset( $columns['title'], $columns['date'], $columns['author'] );

		$columns["restaurant_listing_type"]     = __( "Type", 'wp-review-restaurant' );
		$columns["restaurant_position"]         = __( "Position", 'wp-review-restaurant' );
		$columns["restaurant_posted"]           = __( "Posted", 'wp-review-restaurant' );
		$columns["restaurant_expires"]          = __( "Expires", 'wp-review-restaurant' );
		if ( get_option( 'review_restaurant_enable_categories' ) )
		$columns["restaurant_listing_category"] = __( "Categories", 'wp-review-restaurant' );
		$columns['featured_restaurant']         = '<span class="tips" data-tip="' . __( "Featured?", 'wp-review-restaurant' ) . '">' . __( "Featured?", 'wp-review-restaurant' ) . '</span>';
		$columns['filled']               = '<span class="tips" data-tip="' . __( "Filled?", 'wp-review-restaurant' ) . '">' . __( "Filled?", 'wp-review-restaurant' ) . '</span>';
		$columns['restaurant_status']           = __( "Status", 'wp-review-restaurant' );
		$columns['restaurant_actions']          = __( "Actions", 'wp-review-restaurant' );

		return $columns;
	}

	/**
	 * custom_columns function.
	 *
	 * @access public
	 * @param mixed $column
	 * @return void
	 */
	public function custom_columns( $column ) {
		global $post, $review_restaurant;

		switch ( $column ) {
			case "restaurant_listing_type" :
				$type = get_the_restaurant_type( $post );
				if ( $type )
					echo '<span class="restaurant-type ' . $type->slug . '">' . $type->name . '</span>';
			break;
			case "restaurant_position" :
				echo '<div class="restaurant_position">';
				echo '<a href="' . admin_url('post.php?post=' . $post->ID . '&action=edit') . '" class="tips restaurant_title" data-tip="' . sprintf( __( 'Restaurant ID: %d', 'wp-review-restaurant' ), $post->ID ) . '">' . $post->post_title . '</a>';

				echo '<div class="location">';

				if ( get_the_restaurant_website() )
					the_restaurant_name( '<span class="tips" data-tip="' . esc_attr( get_the_restaurant_tagline() ) . '"><a href="' . get_the_restaurant_website() . '">', '</a></span> &ndash; ' );
				else
					the_restaurant_name( '<span class="tips" data-tip="' . esc_attr( get_the_restaurant_tagline() ) . '">', '</span> &ndash; ' );

				the_restaurant_location( $post );

				echo '</div>';

				the_restaurant_logo();
				echo '</div>';
			break;
			case "restaurant_listing_category" :
				if ( ! $terms = get_the_term_list( $post->ID, $column, '', ', ', '' ) ) echo '<span class="na">&ndash;</span>'; else echo $terms;
			break;
			case "filled" :
				if ( is_restaurant_filled( $post ) ) echo '&#10004;'; else echo '&ndash;';
			break;
			case "featured_restaurant" :
				if ( is_restaurant_featured( $post ) ) echo '&#10004;'; else echo '&ndash;';
			break;
			case "restaurant_posted" :
				echo '<strong>' . date_i18n( __( 'M j, Y', 'wp-review-restaurant' ), strtotime( $post->post_date ) ) . '</strong><span>';
				echo ( empty( $post->post_author ) ? __( 'by a guest', 'wp-review-restaurant' ) : sprintf( __( 'by %s', 'wp-review-restaurant' ), '<a href="' . get_edit_user_link( $post->post_author ) . '">' . get_the_author() . '</a>' ) ) . '</span>';
			break;
			case "restaurant_expires" :
				if ( $post->_restaurant_expires )
					echo '<strong>' . date_i18n( __( 'M j, Y', 'wp-review-restaurant' ), strtotime( $post->_restaurant_expires ) ) . '</strong>';
				else
					echo '&ndash;';
			break;
			case "restaurant_status" :
				echo get_the_restaurant_status( $post );
			break;
			case "restaurant_actions" :
				echo '<div class="actions">';
				$admin_actions           = array();
				if ( $post->post_status == 'pending' ) {
					$admin_actions['approve']   = array(
						'action'  => 'approve',
						'name'    => __( 'Approve', 'wp-review-restaurant' ),
						'url'     =>  wp_nonce_url( add_query_arg( 'approve_restaurant', $post->ID ), 'approve_restaurant' )
					);
				}
				if ( $post->post_status !== 'trash' ) {
					$admin_actions['view']   = array(
						'action'  => 'view',
						'name'    => __( 'View', 'wp-review-restaurant' ),
						'url'     => get_permalink( $post->ID )
					);
					$admin_actions['edit']   = array(
						'action'  => 'edit',
						'name'    => __( 'Edit', 'wp-review-restaurant' ),
						'url'     => get_edit_post_link( $post->ID )
					);
					$admin_actions['delete'] = array(
						'action'  => 'delete',
						'name'    => __( 'Delete', 'wp-review-restaurant' ),
						'url'     => get_delete_post_link( $post->ID )
					);
				}

				$admin_actions = apply_filters( 'review_restaurant_admin_actions', $admin_actions, $post );

				foreach ( $admin_actions as $action ) {
					printf( '<a class="button tips icon-%s" href="%s" data-tip="%s">%s</a>', sanitize_title( $action['name'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
				}

				echo '</div>';

			break;
		}
	}

    /**
	 * Adds post status to the "submitdiv" Meta Box and post type WP List Table screens. Based on https://gist.github.com/franz-josef-kaiser/2930190
	 *
	 * @return void
	 */
	public function extend_submitdiv_post_status() {
		global $wp_post_statuses, $post, $post_type;

		// Abort if we're on the wrong post type, but only if we got a restriction
		if ( 'restaurant_listing' !== $post_type ) {
			return;
		}

		// Get all non-builtin post status and add them as <option>
		$options = $display = '';
		foreach ( $wp_post_statuses as $status )
		{
			if ( ! $status->_builtin ) {
				// Match against the current posts status
				$selected = selected( $post->post_status, $status->name, false );

				// If we one of our custom post status is selected, remember it
				$selected AND $display = $status->label;

				// Build the options
				$options .= "<option{$selected} value='{$status->name}'>{$status->label}</option>";
			}
		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function($)
			{
				<?php
				// Add the selected post status label to the "Status: [Name] (Edit)"
				if ( ! empty( $display ) ) :
				?>
					$( '#post-status-display' ).html( '<?php echo $display; ?>' )
				<?php
				endif;

				// Add the options to the <select> element
				?>
				var select = $( '#post-status-select' ).find( 'select' );
				$( select ).append( "<?php echo $options; ?>" );
			} );
		</script>
		<?php
	}
}

new WP_Review_Restaurant_CPT();