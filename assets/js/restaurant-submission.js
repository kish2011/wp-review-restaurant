jQuery(document).ready(function($) {
	jQuery( '.review-restaurant-remove-uploaded-file' ).click(function() {
		jQuery( '.review-restaurant-uploaded-file' ).remove();
		return false;
	});
});