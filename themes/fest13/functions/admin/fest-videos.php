<?php
/* Add FEST VIDEO ARCHIVE admin page */
add_action( "admin_menu", "fest_videos_menu" );

function fest_videos_menu() {
	add_menu_page( "Fest Video Archive - Admin", "Fest Video Archive", "manage_options", "fest-videos", "fest_videos_menu_options");
}


function fetch( $playlist ) {
	$searchResultCount = 0;
	$requestCount = 1;

	$playlistURL = implode( '/', array( 'http://gdata.youtube.com/feeds/api/playlists', $playlist['youtube_id'], '?v=2&autoplay=1&fs=1&alt=json&max-results=50' ) );
	$data = json_decode( file_get_contents( $playlistURL . '&start-index=1' ) );

	$searchResultCount = $data->feed->{'openSearch$totalResults'}->{'$t'};
	$requestCount = ceil( $searchResultCount / 50 );

	if ( $requestCount == '1' ) {
		populate( implode( '&', array( $playlistURL, 'start-index=1' ) ), $playlist );
	} else {
		for ($x = 1; $x < $requestCount; $x++) {
			$index = ( $x === 1 ) ? 1 : $x * 50;
			populate( implode( '&', array( $playlistURL, 'start-index=' . $index ) ), $playlist );
		}
	}
}

function populate( $url, $playlist ) {
	$data = json_decode( file_get_contents( $url ) );
	$transient = 'videos-' . $playlist['id'];

	$current = get_transient( $transient );
	set_transient( $transient, array_merge( (array)$current, (array)$data->feed->entry ) );
}


function fest_videos_menu_options() {
	if ( !current_user_can( "manage_options" ) )  {
		wp_die( __( "You do not have sufficient permissions to access this page." ) );
	}

	if ( $_SERVER["REQUEST_METHOD"] === "POST") {
		$playlists = json_decode('[{
			"id": "fest12",
			"youtube_id": "PLbEHdRS_CJgQ6xEj3jiGI4oxo7QsBa0zl",
			"title": "Fest 12"
		}, {
			"id": "fest11",
			"youtube_id": "PLbEHdRS_CJgSN_Un5FMvd_lOnMkszz8PM",
			"title": "Fest 11"
		}, {
			"id": "fest10",
			"youtube_id": "PLbEHdRS_CJgRj79pOudveryhCJgarmv8Z",
			"title": "Fest 10"
		}, {
			"id": "fest9",
			"youtube_id": "PLbEHdRS_CJgRkzTTKji8AlSNe1hghLMot",
			"title": "Fest 9"
		}, {
			"id": "fest8",
			"youtube_id": "PLbEHdRS_CJgSapTz3q2TFHFIRtviO9Opb",
			"title": "Fest 8"
		}, {
			"id": "fest7",
			"youtube_id": "PLbEHdRS_CJgS5vRZmgMILHInSRsQcH39a",
			"title": "Fest 7"
		}, {
			"id": "fest6",
			"youtube_id": "PLbEHdRS_CJgRktHfJxDUVV-Al5xgiHsKJ",
			"title": "Fest 6"
		}, {
			"id": "fest5",
			"youtube_id": "PLbEHdRS_CJgR3IvRrbTOX99BqWYYbifRs",
			"title": "Fest 5"
		}, {
			"id": "fest4",
			"youtube_id": "PLbEHdRS_CJgTuF2aph3-ih825MA09W7A0",
			"title": "Fest 4"
		}, {
			"id": "fest3",
			"youtube_id": "PLbEHdRS_CJgRKkyeAcZvAS2AGe_xFiIje",
			"title": "Fest 3"
		}, {
			"id": "fest2",
			"youtube_id": "PLbEHdRS_CJgQ9Y-PxV6sugrmvtlvYSbxg",
			"title": "Fest 2"
		}, {
			"id": "fest1",
			"youtube_id": "PLbEHdRS_CJgQ3LrDZH1Y4j_AFYC4RvODb",
			"title": "Fest 1"
		}]', true );

		foreach( $playlists as $playlist ) {
			$transient = 'videos-' . $playlist['id'];
			set_transient( $transient, array() );
			fetch($playlist);
		}
	} else {
	?>

	<form method="POST">
		<input type="submit" value="Fetch the latest" />
	</form>

<?php } }