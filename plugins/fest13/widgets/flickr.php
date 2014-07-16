<?php
class wp_fest13_flickr_widget extends WP_Widget {
	function wp_fest13_flickr_widget() {
        parent::WP_Widget( false, 
        	$name = __( 'Fest13 - Flickr', 'wp_widget_plugin' ), 
        	array( 'description' => __( 'Embed recent Flickr uploads from the @thefestfl account.', 'text_domain' ), ) );
	}

	/**
	 * Render widget controls.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$username = isset( $instance['username'] ) ? $instance['username'] : '';
		$count = isset( $instance['count'] ) ? absint( $instance['count'] ) : 8;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e( 'Username or RSS:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo esc_attr( $username ); ?>" />
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Count:' ); ?></label><br />
			<input type="number" min="1" max="20" value="<?php echo esc_attr( $count ); ?>" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" />
		</p>

		<?php
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		$photos = $this->get_photos( array(
			'username' => $instance['username'],
			'count' => $instance['count']
		) );

		echo '<div class="row">';

		if ( is_wp_error( $photos ) ) {
			echo $photos->get_error_message();
		} else {
			foreach ( $photos as $photo ) {
				$link = esc_url( $photo->link );
				$src = esc_url( $photo->media->m );
				$title = esc_attr( $photo->title );

				// http or https
				$src = str_replace( 'http://', '//', $src);

				$item = sprintf( '<a target="_blank" href="%s"><img src="%s" alt="%s" /></a>', $link, $src, $title );
				$item = sprintf( '<div class="col-xs-6 fest-flickr-item" style="background-image: url(\'%s\')">%s</div>', $src, $item );
				echo $item;
			}
		}

		echo '</div>';
		echo '<br /><a target="_blank" href="http://www.flickr.com/photos/thefestfl/">View more on Flickr!</a>';

		echo $args['after_widget'];
	}

	/**
	 * Returns an array of photos on a WP_Error.
	 */
	private function get_photos( $args = array() ) {
		$transient_key = md5( 'afest-flickr-cache-' . print_r( $args, true ) );
		$cached = get_transient( $transient_key );
		if ( $cached )
			return $cached;

		$username = isset( $args['username'] ) ? $args['username'] : '';
		$count = isset( $args['count'] ) ? absint( $args['count'] ) : 10;
		$query = array(
			'tagmode' => 'any'
		);

		// If username is an RSS feed
		if ( preg_match( '#^https?://api\.flickr\.com/services/feeds/photos_public\.gne#', $username ) ) {
			$url = parse_url( $username );
			$url_query = array();
			wp_parse_str( $url['query'], $url_query );
			$query = array_merge( $query, $url_query );
		} else {
			$user = $this->request( 'flickr.people.findByUsername', array( 'username' => $username ) );
			if ( is_wp_error( $user ) )
				return $user;

			$user_id = $user->user->id;
			$query['id'] = $user_id;
		}

		$photos = $this->request_feed( 'photos_public', $query );

		if ( ! $photos )
			return new WP_Error( 'error', 'Could not fetch photos.' );

		$photos = array_slice( $photos, 0, $count );
		set_transient( $transient_key, $photos, apply_filters( 'fest_flickr_widget_cache_timeout', 3600 ) );
		return $photos;
	}

	/**
	 * Make a request to the Flickr API.
	 */
	private function request( $method, $args ) {
		$args['method'] = $method;
		$args['format'] = 'json';
		$args['api_key'] = '41443d38c4e7b8ca70841b903624afa1';
		$args['nojsoncallback'] = 1;
		$url = esc_url_raw( add_query_arg( $args, 'https://api.flickr.com/services/rest/' ) );

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) )
			return false;

		$body = wp_remote_retrieve_body( $response );
 		$obj = json_decode( $body );

		if ( $obj && $obj->stat == 'fail' )
			return new WP_Error( 'error', $obj->message );

		return $obj ? $obj : false;
	}

	/**
	 * Fetch items from the Flickr Feed API.
	 */
	private function request_feed( $feed = 'photos_public', $args = array() ) {
		$args['format'] = 'json';
		$args['nojsoncallback'] = 1;
		$url = sprintf( 'http://api.flickr.com/services/feeds/%s.gne', $feed );
		$url = esc_url_raw( add_query_arg( $args, $url ) );

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) )
			return false;
		
		$body = wp_remote_retrieve_body( $response );
		$body = preg_replace( "#\\\\'#", "\\\\\\'", $body );
 		$obj = json_decode( $body );

		return $obj ? $obj->items : false;

	}

	/**
	 * Validate and update widget options.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['username'] = strip_tags( $new_instance['username'] );
		$instance['count'] = absint( $new_instance['count'] );
		return $new_instance;
	}

}