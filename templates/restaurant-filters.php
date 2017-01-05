<?php wp_enqueue_script( 'wp-review-restaurant-ajax-filters' ); ?>
<form class="restaurant_filters">

	<div class="search_restaurants">
		<?php do_action( 'review_restaurant_review_filters_search_restaurants_start', $atts ); ?>

		<div class="search_keywords">
			<label for="search_keywords"><?php _e( 'Keywords', 'wp-review-restaurant' ); ?></label>
			<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php _e( 'All Restaurants', 'wp-review-restaurant' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" />
		</div>

		<div class="search_location">
			<label for="search_location"><?php _e( 'Location', 'wp-review-restaurant' ); ?></label>
			<input type="text" name="search_location" id="search_location" placeholder="<?php _e( 'Any Location', 'wp-review-restaurant' ); ?>" value="<?php echo esc_attr( $location ); ?>" />
		</div>

		<?php if ( $categories ) : ?>
			<?php foreach ( $categories as $category ) : ?>
				<input type="hidden" name="search_categories[]" value="<?php echo sanitize_title( $category ); ?>" />
			<?php endforeach; ?>
		<?php elseif ( $show_categories && ! is_tax( 'restaurant_listing_category' ) && get_terms( 'restaurant_listing_category' ) ) : ?>
			<div class="search_categories">
				<label for="search_categories"><?php _e( 'Category', 'wp-review-restaurant' ); ?></label>
				<?php wp_dropdown_categories( array( 'taxonomy' => 'restaurant_listing_category', 'hierarchical' => 1, 'show_option_all' => __( 'All Restaurant Categories', 'wp-review-restaurant' ), 'name' => 'search_categories', 'orderby' => 'name', 'selected' => $selected_category ) ); ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'review_restaurant_review_filters_search_restaurants_end', $atts ); ?>
	</div>

	<?php if ( ! is_tax( 'restaurant_listing_type' ) && empty( $restaurant_types ) ) : ?>
		<ul class="restaurant_types">
			<?php foreach ( get_restaurant_listing_types() as $type ) : ?>
				<li><label for="restaurant_type_<?php echo $type->slug; ?>" class="<?php echo sanitize_title( $type->name ); ?>"><input type="checkbox" name="filter_restaurant_type[]" value="<?php echo $type->slug; ?>" <?php checked( in_array( $type->slug, $selected_restaurant_types ), true ); ?> id="restaurant_type_<?php echo $type->slug; ?>" /> <?php echo $type->name; ?></label></li>
			<?php endforeach; ?>
		</ul>
	<?php elseif ( $restaurant_types ) : ?>
		<?php foreach ( $restaurant_types as $restaurant_type ) : ?>
			<input type="hidden" name="filter_restaurant_type[]" value="<?php echo sanitize_title( $restaurant_type ); ?>" />
		<?php endforeach; ?>
	<?php endif; ?>
	
	<?php if ( ! is_tax( 'restaurant_listing_type_cuisine' ) && empty( $restaurant_types_cuisine ) ) : ?>
		<ul class="restaurant_types cuisine">
			<?php foreach ( get_restaurant_listing_types_cuisine() as $type ) : ?>
				<li><label for="restaurant_type_cuisine<?php echo $type->slug; ?>" class="<?php echo sanitize_title( $type->name ); ?>"><input type="checkbox" name="filter_restaurant_type_cuisine[]" value="<?php echo $type->slug; ?>" <?php checked( in_array( $type->slug, $restaurant_types_cuisine ), true ); ?> id="restaurant_type_cuisine<?php echo $type->slug; ?>" /> <?php echo $type->name; ?></label></li>
			<?php  endforeach; ?>
		</ul>
	<?php elseif ( $restaurant_types_cuisine ) : ?>
		<?php foreach ( $restaurant_types_cuisine as $restaurant_type_cuisine ) : ?>
			<input type="hidden" name="filter_restaurant_type_cuisine[]" value="<?php echo sanitize_title( $restaurant_type_cuisine ); ?>" />
		<?php  endforeach; ?>
	<?php endif; ?>
	
	<?php if ( ! is_tax( 'restaurant_listing_type_advanced' ) && empty( $restaurant_listing_types_advanced ) ) : ?>
		<ul class="restaurant_types advanced">
			<?php foreach ( get_restaurant_listing_types_advanced() as $type ) : ?>
				<li><label for="restaurant_type_advanced<?php echo $type->slug; ?>" class="<?php echo sanitize_title( $type->name ); ?>"><input type="checkbox" name="filter_restaurant_type_advanced[]" value="<?php echo $type->slug; ?>" <?php checked( in_array( $type->slug, $restaurant_types_advanced ), true ); ?> id="restaurant_type_advanced<?php echo $type->slug; ?>" /> <?php echo $type->name; ?></label></li>
			<?php  endforeach; ?>
		</ul>
	<?php elseif ( $restaurant_listing_types_advanced ) : ?>
		<?php foreach ( $restaurant_types_advanced as $restaurant_type_advanced ) : ?>
			<input type="hidden" name="filter_restaurant_type_advanced[]" value="<?php echo sanitize_title( $restaurant_type_advanced ); ?>" />
		<?php  endforeach; ?>
	<?php endif; ?>

	<div class="showing_restaurants"></div>
</form>