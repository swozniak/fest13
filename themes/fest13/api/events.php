<?php
/**
 * Template Name: API - Events
 *
 */

header('Content-Type: application/json');
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

if ( false === ( get_transient( 'fest13_api_bands' ) ) ) {
	require_once( 'bands.php' );
}
$bands = json_decode( get_transient( 'fest13_api_bands' ), true );

if ( false === ( get_transient( 'fest13_api_venues' ) ) ) {
	require_once( 'venues.php' );
}
$venues = json_decode( get_transient( 'fest13_api_venues' ), true );

if ( false === ( get_transient( 'fest13_api_events' ) ) ) {
	global $wpdb;

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
	set_transient( 'fest13_api_events', json_encode( array_values( $events ) ), 60*60*24*30 );
	echo json_encode( $events );
} else {
	$events = get_transient( 'fest13_api_events' );
	echo $events;
}
?>