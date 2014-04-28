(function ($) {
	var $content = $('.video-content'),
		$sidebar = $('.video-sidebar');

	var playlists = [{
		id: 'fest12',
		youtube_id: 'PLbEHdRS_CJgQ6xEj3jiGI4oxo7QsBa0zl',
		title: 'Fest 12'
	}, {
		id: 'fest11',
		youtube_id: 'PLbEHdRS_CJgSN_Un5FMvd_lOnMkszz8PM',
		title: 'Fest 11'
	}, {
		id: 'fest10',
		youtube_id: 'PLbEHdRS_CJgRj79pOudveryhCJgarmv8Z',
		title: 'Fest 10'
	}, {
		id: 'fest9',
		youtube_id: 'PLbEHdRS_CJgRkzTTKji8AlSNe1hghLMot',
		title: 'Fest 9'
	}, {
		id: 'fest8',
		youtube_id: 'PLbEHdRS_CJgSapTz3q2TFHFIRtviO9Opb',
		title: 'Fest 8'
	}, {
		id: 'fest7',
		youtube_id: 'PLbEHdRS_CJgS5vRZmgMILHInSRsQcH39a',
		title: 'Fest 7'
	}, {
		id: 'fest6',
		youtube_id: 'PLbEHdRS_CJgRktHfJxDUVV-Al5xgiHsKJ',
		title: 'Fest 6'
	}, {
		id: 'fest5',
		youtube_id: 'PLbEHdRS_CJgR3IvRrbTOX99BqWYYbifRs',
		title: 'Fest 5'
	}, {
		id: 'fest4',
		youtube_id: 'PLbEHdRS_CJgTuF2aph3-ih825MA09W7A0',
		title: 'Fest 4'
	}, {
		id: 'fest3',
		youtube_id: 'PLbEHdRS_CJgRKkyeAcZvAS2AGe_xFiIje',
		title: 'Fest 3'
	}, {
		id: 'fest2',
		youtube_id: 'PLbEHdRS_CJgQ9Y-PxV6sugrmvtlvYSbxg',
		title: 'Fest 2'
	}, {
		id: 'fest1',
		youtube_id: 'PLbEHdRS_CJgQ3LrDZH1Y4j_AFYC4RvODb',
		title: 'Fest 1'
	}, ];

	function fetch(playlist) {
		var playListURL = ['//gdata.youtube.com/feeds/api/playlists', playlist.youtube_id, '?v=2&autoplay=1&fs=1&alt=json&callback=?'].join('/');

		$.getJSON(playListURL + '&max-results=50&start-index=1', function (data) {
			/* 
			openSearch$itemsPerPage.$t = 25;
			openSearch$startIndex.$t = 1;
			openSearch$totalResults.$t = 165;
			*/
			populate(data.feed, playlist);
		});
	}

	function populate(feed, playlist) {
		var list_data = '',
			videoURL = '//www.youtube.com/embed/';

		$.each(feed.entry, function (i, item) {
			var videoTitle = item.title.$t,
				author = item.media$group.media$credit[0].$t,
				href = item.link[0].href,
				feedURL = item.link[1].href,
				fragments = feedURL.split('/'),
				videoID = fragments[fragments.length - 2],
				url = videoURL + videoID,
				thumb = "//img.youtube.com/vi/" + videoID + "/default.jpg";

			list_data += '<li class="video-thumb" data-href="' + href + '" data-title="' + videoTitle + '" data-author="' + author + '" data-url="' + url + '"><h5>' + videoTitle + '</h5><img alt="' + videoTitle + '" src="' + thumb + '"></li>';
		});
		$(list_data).appendTo(['.', playlist.id].join(''));
	}

	$(document).ready(function () {
		$.each(playlists, function (i, playlist) {
			fetch(playlist);
		});

		$('.video-thumb').live('click', function () {
			var videoInfo = $(this),
				$videoAuthor = $('.video-author');;

			$('#ytplayer').attr('src', videoInfo.data('url'));

			$('.video-title').text(videoInfo.data('title'));
			$videoAuthor.text(['@', videoInfo.data('author')].join(''));
			$videoAuthor.attr('href', ['//youtube.com/user/', videoInfo.data('author')].join(''))

			$('html, body').animate({
				scrollTop: parseInt($('.content-container').offset().top)
			}, 100);
		});

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