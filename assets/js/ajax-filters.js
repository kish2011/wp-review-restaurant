jQuery( document ).ready( function ( $ ) {

	var xhr;

	$( '.restaurant_listings' ).on( 'update_results', function ( event, page, append ) {

		if ( xhr ) {
			xhr.abort();
		}

		var data               = '';
		var target             = $( this );
		var form               = target.find( '.restaurant_filters' );
		var showing            = target.find( '.showing_restaurants' );
		var results            = target.find( '.restaurant_listings' );
		var per_page           = target.data( 'per_page' );
		var orderby            = target.data( 'orderby' );
		var order              = target.data( 'order' );
		var featured           = target.data( 'featured' );

		if ( append ) {
			$( '.load_more_restaurants', target ).addClass( 'loading' );
		} else {
			$( results ).addClass( 'loading' );
			$( 'li.restaurant_listing, li.no_restaurant_listings_found', results ).css( 'visibility', 'hidden' );
		}

		if ( true == target.data( 'show_filters' ) ) {

			var filter_restaurant_type = [];
			var filter_restaurant_type_cuisine = [];
			var filter_restaurant_type_advanced = [];

			$( ':input[name="filter_restaurant_type[]"]:checked, :input[name="filter_restaurant_type[]"][type="hidden"]', form ).each( function () {
				filter_restaurant_type.push( $( this ).val() );
			} );
			
			$( ':input[name="filter_restaurant_type_cuisine[]"]:checked, :input[name="filter_restaurant_type_cuisine[]"][type="hidden"]', form ).each( function () {
				filter_restaurant_type_cuisine.push( $( this ).val() );
			} );
			
			$( ':input[name="filter_restaurant_type_advanced[]"]:checked, :input[name="filter_restaurant_type_advanced[]"][type="hidden"]', form ).each( function () {
				filter_restaurant_type_advanced.push( $( this ).val() );
			} );

			var categories = form.find( ':input[name^=search_categories], :input[name^=search_categories]' ).map( function () {
				return $( this ).val();
			} ).get();
			var keywords = '';
			var location = '';
			var $keywords = form.find( ':input[name=search_keywords]' );
			var $location = form.find( ':input[name=search_location]' );

			// Workaround placeholder scripts
			if ( $keywords.val() !== $keywords.attr( 'placeholder' ) ) {
				keywords = $keywords.val();
			}

			if ( $location.val() !== $location.attr( 'placeholder' ) ) {
				location = $location.val();
			}

			data = {
				action: 'review_restaurant_get_listings',
				search_keywords: keywords,
				search_location: location,
				search_categories: categories,
				filter_restaurant_type: filter_restaurant_type,
				filter_restaurant_type_cuisine: filter_restaurant_type_cuisine,
				filter_restaurant_type_advanced: filter_restaurant_type_advanced,
				per_page: per_page,
				orderby: orderby,
				order: order,
				page: page,
				featured: featured,
				form_data: form.serialize()
			};

		} else {

			var categories = target.data( 'categories' );
			var keywords   = target.data( 'keywords' );
			var location   = target.data( 'location' );

			if ( categories ) {
				categories = categories.split( ',' );
			}

			data = {
				action: 'review_restaurant_get_listings',
				search_categories: categories,
				search_keywords: keywords,
				search_location: location,
				per_page: per_page,
				orderby: orderby,
				order: order,
				page: page,
				featured: featured
			};

		}

		xhr = $.ajax( {
			type: 'POST',
			url: review_restaurant_ajax_filters.ajax_url,
			data: data,
			success: function ( response ) {
				if ( response ) {
					try {

						// Get the valid JSON only from the returned string
						if ( response.indexOf( "<!--WPRR-->" ) >= 0 ) {
							response = response.split( "<!--WPRR-->" )[ 1 ]; // Strip off before WPRR
						}

						if ( response.indexOf( "<!--WPRR_END-->" ) >= 0 ) {
							response = response.split( "<!--WPRR_END-->" )[ 0 ]; // Strip off anything after WPRR_END
						}

						var result = $.parseJSON( response );

						if ( result.showing ) {
							$( showing ).show().html( '' ).append( '<span>' + result.showing + '</span>' + result.showing_links );
						} else {
							$( showing ).hide();
						}

						if ( result.html ) {
							if ( append ) {
								$( results ).append( result.html );
							} else {
								$( results ).html( result.html );
							}
						}

						if ( ! result.found_restaurants || result.max_num_pages === page ) {
							$( '.load_more_restaurants', target ).hide();
						} else {
							$( '.load_more_restaurants', target ).show().data( 'page', page );
						}

						$( results ).removeClass( 'loading' );
						$( '.load_more_restaurants', target ).removeClass( 'loading' );
						$( 'li.restaurant_listing', results ).css( 'visibility', 'visible' );

					} catch ( err ) {
						//console.log( err );
					}
				}
			}
		} );
	} );

	$( '#search_keywords, #search_location, .restaurant_types input, #search_categories' ).change( function () {
		var target = $( this ).closest( 'div.restaurant_listings' );

		target.trigger( 'update_results', [ 1, false ] );
	} )

	.on( "keyup", function(e) {
	    if ( e.which === 13 ) {
	        $( this ).trigger( 'change' );
	    }
	} );

	$( '.restaurant_filters' ).each(function() {
		$( this ).find( '#search_keywords, #search_location, .restaurant_types input, #search_categories' ).eq(0).change();
	});

	$( '.restaurant_filters' ).on( 'click', '.reset', function () {
		var target = $( this ).closest( 'div.restaurant_listings' );
		var form = $( this ).closest( 'form' );

		form.find( ':input[name=search_keywords]' ).val( '' );
		form.find( ':input[name=search_location]' ).val( '' );
		form.find( ':input[name^=search_categories]' ).val( 0 );
		$( ':input[name="filter_restaurant_type[]"]', form ).attr( 'checked', 'checked' );
		$( ':input[name="filter_restaurant_type_cuisine[]"]', form ).attr( 'checked', 'checked' );
		$( ':input[name="filter_restaurant_type_advanced[]"]', form ).attr( 'checked', 'checked' );

		target.trigger( 'reset' );
		target.trigger( 'update_results', [ 1, false ] );

		return false;
	} );

	$( '.load_more_restaurants' ).click( function () {
		var target = $( this ).closest( 'div.restaurant_listings' );
		var page = $( this ).data( 'page' );

		if ( !page ) {
			page = 1;
		} else {
			page = parseInt( page );
		}

		$( this ).data( 'page', ( page + 1 ) );

		target.trigger( 'update_results', [ page + 1, true ] );

		return false;
	} );

} );