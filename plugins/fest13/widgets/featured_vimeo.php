<?php
class wp_fest13_featured_vimeo_widget extends WP_Widget {
	// constructor
	function wp_fest13_featured_vimeo_widget() {
        parent::WP_Widget( false, 
        	$name = __( 'Fest13 - Featured Vimeo Video', 'wp_widget_plugin' ), 
        	array( 'description' => __( 'Embed Vimeo Video', 'text_domain' ), ) );
	}

	// widget form creation
	function form( $instance ) {
		if ( $instance ) {
		     $title = esc_attr( $instance['title'] );
		     $vimeoID = esc_attr( $instance['vimeoID'] );
		} else {
		     $title = '';
		     $vimeoID = '';
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'wp_widget_plugin' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'vimeoID' ); ?>"><?php _e( 'Vimeo video ID:', 'wp_widget_plugin' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'vimeoID' ); ?>" name="<?php echo $this->get_field_name( 'vimeoID' ); ?>" type="text" value="<?php echo $vimeoID; ?>" />
		</p>

		<?php
	}

	// update widget
	function update( $new_instance, $old_instance ) {
	      $instance = $old_instance;
	      // Fields
	      $instance['title'] = strip_tags( $new_instance['title'] );
	      $instance['vimeoID'] = strip_tags( $new_instance['vimeoID'] );
	     return $instance;
	}

	// display widget
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$vimeoID = $instance['vimeoID'];
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';

		// Check if title is set
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		// Check if text is set
		if ( $vimeoID ) {
			$embedUrl = '//player.vimeo.com/video/' . $vimeoID . '?title=0&byline=0&portrait=0';
			echo '<div>	
			    	<iframe width="290" height="218" frameborder="0" src="' . $embedUrl . 'webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' .
				'</div>';
		}
		echo '</div>';
		echo $after_widget;
	}
}