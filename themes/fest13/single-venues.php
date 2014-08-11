<?php
/**
 * Template Name: Fest 13 - Venue (Single)
 *
 */

$custom = get_post_custom();
$prefest = ( $custom['wpcf-city'][0] === 'Tampa' );

if ( $prefest ) {
	get_header( 'prefest' );
} else {
	get_header();
}

?>
<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		    $websites = $custom['wpcf-venue-website-url'];
		    $websiteTitles = $custom['wpcf-venue-website-title'];
		    $websiteCount = count( $websites );
		?>
		<div class="col-xs-12 col-md-8 venue-content" id="content-column">
			<?php 
				$photo_id = get_attachment_id_from_src($custom['wpcf-venue-photo'][0]);
				$photo_urls = wp_get_attachment_image_src( $photo_id, 'large' );
				$photo_url = $photo_urls[0];
			?>
			<img class="img-responsive" src="<?php echo $photo_url; ?>" />
			<?php the_title( '<h2 class="post-title">', '</h2>' ); ?><br />
			<?php $address = $custom['wpcf-address'][0] . ', ' . $custom['wpcf-city'][0] . ', FL'; ?>
			<h5 class="hometown"><?php echo $address ?></h5>
			<?php echo the_content(); ?>
			<br /><br />

			<?php
			/*
			if ( $prefest ) {
				echo '<h3 class="hometown">Pre-FEST Schedule</h3>';
			} else {
				echo '<h3 class="hometown">FEST Schedule</h3>';
			}

			global $wpdb;
			$venue_id = $post->ID;
			$event_results = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wpcf_belongs_venues_id' AND meta_value = $venue_id ORDER BY meta_value ASC", OBJECT );
			foreach ($event_results as $event_id) {
				$event = get_post( $event_id->post_id );
				$event_custom = get_post_custom( $event_id->post_id );

				$band_id = $event_custom['_wpcf_belongs_bands_id'][0];
				$band_name = '';
				$band_results = $wpdb->get_results( "SELECT post_title FROM $wpdb->posts WHERE ID = $band_id", OBJECT );
				$band_name = $band_results[0]->post_title;
				echo $band_name . $event_custom['wpcf-memo'][0];
				echo '<br />';
			} 
			*/ 
			?>
		
		</div>

		<div class="col-xs-12 col-md-4 venue-sidebar" id="sidebar-column">
			<div style="height:2em;"></div>
			<?php if ( $custom['wpcf-address'][0] ): ?>
				<div class="widget">
				<h4>Map</h4>
					<iframe width="100%" height="320" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?q=<?php echo rawurlencode( $address ); ?>&key=AIzaSyAJS1vZE31eaYr30-xiASx4h8Dm_IAtbSw"></iframe>
				</div>
			<?php endif;?>
			<?php if ( $custom['wpcf-age-limit'][0] ): ?>
				<hr class="widget-divider" />
				<div class="widget">
					<h4>Venue Info</h4>

					<?php if ( $custom['wpcf-age-limit'][0] ): ?>
						<div class="widget">
							<h5>Age Limit</h5>
							<p><?php echo $custom['wpcf-age-limit'][0]; ?></p>
						</div>
					<?php endif;?>
					<?php if ( $custom['wpcf-capacity'][0] ): ?>
						<div class="widget">
							<h5>Capacity</h5>
							<p><?php echo $custom['wpcf-capacity'][0]; ?></p>
						</div>
					<?php endif;?>
					<?php if ( $custom['wpcf-stage-size'][0] ): ?>
						<div class="widget">
							<h5>Stage Size</h5>
							<p><?php echo $custom['wpcf-stage-size'][0]; ?></p>
						</div>
					<?php endif;?>
					<?php if ( $custom['wpcf-sound-provided'][0] ): ?>
						<div class="widget">
							<h5>Sound Provided</h5>
							<p><?php echo $custom['wpcf-sound-provided'][0]; ?></p>
						</div>
					<?php endif;?>
				</div>
			<?php endif; ?>
			
			<?php if ( $custom['wpcf-food'][0] || $custom['wpcf-beer'][0] || $custom['wpcf-wine'][0] || $custom['wpcf-liquor'][0] ): ?>
				<hr class="widget-divider" />
				<div class="widget">
					<?php if ( $custom['wpcf-beer'][0] || $custom['wpcf-wine'][0] || $custom['wpcf-liquor'][0] ): ?>
						<h4>Drinks</h4>
						<?php if ( $custom['wpcf-beer'][0] ): ?>
							<h5>Beer</h5>
							<p><?php echo $custom['wpcf-beer'][0]; ?></p>
						<?php endif; ?>
						<?php if ( $custom['wpcf-wine'][0] ): ?>
							<h5>Wine</h5>
							<p><?php echo $custom['wpcf-wine'][0]; ?></p>
						<?php endif; ?>
						<?php if ( $custom['wpcf-liquor'][0] ): ?>
							<h5>Liquor</h5>
							<p><?php echo $custom['wpcf-liquor'][0]; ?></p>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( $custom['wpcf-food'][0] ): ?>
						<br />
						<h4>Food</h4>
						<p><?php echo $custom['wpcf-food'][0]; ?></p>
					<?php endif;?>
				</div>
			<?php endif;?>
			<?php if ( $websiteCount > 0 ): ?>
				<hr class="widget-divider" />
				<div class="widget">
					<div class="widget-text wp_widget_plugin_box">
						<h4>Website<?php if ( $websiteCount !== 1 ) echo 's'; ?> </h4>
						<div>
							<ul class="venue-sidebar-websites">
							<?php for ($i = 0; $i < $websiteCount; $i++): ?>
								<li><a target="_blank" href="<?php echo $websites[$i]; ?>"><?php echo $websiteTitles[$i]; ?></a></li>
							<?php endfor; ?>
							</ul>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<div style="height:2em;"></div>
		</div>
   <?php endwhile; ?>
<?php endif; ?>

<?php
get_footer();
?>