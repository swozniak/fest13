<?php
class wp_fest13_featured_band_widget extends WP_Widget {
	// constructor
	function wp_fest13_featured_band_widget() {
        parent::WP_Widget( false, 
        	$name = __( 'Fest13 - Featured Band', 'wp_widget_plugin' ), 
        	array( 'description' => __( 'Choose Band', 'text_domain' ), ) );
	}

	// widget form creation
	function form( $instance ) {
		if ( $instance ) {
		     $title = esc_attr( $instance['title'] );
		     $featured_band_id = esc_attr( $instance['featured_band_id'] );
		} else {
		     $title = '';
		     $featured_band_id = '';
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'wp_widget_plugin' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'featured_band_id' ); ?>"><?php _e( 'Choose Band:', 'wp_widget_plugin' ); ?></label>
			<br />
			<?php 
				$args = array( 
					'posts_per_page' => 500,
					'post_type' => 'bands',
					'order'=> 'ASC',
					'orderby' => 'title',
					'post_status' => 'publish' 
					);
				$postslist = get_posts( $args );
			?>
				<select id="<?php echo $this->get_field_id( 'featured_band_id' ); ?>" name="<?php echo $this->get_field_name( 'featured_band_id' ); ?>">
			<?php 
				foreach ( $postslist as $post ) :
					setup_postdata( $post );
				?>
					<option value="<?php echo $post->ID; ?>" <?php if ( $featured_band_id == $post->ID ) { echo 'selected'; } ?>><?php echo $post->post_title; ?></option>

			<?php
				endforeach;
				wp_reset_postdata();
			?>

			<?php echo '</select>'; ?>

		</p>
		<?php
	}

	// update widget
	function update( $new_instance, $old_instance ) {
	      $instance = $old_instance;
	      // Fields
	      $instance['title'] = strip_tags( $new_instance['title'] );
	      $instance['featured_band_id'] = strip_tags( $new_instance['featured_band_id'] );

	     return $instance;
	}

	// display widget
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$post = get_post( $instance['featured_band_id'] );
		$custom = get_post_custom($post->ID);
		$photo_id = get_attachment_id_from_src( $custom['wpcf-photo'][0] );
		$photo_urls = wp_get_attachment_image_src( $photo_id, 'thumbnail' );
		$photo_url = $photo_urls[0];

		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';

		// Check if title is set
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		?> 
			<div class="row">
				<div class="band col-xs-12">
					<div class="bands-image-container">
                        <a href="<?php echo '/' . $post->post_type . '/' . $post->post_name; ?>">
							<?php 
								$photo_urls = wp_get_attachment_image_src( $photo_id, 'thumbnail' );
								$photo_url = $photo_urls[0];
							?>
							<img class="bands-image img-responsive" src="<?php echo $photo_url; ?>" />
						</a>
					</div>
					<div>
						<h2 class="bands-name-container"><a class="bands-name" href="<?php echo site_url($post->post_type . '/' . $post->post_name); ?>"><?php echo $post->post_title; ?></a></h2>
						<h5 class="bands-hometown-container"><a class="bands-hometown" href="<?php echo site_url($post->post_type . '/' . $post->post_name); ?>"><?php echo $custom['wpcf-hometown'][0]; ?></a></h5>
					</div>
				</div>
			</div>
		</div>
		<?php
		echo $after_widget;
	}
}