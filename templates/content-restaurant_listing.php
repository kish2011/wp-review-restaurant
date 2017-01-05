<li <?php restaurant_listing_class(); ?>>
	<a href="<?php the_restaurant_permalink(); ?>">
		<?php the_restaurant_logo(); ?>
		<div class="position">
			<h3><?php the_title(); ?></h3>
			<div class="restaurant">
				<?php the_restaurant_name( '<strong>', '</strong> ' ); ?>
				<?php the_restaurant_tagline( '<span class="tagline">', '</span>' ); ?>
			</div>
		</div>
		<div class="location">
			<?php the_restaurant_location( false ); ?>
		</div>
		<ul class="meta">
			<li class="restaurant-type <?php echo get_the_restaurant_type() ? sanitize_title( get_the_restaurant_type()->slug ) : ''; ?>"><?php the_restaurant_type(); ?></li>
			<li class="reviews"><p><?php  comments_number( 'no reviews', 'one review', '% reviews' ); ?></p></li>
		</ul>
	</a>
</li>