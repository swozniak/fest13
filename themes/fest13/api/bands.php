<?php
/**
 * Template Name: API - Bands
 *
 */

header('Content-Type: application/json');

if ( false === ( get_transient( 'fest13_api_bands' ) ) ) {
	global $wpdb;

	$bands = Array();
	$bands_query = $wpdb->get_results( "SELECT ID, post_title, post_name, post_content from $wpdb->posts WHERE post_status='publish' AND post_type='bands' ORDER BY post_name ASC", ARRAY_A );

	foreach ( $bands_query as $band_result ) {
		$band_ID = $band_result['ID'];
		$band_custom = get_post_custom( $band_ID );

		$band_urls = Array();

		$band = Array( 
			'id' => $band_ID,
			'url' => 'https://thefestfl.com/bands/' . $band_result['post_name'] . '/', 
			'name' => $band_result['post_title'],
			'bio' => $band_result['post_content'],
			'bio_text' => wp_strip_all_tags( $band_result['post_content'] ),
			'hometown' => $band_custom['wpcf-hometown'][0],
			'photo_url' => $band_custom['wpcf-photo'][0],
			'mp3_url' => $band_custom['wpcf-mp3'][0],
			'video_url' => $band_custom['wpcf-video-url'][0]
		);

		for ( $x = 0; $x < count( $band_custom['wpcf-website-title'] ); $x++ ) {
			array_push( $band_urls, array( 
				'title' => $band_custom['wpcf-website-title'][$x],
				'url' => $band_custom['wpcf-website-url'][$x] 
				)
			);
		}
		$band['links'] = $band_urls;

		$bands[$band_ID] = $band;
	}
	set_transient( 'fest13_api_bands', json_encode( $bands ), 60*60*24*30 );

	ob_clean();
	echo json_encode( $bands );
} else {
	$bands = get_transient( 'fest13_api_bands' );

	ob_clean();
	echo $bands;
}
?>