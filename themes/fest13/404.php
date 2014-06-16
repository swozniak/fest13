<?php
/**
 * Template Name: Fest 13 - Page Content
 *
 */


get_header(); ?>


<div class="col-xs-12 col-md-8" id="content-column">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<div class="page-header">	
					<h1><?php _e( 'Page Not Found', 'fest13' ); ?></h1>
				</div>
			</header>

			<div class="entry-content">
				<?php _e( 'Sorry, nothin’ here!', 'fest13' ); ?>
			</div><!-- .entry-content -->

		</article><!-- #post -->
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>