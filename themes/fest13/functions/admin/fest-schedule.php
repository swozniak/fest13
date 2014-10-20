<?php
/* Add FEST VIDEO ARCHIVE admin page */
add_action( "admin_menu", "fest_schedule_menu" );

function fest_schedule_menu() {
	add_menu_page( "Fest Schedule - Refresh", "Fest Schedule Refresh", "manage_options", "fest-schedule", "fest_schedule_menu_options" );
}

function fest_schedule_menu_options() {
	if ( !current_user_can( "manage_options" ) )  {
		wp_die( __( "You do not have sufficient permissions to access this page." ) );
	}

	if ( $_SERVER["REQUEST_METHOD"] === "POST" ) :
		delete_transient( 'fest13_api_bands' );
		delete_transient( 'fest13_api_venues' );
		delete_transient( 'fest13_api_events' );

		delete_transient( 'fest13_api_bands_updated' );
		delete_transient( 'fest13_api_venues_updated' );
		delete_transient( 'fest13_api_events_updated' );

		$protocol = ( !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

		$events_url = $protocol . $_SERVER['HTTP_HOST'] . '/api/v1/events?ts=' . time();
		$events_response = file_get_contents( $events_url );
	?> 
		All done! Go check the <a href="<?php echo $protocol . $_SERVER['HTTP_HOST']; ?>/schedule/">SCHEDULE</a> page to make sure everything looks good!
	<?php else : ?>
	<br />
	<form method="POST">
		<input type="submit" value="Refresh the schedule" />
	</form>

<?php endif; }