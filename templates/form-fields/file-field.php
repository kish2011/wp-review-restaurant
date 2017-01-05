<?php if ( ! empty( $field['value'] ) ) : ?>
	<div class="review-restaurant-uploaded-file">
		<?php if ( in_array( substr( strrchr( $field['value'], '.' ), 1 ), array( 'jpg', 'gif', 'png', 'jpeg', 'jpe' ) ) ) : ?>
			<img src="<?php echo $field['value']; ?>" /> 
		<?php endif; ?>
		<?php echo '<code>' . basename( $field['value'] ) . ' <a class="review-restaurant-remove-uploaded-file" href="#">[' . __( 'remove', 'wp-review-restaurant' ) . ']</a>' . '</code> ' . __( 'or', 'wp-review-restaurant' ) . '&hellip;'; ?>
		<input type="hidden" class="input-text" name="current_<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" value="<?php echo isset( $field['value'] ) ? esc_attr( $field['value'] ) : ''; ?>" />
	</div>
<?php endif; ?>

<input type="file" class="input-text" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" />
<small class="description">
	<?php if ( ! empty( $field['description'] ) ) : ?>
		<?php echo $field['description']; ?>
	<?php else : ?>
		<?php printf( __( 'Max. file size: %s.', 'wp-review-restaurant' ), size_format( wp_max_upload_size() ) ); ?>
	<?php endif; ?>
</small>