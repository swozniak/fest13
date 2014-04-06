<?php
/**
 * Template Name: Fest 13 - Band
 *
 */

$custom = get_post_custom();
$shown_on = unserialize( $custom['wpcf-band-listed-on'][0] );
$playing_prefest = $shown_on['wpcf-fields-checkboxes-option-352a822dea3d782a96163ec95d4c356c-2'];
$playing_fest = $shown_on['wpcf-fields-checkboxes-option-9dad90b4c10f6c8bae9de86653e3c68a-1'];
if ( !is_user_logged_in() && !$playing_prefest && !$playing_fest ) {
	header('Location: /bands');
} else {
	get_header();
}
?>
<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
			// Sort out video embed code
			$videoUrl = $custom['wpcf-video-url'][0];
			$embedUrl = null;
		    if ( strpos( $videoUrl, 'youtube') > 0 ) {
				parse_str( parse_url( $videoUrl, PHP_URL_QUERY ), $youtubeQueryString );
				$youtubeId = $youtubeQueryString['v'];
		        $embedUrl = '//www.youtube.com/embed/' . $youtubeId;
		        $embedCode = '<iframe id="ytplayer" type="text/html" width="290" height="218" frameborder="0" src="' . $embedUrl . '"></iframe>';
		    } elseif (strpos($url, 'vimeo') > 0) {
		    	$pattern = '/\/\/(www\.)?vimeo.com\/(\d+)($|\/)/';
			    preg_match( $pattern, $videoUrl, $matches );
			    if ( count( $matches ) ) {
			    	$vimeoId = $matches[2];
			    	$embedUrl = '//player.vimeo.com/video/' . $vimeoId . '?title=0&amp;byline=0&amp;portrait=0';
			    	$embedCode = '<iframe width="290" height="218" frameborder="0" src="' . $embedUrl . 'webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
			    }
		    }

		    $websites = $custom['wpcf-website-url'];
		    $websiteTitles = $custom['wpcf-website-title'];
		    $websiteCount = count( $websites );
		?>
		<div class="col-xs-12 col-md-8 band-content" id="content-column">
			<?php 
				$photo_id = get_attachment_id_from_src($custom['wpcf-photo'][0]);
				$photo_urls = wp_get_attachment_image_src( $photo_id, 'large' );
				$photo_url = $photo_urls[0];
			?>
			<img class="img-responsive" src="<?php echo $photo_url; ?>" />
			<?php the_title( '<h2 class="post-title">', '</h2>' ); ?><br />
			<h5 class="hometown"><?php echo $custom['wpcf-hometown'][0]; ?></h5>
			<?php echo the_content(); ?>
		</div>

		<div class="col-xs-12 col-md-4 band-sidebar" id="sidebar-column">
			<div class="widget-area">
			<?php if ( $playing_prefest ) : ?>
				<div class="widget">
					<img class="img-responsive" src="<?php bloginfo( 'template_directory' ); ?>/img/prefest_sticker.png" />
				</div>
				<hr class="widget-divider" />
			<?php endif; ?>
			<?php if ( $custom['wpcf-mp3'][0] ): ?>
				<div class="widget">
					<h4>MP3 Sample</h4>
					<?php echo do_shortcode( '[audio ' . $custom['wpcf-mp3'][0] . ']' ); ?>
				</div>
				<hr class="widget-divider" />
			<?php endif;?>
			<?php if ($embedCode): ?>
				<div class="widget">
					<div class="widget-text wp_widget_plugin_box">
						<h4>Featured Video</h4>
						<div>
							<?php if ($embedCode) echo $embedCode; ?>
						</div>
					</div>
				</div>
				<hr class="widget-divider" />
			<?php endif; ?>
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

<?php
get_footer();
?>
<script>
$(document).ready(function () {
	window.setTimeout(function () {
		$('.mejs-controls div.mejs-horizontal-volume-slider').css('width', '55px');
	}, 100);
})
</script>