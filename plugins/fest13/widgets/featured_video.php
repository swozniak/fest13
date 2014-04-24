<?php
class wp_fest13_featured_video_widget extends WP_Widget {
	// constructor
	function wp_fest13_featured_video_widget() {
        parent::WP_Widget( false, 
        	$name = __( 'Fest13 - Featured Video', 'wp_widget_plugin' ), 
        	array( 'description' => __( 'Embed YouTube Video', 'text_domain' ), ) );
	}

	// widget form creation
	function form( $instance ) {
		if ( $instance ) {
		     $title = esc_attr( $instance['title'] );
		     $youtubeUrl = esc_attr( $instance['youtubeUrl'] );
		} else {
		     $title = '';
		     $youtubeUrl = '';
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'wp_widget_plugin' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'youtubeUrl' ); ?>"><?php _e( 'YouTube Link:', 'wp_widget_plugin' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'youtubeUrl' ); ?>" name="<?php echo $this->get_field_name( 'youtubeUrl' ); ?>" type="text" value="<?php echo $youtubeUrl; ?>" />
		</p>

		<?php
	}

	// update widget
	function update( $new_instance, $old_instance ) {
	      $instance = $old_instance;
	      // Fields
	      $instance['title'] = strip_tags( $new_instance['title'] );
	      $instance['youtubeUrl'] = strip_tags( $new_instance['youtubeUrl'] );
	     return $instance;
	}

	// display widget
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$youtubeUrl = $instance['youtubeUrl'];
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';

		// Check if title is set
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		// Check if text is set
		if( $youtubeUrl ) {
			parse_str( parse_url( $youtubeUrl, PHP_URL_QUERY ), $youtubeQueryString );
			echo '<div>
				<iframe id="ytplayer" type="text/html" width="290" height="218" src="//www.youtube.com/embed/' . $youtubeQueryString['v'] . '" frameborder="0"></iframe>
				</div>';
		}
		echo '</div>';
		echo $after_widget;
	}
}