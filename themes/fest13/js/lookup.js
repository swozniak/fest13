(function ($) {
	$('#lookup-query-form').submit(function( event ) {
		var lookupQuery = $( '#lookup-query' ).val();
		var data = {
			action: 'lookup_orders',
			lookup_query: lookupQuery
		};

		jQuery.post( ajaxurl, data, function ( response_ajax ) {
			box.html( jQuery.parseJSON( response_ajax ) );
		});

		event.preventDefault();
	});
})(jQuery);
