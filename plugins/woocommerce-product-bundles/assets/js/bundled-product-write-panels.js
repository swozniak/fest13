/*
// from write-panels.js
*/

jQuery( function($){

	// bundle type move stock msg up
	$('.bundle_stock_msg').insertBefore('._manage_stock_field');

	// bundle type specific options
	$('body').on('woocommerce-product-type-change', function( event, select_val, select ) {

		if ( select_val == 'bundle' ) {

			$('input#_downloadable').prop('checked', false);
			$('input#_virtual').removeAttr('checked');

			$('.show_if_simple').show();
			$('.show_if_external').hide();
			$('.show_if_bundle').show();

			$('input#_downloadable').closest('.show_if_simple').hide();
			$('input#_virtual').closest('.show_if_simple').hide();

			$('input#_manage_stock').change();
			$('input#_per_product_pricing_active').change();
			$('input#_per_product_shipping_active').change();

			$( '#_nyp' ).change();
		} else {

			$('.show_if_bundle').hide();
		}

	});

	$('select#product-type').change();

	// variation filtering options
	$('.filter_variations input').change(function(){
		if ($(this).is(':checked')) $(this).closest('div.item-data').find('div.bundle_variation_filters').show();
		else $(this).closest('div.item-data').find('div.bundle_variation_filters').hide();
	}).change();

	// selection defaults options
	$('.override_defaults input').change(function(){
		if ($(this).is(':checked')) $(this).closest('div.item-data').find('div.bundle_selection_defaults').show();
		else $(this).closest('div.item-data').find('div.bundle_selection_defaults').hide();
	}).change();

	// visibility
	$('.item_visibility select').change(function(){

		if ( $(this).val() == 'visible' ) {
			$(this).closest('div.item-data').find('div.override_title').show();
			$(this).closest('div.item-data').find('div.override_description').show();
			$(this).closest('div.item-data').find('div.images').show();
		} else {
			$(this).closest('div.item-data').find('div.override_title').hide();
			$(this).closest('div.item-data').find('div.override_description').hide();
			$(this).closest('div.item-data').find('div.images').hide();
		}

	}).change();

	// custom title options
	$('.override_title > p input').change(function(){
		if ($(this).is(':checked')) $(this).closest('div.override_title').find('div.custom_title').show();
		else $(this).closest('div.override_title').find('div.custom_title').hide();
	}).change();

	// custom description options
	$('.override_description > p input').change(function(){
		if ($(this).is(':checked')) $(this).closest('div.override_description').find('div.custom_description').show();
		else $(this).closest('div.override_description').find('div.custom_description').hide();
	}).change();

	// non-bundled shipping
	$( 'input#_per_product_shipping_active' ).change( function(){

		if ( $('select#product-type').val() == 'bundle' ) {

			if ( $('input#_per_product_shipping_active').is(':checked') ) {
				$('.show_if_virtual').show();
				$('.hide_if_virtual').hide();
				if ( $('.shipping_tab').hasClass('active') )
					$('ul.product_data_tabs li:visible').eq(0).find('a').click();
			} else {
				$('.show_if_virtual').hide();
				$('.hide_if_virtual').show();
			}
		}

	} ).change();

	// show options if pricing is static
	$( 'input#_per_product_pricing_active' ).change( function() {

		if ( $('select#product-type').val() == 'bundle' ) {

			if ( $(this).is(':checked') ) {

				$('#_regular_price').attr('disabled', true);
		        $('#_regular_price').val('');
		        $('#_sale_price').attr('disabled', true);
		        $('#_sale_price').val('');

				$('._tax_class_field').closest('.options_group').hide();
				$('.pricing').hide();

				$('#bundled_product_data .wc-bundled-item .item-data .discount input.bundle_discount').each( function() {
					$(this).attr('disabled', false);
				} );

			} else {

				$('#_regular_price').removeAttr('disabled');
		        $('#_sale_price').removeAttr('disabled');

				$('._tax_class_field').closest('.options_group').show();

				if ( ! $( '#_nyp' ).is( ':checked' ) )
					$('.pricing').show();

				$('#bundled_product_data .wc-bundled-item .item-data .discount input.bundle_discount').each( function() {
					$(this).attr('disabled', 'disabled');
				} );
			}
		}

	} ).change();

	// nyp support
	$( '#_nyp' ).change( function() {

		if ( $('select#product-type').val() == 'bundle' ) {

			if ( $( '#_nyp' ).is( ':checked' ) ) {
				$( 'input#_per_product_pricing_active' ).prop( 'checked', false );
				$( '.bundle_pricing' ).hide();
			} else {
				$( '.bundle_pricing' ).show();
			}

			$( 'input#_per_product_pricing_active' ).change();
		}

	} ).change();

	function bundle_row_indexes() {
		$('.wc-bundled-items .wc-bundled-item').each(function( index, el ){
			$('.bundled_item_position', el).val( parseInt( $(el).index('.wc-bundled-items .wc-bundled-item') ) );
		});
	};

	// Initial order
	var bundled_items = $('.wc-bundled-items').find('.wc-bundled-item').get();

	bundled_items.sort(function(a, b) {
	   var compA = parseInt($(a).attr('rel'));
	   var compB = parseInt($(b).attr('rel'));
	   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
	})
	$(bundled_items).each( function(idx, itm) { $('.wc-bundled-items').append(itm); } );

	// Item ordering
	$('.wc-bundled-items').sortable({
		items:'.wc-bundled-item',
		cursor:'move',
		axis:'y',
		handle: 'h3',
		scrollSensitivity:40,
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'wc-metabox-sortable-placeholder',
		start:function(event,ui){
			ui.item.css('background-color','#f6f6f6');
		},
		stop:function(event,ui){
			ui.item.removeAttr('style');
			bundle_row_indexes();
		}
	});

	$('#bundled_product_data .expand_all').click(function(){
		$(this).closest('.wc-metaboxes-wrapper').find('.wc-metabox > .item-data').show();
		return false;
	});

	$('#bundled_product_data .close_all').click(function(){
		$(this).closest('.wc-metaboxes-wrapper').find('.wc-metabox > .item-data').hide();
		return false;
	});

});
