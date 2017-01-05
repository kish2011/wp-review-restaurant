<div id="review-restaurant-restaurant-dashboard">
	<p><?php _e( 'Your restaurant listings are shown in the table below. Expired listings will be automatically removed after 30 days.', 'wp-review-restaurant' ); ?></p>
	<table class="review-restaurant-restaurants">
		<thead>
			<tr>
				<?php foreach ( $restaurant_dashboard_columns as $key => $column ) : ?>
					<th class="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! $restaurants ) : ?>
				<tr>
					<td colspan="6"><?php _e( 'You do not have any active restaurant listings.', 'wp-review-restaurant' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $restaurants as $restaurant ) : ?>
					<tr>
						<?php foreach ( $restaurant_dashboard_columns as $key => $column ) : ?>
							<td class="<?php echo esc_attr( $key ); ?>">
								<?php if ('restaurant_title' === $key ) : ?>
									<?php if ( $restaurant->post_status == 'publish' ) : ?>
										<a href="<?php echo get_permalink( $restaurant->ID ); ?>"><?php echo $restaurant->post_title; ?></a>
									<?php else : ?>
										<?php echo $restaurant->post_title; ?> <small>(<?php the_restaurant_status( $restaurant ); ?>)</small>
									<?php endif; ?>
									<ul class="restaurant-dashboard-actions">
										<?php
											$actions = array();

											switch ( $restaurant->post_status ) {
												case 'publish' :
													$actions['edit'] = array( 'label' => __( 'Edit', 'wp-review-restaurant' ), 'nonce' => false );

													if ( is_restaurant_filled( $restaurant ) ) {
														$actions['mark_not_filled'] = array( 'label' => __( 'Mark not filled', 'wp-review-restaurant' ), 'nonce' => true );
													} else {
														$actions['mark_filled'] = array( 'label' => __( 'Mark filled', 'wp-review-restaurant' ), 'nonce' => true );
													}
													break;
											}

											$actions['delete'] = array( 'label' => __( 'Delete', 'wp-review-restaurant' ), 'nonce' => true );
											$actions           = apply_filters( 'review_restaurant_my_restaurant_actions', $actions, $restaurant );

											foreach ( $actions as $action => $value ) {
												$action_url = add_query_arg( array( 'action' => $action, 'restaurant_id' => $restaurant->ID ) );
												if ( $value['nonce'] ) {
													$action_url = wp_nonce_url( $action_url, 'review_restaurant_my_restaurant_actions' );
												}
												echo '<li><a href="' . $action_url . '" class="restaurant-dashboard-action-' . $action . '">' . $value['label'] . '</a></li>';
											}
										?>
									</ul>
								<?php elseif ('date' === $key ) : ?>
									<?php echo date_i18n( get_option( 'date_format' ), strtotime( $restaurant->post_date ) ); ?>
								<?php elseif ('expires' === $key ) : ?>
									<?php echo $restaurant->_restaurant_expires ? date_i18n( get_option( 'date_format' ), strtotime( $restaurant->_restaurant_expires ) ) : '&ndash;'; ?>
								<?php elseif ('filled' === $key ) : ?>
									<?php echo is_restaurant_filled( $restaurant ) ? '&#10004;' : '&ndash;'; ?>
								<?php else : ?>
									<?php do_action( 'review_restaurant_review_dashboard_column_' . $key, $restaurant ); ?>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php get_review_restaurant_template( 'pagination.php', array( 'max_num_pages' => $max_num_pages ) ); ?>
</div>