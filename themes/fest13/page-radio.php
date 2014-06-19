<?php
/**
 * Template Name: Fest 13 - Radio
 *
 */

get_header( 'radio' );
?>

<div class="fest13-radio-fixed-header">
	<div class="fest13-radio-fixed-header-logo">
		<img src="<?php bloginfo( 'template_directory' ); ?>/img/radio/logo.png" />
	</div>
	<div class="fest13-radio-fixed-header-link">
		<a target="_blank" href="http://thefestfl.com">
			<img src="<?php bloginfo( 'template_directory' ); ?>/img/radio/link.png" />
		</a>
	</div>
	<div style="clear:both;"></div>
</div>
<div class="fest13-radio-fixed-scrolling-fix"></div>
<div class="col-xs-12 radio-content" id="content-column">
<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
			</header>

			<div class="entry-content">
				<?php the_content(); ?>
				<?php wp_link_pages( array( 'before' => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentythirteen' ) . '</span>', 'after' => '</div>', 'link_before' => '<span>', 'link_after' => '</span>' ) ); ?>
			</div><!-- .entry-content -->

		</article><!-- #post -->
		<?php // comments_template(); ?>
	<?php endwhile; ?>
<?php endif; ?>
</div>
<?php get_footer( 'radio' ); ?>