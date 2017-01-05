<li <?php restaurant_listing_class(); ?>>
	<a href="<?php the_restaurant_permalink(); ?>">
		<div class="position">
			<h3><?php the_title(); ?></h3>
		</div>
		<ul class="meta">
			<li class="location"><?php the_restaurant_location( false ); ?></li>
			<li class="restaurant"><?php the_restaurant_name(); ?></li>
			<li class="restaurant-type <?php echo get_the_restaurant_type() ? sanitize_title( get_the_restaurant_type()->slug ) : ''; ?>"><?php the_restaurant_type(); ?></li>
		</ul>
	</a>
</li>