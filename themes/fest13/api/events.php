<?php
/**
 * Template Name: API - Events
 *
 */

header('Content-Type: application/json');

define( 'DONOTCACHEPAGE', true );
/*

    {
        "day": 1,
        "venue": {
            "id": "crowbar",
            "name": "Crowbar"
        },
        "band": {
            "ids": [
                "heartless"
            ],
            "name": "Heartless"
        },
        "show_id": "heartless-prefest",
        "start_timestamp": "2013-10-29 18:20:00 -0400",
        "start_epoch": 1383085200000,
        "start_string": "6:20pm",
        "end_timestamp": "2013-10-29 18:50:00 -0400",
        "end_epoch": 1383087000000,
        "end_string": "6:50pm"
    },

*/

function get_fest_date( $fest_day ) {
	$year = 2014;
	$month = 10;

	switch ( $fest_day ) {
		case 1:
			$day = 29;
			break;
		case 2:
			$day = 30;
			break;
		case 3:
			$day = 31;
			break;
		case 4:
			$month = 11;
			$day = 1;
			break;
		case 5:
			$month = 11;
			$day = 2;
			break;
	}
	return implode( '-', array( $year, $month, $day ) ); 
}

function get_unix_timestamp( $fest_day, $time_string ) {
	date_default_timezone_set( 'America/New_York' );

	$date = get_fest_date( $fest_day );
	$time_array = explode( ':', $time_string );
	$hour = $time_array[0];
	$minute = $time_array[1];
	$ante_meridiem = strpos( $minute, 'AM' );

	if ($ante_meridiem === false) {
		if ( $hour != 12 ) {
			$hour += 12;
		}
		$minute = str_replace(' PM', '', $minute);
	} else {
		if ( $hour == 12 ) {
			$hour += 12;
		} else if ( $hour <= 6 ) { // if up to 6:00 AM, actually assume they mean the next day) {
			$hour += 24;
		}
		$minute = str_replace(' AM', '', $minute);
	}

	$date = new DateTime( $date );
	$date->setTime( $hour, intval( $minute ) );
	return $date->getTimestamp();
}


/* TESTING THIS UNIX TIMESTAMP THING

date_default_timezone_set( 'America/New_York' );
$date = new DateTime();

$date->setTimestamp( get_unix_timestamp( 1, '6:00 PM' ) );
echo 'Day 1 at 6PM' . "\n";
echo '10/28 18:00' . "\n";
echo $date->format('m/d H:i') . "\n\n";

$date->setTimestamp( get_unix_timestamp( 1, '11:59 PM' ) );
echo 'Day 1 at 11:59PM' . "\n";
echo '10/28 23:59' . "\n";
echo $date->format('m/d H:i') . "\n\n";

$date->setTimestamp( get_unix_timestamp( 1, '12:00 AM' ) );
echo 'Day 1 at 12AM' . "\n";
echo '10/29 00:00' . "\n";
echo $date->format('m/d H:i') . "\n\n";

$date->setTimestamp( get_unix_timestamp( 1, '12:30 AM' ) );
echo 'Day 1 at 12:30AM' . "\n";
echo '10/29 00:30' . "\n";
echo $date->format('m/d H:i') . "\n\n";

$date->setTimestamp( get_unix_timestamp( 2, '7:00 AM' ) );
echo 'Day 2 at 7:00AM' . "\n";
echo '10/29 07:00' . "\n";
echo $date->format('m/d H:i') . "\n\n";
echo "\n\n\n\n\n";
*/


if ( false === ( get_transient( 'fest13_api_events' ) ) ) {
	global $wpdb;

	$bands = Array();
	$bands_query = $wpdb->get_results( "SELECT ID, post_title, post_name, post_content from $wpdb->posts WHERE post_status='publish' AND post_type='bands' ORDER BY post_name ASC", ARRAY_A );

	foreach ( $bands_query as $band_result ) {
		$band_ID = $band_result['ID'];
		$band_custom = get_post_custom( $band_ID );

		$band_urls = Array();

		$mp3_url = $band_custom['wpcf-mp3'][0];
		$photo_url = $band_custom['wpcf-photo'][0];

		$query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$photo_url'";
		$photo_id = $wpdb->get_var( $query );
		$photo_object = wp_get_attachment_image_src( $photo_id, 'medium' );
		$photo_url = $photo_object[0];

		$mp3_url = str_replace( '107.170.74.175', 'thefestfl.com', $mp3_url );
		$photo_url = str_replace( '107.170.74.175', 'thefestfl.com', $photo_url );

		$band = Array( 
			'id' => $band_ID,
			'url' => 'https://thefestfl.com/bands/' . $band_result['post_name'] . '/', 
			'name' => $band_result['post_title'],
			'bio' => $band_result['post_content'],
			'bio_text' => wp_strip_all_tags( $band_result['post_content'] ),
			'hometown' => $band_custom['wpcf-hometown'][0],
			'photo_url' => $photo_url,
			'mp3_url' => $mp3_url,
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


	$venues = Array();
	$venues_query = $wpdb->get_results( "SELECT ID, post_title, post_name, post_content from $wpdb->posts WHERE post_status='publish' AND post_type='venues' ORDER BY post_name ASC", ARRAY_A );

	foreach ( $venues_query as $venue_result ) {
		$venue_ID = $venue_result['ID'];
		$venue_custom = get_post_custom( $venue_ID );
		
		$venue_urls = Array();

		$photo_url = $venue_custom['wpcf-venue-photo'][0];

		$query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$photo_url'";
		$photo_id = $wpdb->get_var( $query );
		$photo_object = wp_get_attachment_image_src( $photo_id, 'medium' );
		$photo_url = $photo_object[0];

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
			'photo_url' => $photo_url,
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

	$events = Array();
	$events_query = $wpdb->get_results( "SELECT ID from $wpdb->posts WHERE post_status='publish' AND post_type='events' ORDER BY post_name ASC", ARRAY_A );
	
	foreach ( $events_query as $event_result ) {
		$event_ID = $event_result['ID'];
		$event_custom = get_post_custom( $event_ID );

		$venue_sched_nickname_ID = $event_custom['_wpcf_belongs_venue-for-schedule_id'][0];
		$venue_sched_nickname_post = get_post( $venue_sched_nickname_ID, ARRAY_A );
		$venue_sched_nickname_custom = get_post_custom( $venue_sched_nickname_ID );
		$venue_ID = $venue_sched_nickname_custom['_wpcf_belongs_venues_id'][0];

		$venue = Array(
			'id' => $venue_ID,
			'nickname_id' => $venue_sched_nickname_ID,
			'nickname' => $venue_sched_nickname_post['post_title']
		);

		$band_result = $bands[$event_custom['_wpcf_belongs_bands_id'][0]];
		$band = null;

		if ( isset( $band_result['id'] ) ) {
			$band = array( 
				'id' => $band_result['id'],
				'name' => $band_result['name'],
				'url' => $band_result['url']
			);
		}

		$event = Array( 
			'id' => $event_ID,
			'venue' => $venue,
			'band' => $band,
			'day' => $event_custom['wpcf-fest-day'][0],
			'memo' => $event_custom['wpcf-memo'][0],
			'start_epoch' => get_unix_timestamp( $event_custom['wpcf-fest-day'][0], $event_custom['wpcf-start-time'][0] ),
			'start_string' => $event_custom['wpcf-start-time'][0],
			'end_epoch' => get_unix_timestamp( $event_custom['wpcf-fest-day'][0], $event_custom['wpcf-end-time'][0] ),
			'end_string' => $event_custom['wpcf-end-time'][0]
		);

		$events[$event_ID] = $event;
	}
	set_transient( 'fest13_api_events', json_encode( $events ), 60*60*24*30 );	

	ob_clean();
	echo json_encode( $events );
} else {
	$events = get_transient( 'fest13_api_events' );
	
	ob_clean();
	echo $events;
}
?>