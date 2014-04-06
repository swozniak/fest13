(function ($) {
	'use strict';

	function adjustColumns() {
		var $content = $('#content-column'),
			$sidebar = $('#sidebar-column');

		if ($(window).width() >= 990) {
			if ($content.height() > $sidebar.height()) {
				$sidebar.height($content.height() + parseInt($content.css('paddingTop'), 10) + parseInt($content.css('paddingBottom'), 10));
			} else if ($sidebar.height() > $content.height()) {
				$content.height($sidebar.height() - parseInt($content.css('paddingTop'), 10) - parseInt($content.css('paddingBottom'), 10));
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
	});
})(jQuery);