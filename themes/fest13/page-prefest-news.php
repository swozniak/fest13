<?php
/**
 * Template Name: Pre-Fest 2 - News
 *
 */

get_header( 'prefest' ); 

$posts = new WP_Query( array(
   'posts_per_page' => 311,
   'category_name' => 'prefest-news'
) );
?>

<div class="col-xs-12 col-md-8" id="content-column">
<?php if ( $posts->have_posts() ) : ?>
	<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
		<div class="post">
			<?php the_title( '<h2 class="post-title">', '</h2>' ); ?>
			<h3 class="post-author">Posted by <?php the_author(); ?> on <?php the_time('F j, Y'); ?> at <?php the_time('g:ia'); ?></h3>
			<?php the_content(); ?>
		</div>
   <?php endwhile; ?>
	<?php wp_reset_query(); ?>
<?php else : ?>
	<?php get_template_part( 'content', 'none' ); ?>
<?php endif; ?>
</div>

<?php get_sidebar( 'fest13-main' ); ?>
<?php get_footer(); ?>