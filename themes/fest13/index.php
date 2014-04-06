<?php
/**
 * The main template file
 */

get_header(); 

$posts = new WP_Query( array(
   'posts_per_page' => 311,
   'category_name' => 'fest-news'
) );
?>

<div class="col-xs-12 col-md-8" id="content-column">
<?php if ( $posts->have_posts() ) : ?>
	<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
		<div class="post">
			<?php the_title( '<h2 class="post-title">', '</h2>' ); ?>
			<h3 class="post-author">Posted by <?php the_author(); ?> on <?php the_time('F j, Y'); ?> at <?php the_time('g:ia'); ?></h3>
			<?php the_content(); ?>
			<div class="post-category">
				<?php 
					$categories = get_the_category();
					$category_output = array();
					foreach ( $categories as $category ) {
						if ( $category->name === 'Fest 13') {
							array_push( $category_output, '<a href="/">Fest 13 News</a>' );
						} elseif ( $category->name === 'Pre-Fest 2') {
							array_push( $category_output, '<a href="/prefest/">Pre-Fest 2 News</a>' );
						}
					}

				if ( count( $category_output ) > 0 ) {
					echo 'Category: ' . join( ', ', $category_output );
				}
				?>
			</div>
		</div>
   <?php endwhile; ?>
<?php else : ?>
	<?php get_template_part( 'content', 'none' ); ?>
<?php endif; ?>
</div>

<?php get_sidebar( 'fest13-main' ); ?>
<?php get_footer(); ?>