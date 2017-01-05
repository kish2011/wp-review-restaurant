<?php global $review_restaurant; ?>

<a href="<?php the_permalink(); ?>">
	<div class="restaurant-type <?php echo get_the_restaurant_type() ? sanitize_title( get_the_restaurant_type()->slug ) : ''; ?>"><?php the_restaurant_type(); ?></div>

	<?php if ( $logo = get_the_restaurant_logo() ) : ?>
		<img src="<?php echo $logo; ?>" alt="<?php the_restaurant_name(); ?>" title="<?php the_restaurant_name(); ?> - <?php the_restaurant_tagline(); ?>" />
	<?php endif; ?>

	<div class="restaurant_summary_content">

		<h1><?php the_title(); ?></h1>

		<p class="meta"><?php the_restaurant_location( false ); ?> <!-- &mdash; <date><?php // printf( __( 'Posted %s ago', 'wp-review-restaurant' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date> --> </p>

	</div>
</a>