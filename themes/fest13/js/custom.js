(function ($) {
	'use strict';

	function adjustColumns() {
		var $content = $('#content-column'),
			$sidebar = $('#sidebar-column');

		if ($(window).width() >= 990) {
			if ($content.outerHeight() > $sidebar.outerHeight()) {
				$sidebar.height($content.outerHeight() - parseInt($sidebar.css('paddingTop'), 10) - parseInt($sidebar.css('paddingBottom'), 10));
			} else if ($sidebar.outerHeight() > $content.outerHeight()) {
				$content.height($sidebar.outerHeight() - parseInt($content.css('paddingTop'), 10) - parseInt($content.css('paddingBottom'), 10));
			}
		}
	}

	$(document).ready(function () {
		window.setTimeout(function () {
			adjustColumns();
		}, 10);

		$(window).on('resize', function () {
			adjustColumns();
		});

		$( '.button.wc-backward' ).attr( 'href', 'https://thefestfl.com/store/' );
	});
})(jQuery);
