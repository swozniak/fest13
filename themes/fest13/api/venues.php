<?php
/**
 * Template Name: API - Venues
 *
 */

header('Content-Type: application/json');
/*

    {
        "_id": "the-ritz",
        "name": "The Ritz",
        "address": "1503 E 7th Ave",
        "latitude": "27.95993",
        "longitude": "-82.44271",
        "city": "Tampa",
        "age_limit": "All Ages"
    },


*/

if ( false === ( get_transient( 'fest13_api_venues' ) ) ) {
	global $wpdb;

	$venues = Array();
	$venues_query = $wpdb->get_results( "SELECT ID, post_title, post_name, post_content from $wpdb->posts WHERE post_status='publish' AND post_type='venues' ORDER BY post_name ASC", ARRAY_A );

	foreach ( $venues_query as $venue_result ) {
		$venue_ID = $venue_result['ID'];
		$venue_custom = get_post_custom( $venue_ID );
		
		$venue_urls = Array();

		$venue = Array( 
			'id' => $venue_ID,
			'name' => $venue_result['post_title'],
			'url' => 'https://thefestfl.com/venues/' . $venue_result['post_name'] . '/', 
			'bio' => $venue_result['post_content'],
			'bio_text' => wp_strip_all_tags( $venue_result['post_content'] ),
			'address' => $venue_custom['wpcf-address'][0],
			'city' => $venue_custom['wpcf-city'][0],
			'longitude' => $venue_custom['wpcf-longitude'][0],
			'latitude' => $venue_custom['wpcf-latitude'][0],
			'photo_url' => $venue_custom['wpcf-venue-photo'][0],
			'age_limit' => $venue_custom['wpcf-age-limit'][0],
			'capacity' => $venue_custom['wpcf-capacity'][0],
			'stage_size' => $venue_custom['wpcf-stage-size'][0],
			'sound_provided' => $venue_custom['wpcf-sound-provided'][0],
			'beer' => $venue_custom['wpcf-beer'][0],
			'wine' => $venue_custom['wpcf-wine'][0],
			'liquor' => $venue_custom['wpcf-liquor'][0],
			'food' => $venue_custom['wpcf-food'][0]
		);

		for ( $x = 0; $x < count( $venue_custom['wpcf-venue-website-title'] ); $x++ ) {
			array_push( $venue_urls, array( 
				'title' => $venue_custom['wpcf-venue-website-title'][$x],
				'url' => $venue_custom['wpcf-venue-website-url'][$x] 
				)
			);
		}
		$venue['links'] = $venue_urls;

		$venues[$venue_ID] = $venue;
	}
	set_transient( 'fest13_api_venues', json_encode( $venues ), 60*60*24*30 );

	ob_clean();
	echo json_encode( $venues );
} else {
	$venues = get_transient( 'fest13_api_venues' );
	
	ob_clean();
	echo $venues;
}
?>