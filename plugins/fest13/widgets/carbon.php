<?php
class wp_fest13_carbon_widget extends WP_Widget {
	// constructor
	function wp_fest13_carbon_widget() {
        parent::WP_Widget( false, 
        	$name = __( 'Fest13 - Carbon Calculator', 'wp_widget_plugin' ), 
        	array( 'description' => __( 'From We Are Neutral', 'text_domain' ), ) );
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
		extract( $args );
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';
		echo '<iframe frameborder="0" width="290px" height="552px" src="//weareneutral.com/wancc-carbon-calculator-9/" allowtransparency="true"></iframe>';
		echo '</div>';
		echo $after_widget;
	}
}