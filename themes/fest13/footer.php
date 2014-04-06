<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 */
?>
			</div>
		</div>
		<footer id="colophon" class="site-footer" role="contentinfo">
			<div class="row" id="fest-footer">
				<div class="col-xs-12 col-sm-6 col-md-6 social-media">
	                <a target="_blank" href="https://www.facebook.com/pages/THE-FEST/135233613179048"><img src="<?php bloginfo( 'template_directory' ); ?>/img/social/facebook-48-white.png" /></a>
	                <a target="_blank" href="https://twitter.com/thefestfl"><img src="<?php bloginfo( 'template_directory' ); ?>/img/social/twitter-48-white.png" /></a>
	                <a target="_blank" href="http://instagram.com/thefestfl"><img src="<?php bloginfo( 'template_directory' ); ?>/img/social/instagram-48-white.png" /></a>
	                <a target="_blank" href="http://www.flickr.com/photos/thefestfl/"><img src="<?php bloginfo( 'template_directory' ); ?>/img/social/flickr-48-white.png" /></a>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6 site-info">
					Fest 13 artwork by <a target="_blank" href="<?php echo esc_url( __( 'http://www.theblackaxe.com' ) ); ?>">Richard Minino aka HORSEBITES</a><br />
					thefestfl.com design and development by <a target="_blank" href="<?php echo esc_url( __( 'http://twitter.com/stephanwozniak' ) ); ?>">Steve Wozniak</a>
				</div>
			</div>
		</footer>

		<div class="row" id="fest-tier-two-sponsors">
			<div class="col-xs-12">
			  <a target="_blank" href="http://www.nosleeprecords.com/"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_2/nosleep.png" /></a>
			  <a target="_blank" href="http://www.renaissancerecordingsatx.com/home"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_2/renaissance.png" /></a>
			  <a target="_blank" href="http://www.fatwreck.com/"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_2/fatwreck.png" /></a>
			</div>
		</div>
		<div class="row" id="fest-tier-three-sponsors">
			<div class="col-xs-12">
			  <a target="_blank" href="http://www.razorcake.org/site/"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_3/razorcake.png" /></a>
			  <a target="_blank" href="http://www.a-frecords.com/"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_3/af.png" /></a>
			  <a target="_blank" href="http://www.bridge9.com/"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_3/bridge9.png" /></a>
			  <a target="_blank" href="http://www.topshelfrecords.com/"><img src="<?php bloginfo( 'template_directory' ); ?>/img/sponsors/tier_3/topshelf.png" /></a>
			</div>
		</div>
	</div>

	<?php wp_footer(); ?>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	
	<script src="<?php bloginfo('template_directory'); ?>/js/bootstrap.min.js"></script>
	<script src="<?php bloginfo('template_directory'); ?>/js/custom.js"></script>
	<script src="<?php bloginfo('template_directory'); ?>/js/lib/jquery.unveil.js"></script>
	<script src="<?php bloginfo('template_directory'); ?>/js/bands.js"></script>
</body>
</html>