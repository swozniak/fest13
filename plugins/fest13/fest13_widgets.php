<?php
/*
Plugin Name: Fest - Custom Widgets
Description: Generates Custom Widgets for thefestfl.com sidebars
Version: 1.0
Author: Steve Wozniak
License: GPL2
*/

require_once('widgets/featured_band.php');
require_once('widgets/featured_video.php');
require_once('widgets/social_media.php');
require_once('widgets/upload-ad.php');
require_once('widgets/flickr.php');
require_once('widgets/divider.php');

add_action( 'widgets_init', 'unregister_default_widgets' );
add_action( 'widgets_init', create_function( '', 'return register_widget( "wp_fest13_featured_band_widget" );' ) );
add_action( 'widgets_init', create_function( '', 'return register_widget( "wp_fest13_featured_video_widget" );' ) );
add_action( 'widgets_init', create_function( '', 'return register_widget( "wp_fest13_social_media_widget" );' ) );
add_action( 'widgets_init', create_function( '', 'return register_widget( "wp_fest13_upload_ad_widget" );' ) );
add_action( 'widgets_init', create_function( '', 'return register_widget( "wp_fest13_flickr_widget" );' ) );
add_action( 'widgets_init', create_function( '', 'return register_widget( "wp_fest13_divider_widget" );' ) );


// unregister all widgets
function unregister_default_widgets() {
	unregister_widget('WP_Widget_Pages');
	unregister_widget('WP_Widget_Calendar');
	unregister_widget('WP_Widget_Archives');
	unregister_widget('WP_Widget_Links');
	unregister_widget('WP_Widget_Meta');
	unregister_widget('WP_Widget_Search');
	unregister_widget('WP_Widget_Text');
	unregister_widget('WP_Widget_Categories');
	unregister_widget('WP_Widget_Recent_Comments');
	unregister_widget('WP_Widget_RSS');
	unregister_widget('WP_Widget_Tag_Cloud');
	unregister_widget('WP_Nav_Menu_Widget');
	unregister_widget('Twenty_Eleven_Ephemera_Widget');
}