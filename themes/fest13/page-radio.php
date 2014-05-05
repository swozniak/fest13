<?php
/**
 * Template Name: Fest 13 - Radio
 *
 */

get_header( 'radio' );
?>

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
<div class="fest13-fixed-scrolling-fix"></div>
<?php get_footer( 'radio' ); ?>