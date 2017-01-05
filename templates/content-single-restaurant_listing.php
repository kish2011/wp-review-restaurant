<?php global $review_restaurant; ?>

<div class="single_restaurant_listing" itemscope itemtype="http://schema.org/RestaurantPosting">
	<meta itemprop="title" content="<?php echo esc_attr( $post->post_title ); ?>" />

	<?php if ( $post->post_status == 'expired' ) : ?>

		<div class="review-restaurant-info"><?php _e( 'This restaurant listing has expired', 'wp-review-restaurant' ); ?></div>

	<?php else : ?>

		<?php do_action( 'single_restaurant_listing_start' ); ?>

		<ul class="meta">
			<?php do_action( 'single_restaurant_listing_meta_start' ); ?>

			<li class="restaurant-type <?php echo get_the_restaurant_type() ? sanitize_title( get_the_restaurant_type()->slug ) : ''; ?>" itemprop="employmentType"><?php the_restaurant_type(); ?></li>

			<li class="location" itemprop="restaurantLocation"><?php the_restaurant_location(); ?></li>

			<!-- <li class="date-posted" itemprop="datePosted"><date><?php // printf( __( 'Posted %s ago', 'wp-review-restaurant' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date></li> -->

			<?php if ( is_restaurant_filled() ) : ?>
				<li class="position-filled"><?php _e( 'This position has been filled', 'wp-review-restaurant' ); ?></li>
			<?php endif; ?>

			<?php do_action( 'single_restaurant_listing_meta_end' ); ?>
		</ul>

		<?php do_action( 'single_restaurant_listing_meta_after' ); ?>

		<div class="restaurant" itemscope itemtype="http://data-vocabulary.org/Organization">
			<?php the_restaurant_logo(); ?>

			<p class="name">
				<a class="website" href="<?php echo get_the_restaurant_website(); ?>" itemprop="url"><?php _e( 'Website', 'wp-review-restaurant' ); ?></a>
				<?php the_restaurant_twitter(); ?>
				<?php the_restaurant_name( '<strong itemprop="name">', '</strong>' ); ?>
			</p>
			<?php the_restaurant_tagline( '<p class="tagline">', '</p>' ); ?>
		</div>

		<div class="restaurant_description" itemprop="description">
			<?php echo apply_filters( 'the_restaurant_description', get_the_content() ); ?>
		</div>

		<?php if ( ! is_restaurant_filled() && $post->post_status !== 'preview' ) get_review_restaurant_template( 'restaurant-application.php' ); ?>

		<?php do_action( 'single_restaurant_listing_end' ); ?>

	<?php endif; ?>
</div>