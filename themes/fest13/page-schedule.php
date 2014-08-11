<?php
/**
 * Template Name: Fest 13 - Schedule
 *
 */

global $wpdb;

get_fest13_header();

function time_sort( $a, $b ) {
    return ( $b['start_epoch'] - $a['start_epoch'] );
}

function get_date_label( $day ) {
	switch ( $day ) {
	case 1: 
		$label = 'Wednesday October 29th';
		break;	
	case 2: 
		$label = 'Thursday October 30th';
		break;
	case 3: 
		$label = 'Friday October 31st';
		break;
	case 4: 
		$label = 'Saturday November 1st';
		break;
	case 5: 
		$label = 'Sunday November 2nd';
		break;
	default:
		$label = '';
		break;
	}
	return $label;
}

$venues_url = 'http://localhost/api/v1/venues';
$venues_response = file_get_contents( $venues_url );
$venues_array = json_decode( $venues_response, true );

$events_url = 'http://localhost/api/v1/events';
$events_response = file_get_contents( $events_url );
$events_array = json_decode( $events_response, true );

$sort_method = ( $_GET['sort'] == 'time' ) ? 'time' : 'venue';
$schedule_days = ( is_prefest() ) ? array( 1, 2 ) : array( 3, 4, 5 );

$schedule_events = array();

if ( $sort_method !== 'time' ) {
	foreach ( $events_array as $event ) {
		if ( ( $event['venue']['nickname'] !== 'Events (API)' ) && ( in_array( $event['day'], $schedule_days, false ) ) )	 { 
			if ( is_null( $schedule_events[$event['day']][$event['venue']['nickname_id']] ) ) {
				$schedule_events[$event['day']][$event['venue']['nickname_id']] = $event['venue'];
			}
			$schedule_events[$event['day']][$event['venue']['nickname_id']]['events'][] = $event;
		}
	}
}
?>

<div class="col-xs-12" id="content-column">
	<div class="page-header">	
		<?php if ( is_prefest() ) : ?>
			<h1>Pre-Fest 2 Schedule</h1>
		<?php else : ?>
			<h1>Fest 13 Schedule</h1>
		<?php endif; ?>
	</div>

	<div class="entry-content">
	<?php foreach ( $schedule_days as $day ) : ?>
		<div class="col-xs-12">
			<h3 class="schedule-date-label"><?php echo get_date_label( $day ); ?></h3><br />
			<?php $count = 0; ?>
			<?php foreach ( $schedule_events[$day] as $venue ) : ?>
				<?php 
				$venue_info = $venues_array[$venue['id']];
				$venue_meta = get_post_custom( $venue['nickname_id'] );
				?>
				<?php if ( $count == 0) : ?>
					<?php echo '<div class="row">'; ?>
					<div class="col-xs-12 col-sm-5 schedule-venue-container">
				<?php else : ?>
					<div class="col-xs-12 col-xs-offset-0 col-sm-5 col-sm-offset-1 schedule-venue-container">
				<?php endif; ?>
					<h2 class="schedule-venue-nickname"><?php echo $venue['nickname']; ?></h2><br />
					<h3 class="schedule-venue-address"><?php echo $venue_info['address']; ?></h3>
					<?php usort( $venue['events'], 'time_sort' ); ?>

					<table>
					<?php foreach ( $venue['events'] as $event ) : ?>
						<tr>
							<td style="width:160px">
								<?php
								echo $event['start_string'];

								if ( $event['end_string'] ) {
									echo ' - ' . $event['end_string'];
								}
								?>
							</td>
							<td>
								<?php
								if ( !is_null( $event['band'] ) ) {

									if ( !is_null( $event['band']['url'] ) ) {
										printf( '<a href="%s">%s</a>', $event['band']['url'], $event['band']['name'] );
									} else {
										echo $event['band']['name'];
									}

									if ( !is_null( $event['memo'] ) && strlen( $event['memo'] ) > 0 ) {
										echo ' (' . $event['memo'] . ')';
									}
								} else {
									if ( !is_null( $event['memo'] ) && strlen( $event['memo'] ) > 0 ) {
										echo $event['memo'];
									}
								}
								?>
							</td>	
						</tr>
					<?php endforeach; ?>
					</table>
					<hr />
					<?php 
					$notes = $venue_meta[implode( '-', array( 'wpcf-venue-day',  $day, 'notes' ) )][0];
					echo $notes . '<br /><br />';
					?>
				</div>

				<?php if ( $count == 1 ) {
					echo '</div>';
					$count = 0;
				} else {
					$count = 1;
				} ?>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
	</div><!-- .entry-content -->
</div>

<?php dequeue_woocommerce_assets(); ?>
<?php get_footer(); ?>