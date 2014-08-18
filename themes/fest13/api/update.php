<?php
/**
 * Template Name: API - Update
 *
 */

header('Content-Type: application/json');

$update = array();

$bands = 0;
$venues = 0;
$events = 0;

if ( false !== ( get_transient( 'fest13_api_bands_updated' ) ) ) {
	$bands = get_transient( 'fest13_api_bands_updated' );
}
if ( false !== ( get_transient( 'fest13_api_venues_updated' ) ) ) {
	$venues = get_transient( 'fest13_api_venues_updated' );
}
if ( false !== ( get_transient( 'fest13_api_events_updated' ) ) ) {
	$events = get_transient( 'fest13_api_events_updated' );
}

$update['bands'] = $bands;
$update['venues'] = $venues;
$update['events'] = $events;

ob_clean();
echo json_encode( $update );
?>