(function ($) {
	var $content = $('.video-content'),
		$sidebar = $('.video-sidebar');

	if (!Array.prototype.indexOf) {
		Array.prototype.indexOf = function (elt /*, from*/ ) {
			var len = this.length >>> 0;
			var from = Number(arguments[1]) || 0;
			from = (from < 0) ? Math.ceil(from) : Math.floor(from);
			if (from < 0) from += len;

			for (; from < len; from++) {
				if (from in this && this[from] === elt) return from;
			}
			return -1;
		};
	}

	$(document).ready(function () {
		$('.video-thumb').live('click', function () {
			var videoInfo = $(this),
				$videoAuthor = $('.video-author');;

			$('#ytplayer').attr('src', videoInfo.data('url'));

			$('.video-title').text(videoInfo.data('title'));
			$videoAuthor.text(['@', videoInfo.data('author')].join(''));
			$videoAuthor.attr('href', ['http://youtube.com/user/', videoInfo.data('author')].join(''))

			$('html, body').animate({
				scrollTop: parseInt($('.content-container').offset().top)
			}, 100);
		});

		$('#video-search').on('keyup', function (e) {
			var searchTerm = $(e.currentTarget).val().toLowerCase();
			$('.video-thumb').hide();
			$('.video-thumb').filter(function () {
				return $(this).data('title').toLowerCase().indexOf(searchTerm) > -1;
			}).show();
			$('.playlist-container').scrollTop(0);
		});
	});

})(jQuery);