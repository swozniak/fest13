/* global jQuery */ 

(function ($) {
	'use strict';

	function filter_prefest() {
		var $bands = $('.band');

		if ($('#prefest-checkbox').hasClass('active')) {
			$bands.hide();
			$bands.filter(function () {
				return $(this).data('prefest');
			}).show();
		} else {
			$bands.show();
		}
	}
	$(document).ready(function () {

		filter_prefest();

		// UI
		if (window.location.pathname === '/bands/') {
			var $menuItem = $('.menu-item').find('a:contains("Bands")');
			$menuItem.css('color', '#555');
			$menuItem.parent().addClass('active');
			$('.bands-image').unveil(500);

			// Filter binding
			$('#prefest-checkbox').click(function (e) {
				$(e.currentTarget).toggleClass('active');
				$('#band-search').val('');
				filter_prefest();
			});
			$('#band-search').on('keyup', function (e) {
				var searchTerm = $(e.currentTarget).val().toLowerCase();
				$('.band').hide();
				$('.band').filter(function () {
					if ($('#prefest-checkbox').hasClass('active')) {
						if (!$(this).data('prefest')) {
							return;
						}
					}
					return $(this).data('name').toLowerCase().indexOf(searchTerm) > -1;
				}).show();
				$('.bands-image').trigger("unveil");
			});
		}
	});
})(jQuery);