<?php
/**
 * Restaurant Submission Form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $review_restaurant;
?>
<form action="<?php echo $action; ?>" method="post" id="submit-restaurant-form" class="review-restaurant-form" enctype="multipart/form-data">

	<?php if ( apply_filters( 'submit_restaurant_form_show_signin', true ) ) : ?>

		<?php get_review_restaurant_template( 'account-signin.php' ); ?>

	<?php endif; ?>

	<?php if ( review_restaurant_user_can_post_restaurant() ) : ?>

		<!-- Restaurant Information Fields -->
		<?php do_action( 'submit_restaurant_form_restaurant_fields_start' ); ?>

		<?php foreach ( $restaurant_fields as $key => $field ) : ?>
			<fieldset class="fieldset-<?php esc_attr_e( $key ); ?>">
				<label for="<?php esc_attr_e( $key ); ?>"><?php echo $field['label'] . apply_filters( 'submit_restaurant_form_required_label', $field['required'] ? '' : ' <small>' . __( '(optional)', 'wp-review-restaurant' ) . '</small>', $field ); ?></label>
				<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
					<?php get_review_restaurant_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) ); ?>
				</div>
			</fieldset>
		<?php endforeach; ?>

		<?php do_action( 'submit_restaurant_form_restaurant_fields_end' ); ?>

		<!-- Restaurant Information Fields -->
		<?php if ( $restaurant_fields ) : ?>
			<h2><?php _e( 'Restaurant details', 'wp-review-restaurant' ); ?></h2>

			<?php do_action( 'submit_restaurant_form_restaurant_fields_start' ); ?>

			<?php foreach ( $restaurant_fields as $key => $field ) : ?>
				<fieldset class="fieldset-<?php esc_attr_e( $key ); ?>">
					<label for="<?php esc_attr_e( $key ); ?>"><?php echo $field['label'] . apply_filters( 'submit_restaurant_form_required_label', $field['required'] ? '' : ' <small>' . __( '(optional)', 'wp-review-restaurant' ) . '</small>', $field ); ?></label>
					<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
						<?php get_review_restaurant_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) ); ?>
					</div>
				</fieldset>
			<?php endforeach; ?>

			<?php do_action( 'submit_restaurant_form_restaurant_fields_end' ); ?>
		<?php endif; ?>

		<p>
			<input type="hidden" name="review_restaurant_form" value="<?php echo $form; ?>" />
			<input type="hidden" name="restaurant_id" value="<?php echo esc_attr( $restaurant_id ); ?>" />
			<input type="hidden" name="step" value="0" />
			<input type="submit" name="submit_restaurant" class="button" value="<?php esc_attr_e( $submit_button_text ); ?>" />
		</p>

	<?php else : ?>

		<?php do_action( 'submit_restaurant_form_disabled' ); ?>

	<?php endif; ?>
</form>