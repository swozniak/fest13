jQuery( document ).ready( function($) {

	var bundle_stock_status = [];

	$('body').on( 'quick-view-displayed', function() {

		$('.bundle_form').each( function() {

			$(this).wc_bundle_form();

		} );

	} );

	$.fn.wc_bundle_form = function() {

		// Listeners
		$('.bundled_product')

			.on( 'found_variation', function( event, variation ) {
				var variations 			= $(this).find( '.variations_form' );
				var bundle_id 			= variations.attr( 'data-bundle-id' );
				var product_id			= variations.attr( 'data-product_id' );
				var bundled_item_id 	= variations.attr( 'data-bundled-item-id' );

				var bundle_price_data 	= $( '.bundle_form_' + bundle_id ).data( 'bundle_price_data' );

				if ( bundle_price_data['per_product_pricing'] == true ) {
					// put variation price in price table
					bundle_price_data['prices'][bundled_item_id] = variation.price;
					bundle_price_data['regular_prices'][bundled_item_id] = variation.regular_price;
				}

				$( '.bundle_form_' + bundle_id + ' .bundle_wrap' ).find('input[name="bundle_variation_id['+ bundled_item_id +']"]').val( variation.variation_id ).change();

				for ( attribute in variation.attributes ) {
					$( '.bundle_form_' + bundle_id + ' .bundle_wrap' ).find('input[name="bundle_' + attribute + '['+ bundled_item_id +']"]').val( variations.find('.attribute-options select[name="' + attribute + '"]').val() );
				}

				attempt_show_bundle( bundle_id );

			} )

			.on( 'woocommerce_update_variation_values', function() {
				var variations 			= $(this).find('.variations_form');
				var bundle_id 			= variations.attr('data-bundle-id');
				var bundled_item_id 	= variations.attr('data-bundled-item-id');

				variations.find( '.bundled_item_wrap input[name="variation_id"]').each( function(){
					if ( $(this).val() == '' ) {
						$( '.bundle_form_' + bundle_id + ' .bundle_wrap' ).find('input[name="bundle_variation_id['+ bundled_item_id +']"]').val('');
						$( '.bundle_form_' + bundle_id + ' .bundle_wrap' ).slideUp('200');
					}
				});


			} );


		$('.bundled_product .cart')

			.on( 'woocommerce-product-addons-update', function() {

				var addon 		= $(this).closest( '.product-addon' );
				var item 		= $(this).closest( '.cart' );
				var bundle_id 	= item.attr('data-bundle-id');

				attempt_show_bundle( bundle_id );

			} )

			.on( 'woocommerce-nyp-updated-item', function() {

				var item 		= $(this);
				var bundle_id 	= item.attr('data-bundle-id');
				var item_id 	= item.attr('data-bundled-item-id');
				var nyp 		= item.find( '.nyp' );

				if ( nyp.is(":visible") ) {

					var bundle_price_data = $( '.bundle_form_' + bundle_id ).data( 'bundle_price_data' );

					bundle_price_data['prices'][item_id] = nyp.data( 'price' );

					attempt_show_bundle( bundle_id );
				}

			} );


		$( '.bundle_form' )

			.on( 'woocommerce-nyp-updated-item', function() {

				var item 		= $(this);
				var bundle_id 	= item.attr('data-bundle-id');
				var nyp 		= item.find( '.nyp' );

				if ( nyp.is(":visible") ) {

					var bundle_price_data = $( '.bundle_form_' + bundle_id ).data( 'bundle_price_data' );

					bundle_price_data['total'] = nyp.data( 'price' );

					attempt_show_bundle( bundle_id );
				}

			} );


		/**
		 * Initial states and loading
		 */

		// Add-ons support: move totals

		var addons_totals = $(this).find( '#product-addons-total' );

		$(this).find( '.bundle_price' ).after( addons_totals );

		var bundle_id = $(this).attr('data-bundle-id');

		if ( $(this).find('.bundle_wrap p.stock').length > 0 )
			bundle_stock_status[bundle_id] = $(this).find('.bundle_wrap p.stock').clone().wrap('<p>').parent().html();

		// Init addons - not needed anymore since filtered get_price returns the right result

		// Init variations JS and addons
		$(this).parent().find('.variations select').change();

		$(this).parent().find('input.nyp').change();

		if ( check_all_simple( bundle_id ) )
			attempt_show_bundle( bundle_id );

	}


	function attempt_show_bundle( bundle_id ) {

		var all_set = true;

		var addons_prices = [];

		// Save addons prices
		$( '.bundle_form_' + bundle_id ).parent().find( '.bundled_product .cart' ).each( function() {

			var item 		= $(this);
			var item_id 	= $(this).attr('data-bundled-item-id');

			addons_prices[ item_id ] = 0;

			item.find('.addon').each(function() {
				var addon_cost = 0;

				if ( $(this).is('.addon-custom-price') ) {
					addon_cost = $(this).val();
				} else if ( $(this).is('.addon-input_multiplier') ) {
					if( isNaN( $(this).val() ) || $(this).val() == "" ) { // Number inputs return blank when invalid
						$(this).val('');
						$(this).closest('p').find('.addon-alert').show();
					} else {
						if( $(this).val() != "" ){
							$(this).val( Math.ceil( $(this).val() ) );
						}
						$(this).closest('p').find('.addon-alert').hide();
					}
					addon_cost = $(this).data('price') * $(this).val();
				} else if ( $(this).is('.addon-checkbox, .addon-radio') ) {
					if ( $(this).is(':checked') )
						addon_cost = $(this).data('price');
				} else if ( $(this).is('.addon-select') ) {
					if ( $(this).val() )
						addon_cost = $(this).find('option:selected').data('price');
				} else {
					if ( $(this).val() )
						addon_cost = $(this).data('price');
				}


				if ( ! addon_cost )
					addon_cost = 0;

				addons_prices[ item_id ] = parseFloat( addons_prices[ item_id ] ) + parseFloat( addon_cost );

			} );

		} );

		$( '.bundle_form_' + bundle_id ).parent().find('.variations select' ).each( function() {
			if ( $(this).val().length == 0 ) {
				all_set = false;
			}
		} );

		if ( all_set ) {

			var bundle_price_data = $( '.bundle_form_' + bundle_id ).data( 'bundle_price_data' );
			var bundled_item_quantities = $( '.bundle_form_' + bundle_id ).data( 'bundled_item_quantities' );

			if ( ( bundle_price_data['per_product_pricing'] == false ) && ( bundle_price_data['total'] == -1 ) ) return;

			if ( bundle_price_data['per_product_pricing'] == true ) {
				bundle_price_data['total'] 			= 0;
				bundle_price_data['regular_total'] 	= 0;
				for ( prod_id in bundle_price_data['prices'] ) {
					bundle_price_data['total'] 			+= ( parseFloat( bundle_price_data['prices'][ prod_id ] ) + parseFloat( addons_prices[ prod_id ] ) ) * bundled_item_quantities[ prod_id ];
					bundle_price_data['regular_total'] 	+= ( parseFloat( bundle_price_data['regular_prices'][ prod_id ] ) + parseFloat( addons_prices[ prod_id ] ) ) * bundled_item_quantities[ prod_id ];
				}
			} else {
				bundle_price_data['total_backup'] 			= parseFloat( bundle_price_data['total'] );
				bundle_price_data['regular_total_backup']	= parseFloat( bundle_price_data['regular_total'] );
				for ( item_id in addons_prices ) {
					bundle_price_data['total'] 			+= parseFloat( addons_prices[ item_id ] ) * bundled_item_quantities[ item_id ];
					bundle_price_data['regular_total'] 	+= parseFloat( addons_prices[ item_id ] ) * bundled_item_quantities[ item_id ];
				}
			}

			$( '.bundle_form_' + bundle_id + ' #product-addons-total' ).data( 'price', bundle_price_data['total'] );
			$( '.bundle_form_' + bundle_id ).trigger( 'woocommerce-product-addons-update' );

			if ( bundle_price_data['total'] == 0 )
				$('.bundle_form_' + bundle_id + ' .bundle_price').html('<p class="price"><span class="total">' + bundle_price_data['total_description'] + '</span>'+ bundle_price_data['free'] +'</p>');
			else {

				var sales_price = number_format ( bundle_price_data['total'], bundle_price_data['woocommerce_price_num_decimals'], bundle_price_data['woocommerce_price_decimal_sep'], bundle_price_data['woocommerce_price_thousand_sep'] );

				var regular_price = number_format ( bundle_price_data['regular_total'], bundle_price_data['woocommerce_price_num_decimals'], bundle_price_data['woocommerce_price_decimal_sep'], bundle_price_data['woocommerce_price_thousand_sep'] );

				var remove = bundle_price_data['woocommerce_price_decimal_sep'];

				if ( bundle_price_data['woocommerce_price_trim_zeros'] == 'yes' && bundle_price_data['woocommerce_price_num_decimals'] > 0 ) {
					for (var i = 0; i < bundle_price_data['woocommerce_price_num_decimals']; i++) { remove = remove + '0'; }
					sales_price = sales_price.replace(remove, '');
					regular_price = regular_price.replace(remove, '');
				}

				var sales_price_format = '';
				var regular_price_format = '';

				if ( bundle_price_data['woocommerce_currency_pos'] == 'left' ) {
					sales_price_format = '<span class="amount">' + bundle_price_data['currency_symbol'] + sales_price + '</span>';
					regular_price_format = '<span class="amount">' + bundle_price_data['currency_symbol'] + regular_price + '</span>'; }
				else if ( bundle_price_data['woocommerce_currency_pos'] == 'right' ) {
					sales_price_format = '<span class="amount">' + sales_price + bundle_price_data['currency_symbol'] +  '</span>';
					regular_price_format = '<span class="amount">' + regular_price + bundle_price_data['currency_symbol'] +  '</span>'; }
				else if ( bundle_price_data['woocommerce_currency_pos'] == 'left_space' ) {
					sales_price_format = '<span class="amount">' + bundle_price_data['currency_symbol'] + '&nbsp;' + sales_price + '</span>';
					regular_price_format = '<span class="amount">' + bundle_price_data['currency_symbol'] + '&nbsp;' + regular_price + '</span>'; }
				else if ( bundle_price_data['woocommerce_currency_pos'] == 'right_space' ) {
					sales_price_format = '<span class="amount">' + sales_price + '&nbsp;' + bundle_price_data['currency_symbol'] +  '</span>';
					regular_price_format = '<span class="amount">' + regular_price + '&nbsp;' + bundle_price_data['currency_symbol'] +  '</span>'; }

				if ( bundle_price_data['regular_total'] > bundle_price_data['total'] ) {
					$('.bundle_form_' + bundle_id + ' .bundle_price').html('<p class="price"><span class="total">' + bundle_price_data['total_description'] + '</span><del>' + regular_price_format +'</del> <ins>'+ sales_price_format +'</ins></p>');
				} else {
					$('.bundle_form_' + bundle_id + ' .bundle_price').html('<p class="price"><span class="total">' + bundle_price_data['total_description'] + '</span>'+ sales_price_format +'</p>');
				}
			}

			// reset bundle stock status
			$( '.bundle_form_' + bundle_id + ' .bundle_wrap p.stock' ).replaceWith( bundle_stock_status[bundle_id] );

			// set bundle stock status as out of stock if any selected variation is out of stock
			$( '.bundle_form_' + bundle_id ).parent().find( '.bundled_product .cart' ).each( function() {

				var $item_stock_p = $(this).find( 'p.stock' );

				if ( $item_stock_p.hasClass( 'out-of-stock' ) ) {
					if ( $('.bundle_form_' + bundle_id + ' .bundle_wrap p.stock').length > 0 ) {
						$('.bundle_form_' + bundle_id + ' .bundle_wrap p.stock').replaceWith( $item_stock_p.clone().html( bundle_price_data[ 'partially_out_of_stock' ] ) );
					} else {
						$('.bundle_form_' + bundle_id + ' .bundle_wrap .bundle_price').after( $item_stock_p.clone().html( bundle_price_data[ 'partially_out_of_stock' ] ) );
					}
				}

				if ( $item_stock_p.hasClass( 'available-on-backorder' ) && ! $('.bundle_form_' + bundle_id + ' .bundle_wrap p.stock').hasClass( 'out-of-stock' ) ) {
					if ( $('.bundle_form_' + bundle_id + ' .bundle_wrap p.stock').length > 0 ) {
						$('.bundle_form_' + bundle_id + ' .bundle_wrap p.stock').replaceWith( $item_stock_p.clone().html( bundle_price_data[ 'partially_on_backorder' ] ) );
					} else {
						$('.bundle_form_' + bundle_id + ' .bundle_wrap .bundle_price').after( $item_stock_p.clone().html( bundle_price_data[ 'partially_on_backorder' ] ) );
					}
				}

			} );

			if ( $('.bundle_form_' + bundle_id + ' .bundle_wrap p.stock').hasClass( 'out-of-stock' ) )
				$( '.bundle_form_' + bundle_id + ' .bundle_button' ).hide();
			else
				$( '.bundle_form_' + bundle_id + ' .bundle_button' ).show();

			$( '.bundle_form_' + bundle_id + ' .bundle_wrap' ).slideDown('200').trigger('show_bundle');

			bundle_price_data['total'] 			= bundle_price_data['total_backup'];
			bundle_price_data['regular_total']	= bundle_price_data['regular_total_backup'];
		}
	}

	function check_all_simple( bundle_id ) {

		var bundle_price_data = $( '.bundle_form_' + bundle_id ).data( 'bundle_price_data' );

		if ( typeof bundle_price_data == 'undefined' ) { return false; }
		if ( bundle_price_data['prices'].length < 1 ) { return false; }
		if ( $( '.bundle_form_' + bundle_id + ' input[value="variable"]' ).length > 0 ) {
			return false;
		}
		return true;
	}


	/**
	 * Helper functions for variations
	 */

	function number_format( number, decimals, dec_point, thousands_sep ) {
	    var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
	    var d = dec_point == undefined ? "," : dec_point;
	    var t = thousands_sep == undefined ? "." : thousands_sep, s = n < 0 ? "-" : "";
	    var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;

	    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
	}


	$('.bundle_form').each( function() {

		$(this).wc_bundle_form();

	} );

} );
