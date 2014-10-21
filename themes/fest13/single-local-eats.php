<?php
/**
 * Template Name: Fest 13 - Restaurant
 *
 */

$custom = get_post_custom();

get_header();


?>
<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		    $websites = $custom['wpcf-restaurant-website-url'];
		    $websiteTitles = $custom['wpcf-restaurant-website-title'];
		    $websiteCount = count( $websites );

		    $address = $custom['wpcf-restaurant-address'][0] . ', ' . $custom['wpcf-restaurant-city'][0] . ', FL';
		?>
		<div class="col-xs-12 col-md-8 band-content" id="content-column">
			<?php 
				$photo_id = get_attachment_id_from_src($custom['wpcf-restaurant-photo'][0]);
				$photo_urls = wp_get_attachment_image_src( $photo_id, 'large' );
				$photo_url = $photo_urls[0];
			?>
			<img class="img-responsive" src="<?php echo $photo_url; ?>" />
			<?php the_title( '<h2 class="post-title">', '</h2>' ); ?><br />
			<h5 class="hometown"><?php echo $address ?></h5>

			<?php echo the_content(); ?>

			<?php if ( $custom['wpcf-restaurant-phone'][0] ) : ?>
				<p><strong>Phone Number: </strong><?php echo $custom['wpcf-restaurant-phone'][0]; ?></p>
			<?php endif; ?>
			
			<?php if ( $custom['wpcf-restaurant-hours'][0] ) : ?>
				<p><strong>Hours: </strong><?php echo $custom['wpcf-restaurant-hours'][0]; ?></p>
			<?php endif; ?>

			<?php if ( $custom['wpcf-restaurant-price'][0] ) : ?>
				<p><strong>Price Range: </strong><?php echo $custom['wpcf-restaurant-price'][0]; ?></p>
			<?php endif; ?>

			<?php if ( $custom['wpcf-restaurant-cuisine'][0] ) : ?>
				<p><strong>Cuisine: </strong><?php echo $custom['wpcf-restaurant-cuisine'][0]; ?></p>
			<?php endif; ?>

			<?php if ( $custom['wpcf-restaurant-vegan-veggie'][0] ) : ?>
				<p><strong>Vegetarian/Vegan Options: </strong><?php echo $custom['wpcf-restaurant-vegan-veggie'][0]; ?></p>
			<?php endif; ?>

			<?php if ( $custom['wpcf-restaurant-beer-liquor'][0] ) : ?>
				<p><strong>Beer/Liquor Options: </strong><?php echo $custom['wpcf-restaurant-beer-liquor'][0]; ?></p>
			<?php endif; ?>

			<?php if ( $custom['wpcf-restaurant-fest-venue-days'][0] ) : ?>
				<p><strong>Hosting FEST shows on: </strong><?php echo $custom['wpcf-restaurant-fest-venue-days'][0]; ?></p>
			<?php endif; ?>

			<?php if ( $custom['wpcf-restaurant-thursday-monday-specials'][0] ) : ?>
				<p><strong>Thursday/Monday Specials: </strong><?php echo $custom['wpcf-restaurant-thursday-monday-specials'][0]; ?></p>
			<?php endif; ?>

		</div>

		<div class="col-xs-12 col-md-4 band-sidebar" id="sidebar-column">
			<div class="widget-area">
			
			<?php if ( $custom['wpcf-restaurant-address'][0] ): ?>
				<div class="widget">
				<h4>Map</h4>
					<iframe width="100%" height="320" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?q=<?php echo rawurlencode( $address ); ?>&key=AIzaSyAJS1vZE31eaYr30-xiASx4h8Dm_IAtbSw"></iframe>
				</div>
				<hr class="widget-divider" />
			<?php endif;?>

			<?php if ( $websiteCount > 0 ): ?>
				<div class="widget">
					<div class="widget-text wp_widget_plugin_box">
						<h4>Website<?php if ( $websiteCount !== 1 ) echo 's'; ?> </h4>
						<div>
							<ul class="band-sidebar-websites">
							<?php for ($i = 0; $i < $websiteCount; $i++): ?>
								<li><a target="_blank" href="<?php echo $websites[$i]; ?>"><?php echo $websiteTitles[$i]; ?></a></li>
							<?php endfor; ?>
							</ul>
						</div>
					</div>
				</div>
				<hr class="widget-divider" />
			<?php endif; ?>
			</div>
		</div>
   <?php endwhile; ?>
<?php endif; ?>

<?php get_footer(); ?>