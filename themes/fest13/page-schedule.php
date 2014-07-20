<?php
/**
 * Template Name: Fest 13 - Schedule
 *
 */

get_fest13_header(); ?>

<div class="col-xs-12" id="content-column">
	<div class="page-header">	
		<h1>Schedule</h1>
		<div class="fest13-schedule-container">
			<?php print_r( $venue_names ); ?>
		</div>
	</div>
</div>

<?php
/* wp_enqueue_script( 'underscore', get_template_directory_uri() . '/js/lib/underscore.js' );
 wp_enqueue_script( 'backbone', get_template_directory_uri() . '/js/lib/backbone.js', array( 'jquery', 'underscore' ) );
 wp_enqueue_script( 'backbone.babysitter', get_template_directory_uri() . '/js/lib/backbone.babysitter.js', array( 'backbone' ) );
 wp_enqueue_script( 'backbone.wreqr', get_template_directory_uri() . '/js/lib/backbone.wreqr.js', array( 'backbone' ) );
 wp_enqueue_script( 'backbone.marionette', get_template_directory_uri() . '/js/lib/backbone.marionette.min.js', array( 'backbone' ) );

 wp_enqueue_script( 'fest13_schedule', get_template_directory_uri() . '/js/schedule/App.js' ); */
 ?>
<?php get_footer(); ?>