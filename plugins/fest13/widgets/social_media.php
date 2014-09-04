<?php
class wp_fest13_social_media_widget extends WP_Widget {
	// constructor
	function wp_fest13_social_media_widget() {
        parent::WP_Widget( false, 
        	$name = __( 'Fest13 - Social Media Icons', 'wp_widget_plugin' ), 
        	array( 'description' => __( 'Display icons for Facebook, Twitter, Instagram, and Flickr', 'text_domain' ), ) );
	}

	// widget form creation
	function form( $instance ) {
		if ( $instance ) {
		     $title = esc_attr( $instance['title'] );
		} else {
		     $title = '';
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'wp_widget_plugin' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<?php
	}

	// update widget
	function update( $new_instance, $old_instance ) {
	      $instance = $old_instance;
	      // Fields
	      $instance['title'] = strip_tags( $new_instance['title'] );
	     return $instance;
	}

	// display widget
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';

		// Check if title is set
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		echo '<div class="social-media-sidebar">
			<a target="_blank" href="';
		echo !is_prefest() ? 'https://www.facebook.com/thefestfl' : 'https://www.facebook.com/prefestybor';
		echo '"><img src="' . get_bloginfo( 'template_directory' ) . '/img/social/facebook-48-white.png" alt="Facebook" /></a>
            <a target="_blank" href="https://twitter.com/thefestfl"><img src="' . get_bloginfo( 'template_directory' ) . '/img/social/twitter-48-white.png" alt="Twitter" /></a>
            <a target="_blank" href="http://instagram.com/thefestfl"><img src="' . get_bloginfo( 'template_directory' ) . '/img/social/instagram-48-white.png" alt="Instagram" /></a>
            <a target="_blank" href="http://www.flickr.com/photos/thefestfl/"><img src="' . get_bloginfo( 'template_directory' ) . '/img/social/flickr-48-white.png" alt="Flickr" /></a>
            </div>';

		echo '</div>';
		echo $after_widget;
	}
}