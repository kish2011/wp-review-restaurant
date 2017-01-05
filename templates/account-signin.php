<?php if ( is_user_logged_in() ) : ?>

	<fieldset>
		<label><?php _e( 'Your account', 'wp-review-restaurant' ); ?></label>
		<div class="field account-sign-in">
			<?php
				$user = wp_get_current_user();
				printf( __( 'You are currently signed in as <strong>%s</strong>.', 'wp-review-restaurant' ), $user->user_login );
			?>

			<a class="button" href="<?php echo apply_filters( 'submit_restaurant_form_logout_url', wp_logout_url( get_permalink() ) ); ?>"><?php _e( 'Sign out', 'wp-review-restaurant' ); ?></a>
		</div>
	</fieldset>

<?php else :

	$account_required     = review_restaurant_user_requires_account();
	$registration_enabled = review_restaurant_enable_registration();
	?>
	<fieldset>
		<label><?php _e( 'Have an account?', 'wp-review-restaurant' ); ?></label>
		<div class="field account-sign-in">
			<a class="button" href="<?php echo apply_filters( 'submit_restaurant_form_login_url', wp_login_url( get_permalink() ) ); ?>"><?php _e( 'Sign in', 'wp-review-restaurant' ); ?></a>

			<?php if ( $registration_enabled ) : ?>

				<?php printf( __( 'If you don&rsquo;t have an account you can %screate one below by entering your email address. A password will be automatically emailed to you.', 'wp-review-restaurant' ), $account_required ? '' : __( 'optionally', 'wp-review-restaurant' ) . ' ' ); ?>

			<?php elseif ( $account_required ) : ?>

				<?php echo apply_filters( 'submit_restaurant_form_login_required_message',  __('You must sign in to create a new restaurant listing.', 'wp-review-restaurant' ) ); ?>

			<?php endif; ?>
		</div>
	</fieldset>
	<?php if ( $registration_enabled ) : ?>
		<fieldset>
			<label><?php _e( 'Your email', 'wp-review-restaurant' ); ?> <?php if ( ! $account_required ) echo '<small>' . __( '(optional)', 'wp-review-restaurant' ) . '</small>'; ?></label>
			<div class="field">
				<input type="email" class="input-text" name="create_account_email" id="account_email" placeholder="you@yourdomain.com" value="<?php if ( ! empty( $_POST['create_account_email'] ) ) echo sanitize_text_field( stripslashes( $_POST['create_account_email'] ) ); ?>" />
			</div>
		</fieldset>
	<?php endif; ?>

<?php endif; ?>