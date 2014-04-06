<?php
/**
 * Template Name: Fest 13 - Page Content
 *
 */


get_header(); ?>


<div class="col-xs-12 col-md-8" id="content-column">
<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
				<div class="entry-thumbnail">
					<?php the_post_thumbnail(); ?>
				</div>
				<?php endif; ?>

				<div class="page-header">	
					<?php the_title( '<h1>', '</h1>' ); ?>
				</div>
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

<?php get_sidebar(); ?>
<?php get_footer(); ?>