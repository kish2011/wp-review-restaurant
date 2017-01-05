jQuery(document).ready(function($) {

	$('.restaurant-dashboard-action-delete').click(function() {
		var answer = confirm( review_restaurant_review_dashboard.i18n_confirm_delete );

		if (answer)
			return true;

		return false;
	});

});