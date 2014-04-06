<?php
/**
 * Template Name: Pre-Fest 2 - Coming Soon
 *
 */

get_header( 'prefest' ); ?>

<div class="col-xs-12 col-md-8" id="content-column">
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<div class="page-header">
				<?php the_title( '<h1>', '</h1>' ); ?>
			</div>
		</header>

		<div class="entry-content">
			Coming soon!!!
		</div>
	</article>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>