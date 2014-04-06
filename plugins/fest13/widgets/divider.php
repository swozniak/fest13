<?php
class wp_fest13_divider_widget extends WP_Widget {
	// constructor
	function wp_fest13_divider_widget() {
        parent::WP_Widget( false, 
        	$name = __( 'Fest13 - Divider', 'wp_widget_plugin' ), 
        	array( 'description' => __( 'Add horizontal rule between widgets', 'text_domain' ), ) );
	}

	// widget form creation
	function form( $instance ) {
		return $instance;
	}

	// update widget
	function update( $new_instance, $old_instance ) {
		return $old_instance;
	}

	// display widget
	function widget( $args, $instance ) {
		echo $before_widget;
		echo '<hr class="widget-divider" />';
		echo $after_widget;
	}
}