<?php
/**
 * Template Name: Fest 13 - Store
 *
 */
?>

<?php get_header(); ?>

<?php
	while( $tickets->have_posts() ) : $tickets->the_post();
		//wp_update_post( array( 'ID' => get_the_ID(), 'post_status' => 'publish' ) );
		$id = get_the_ID();
		echo do_shortcode("[product id=$id]");
	endwhile;
?>
<?php get_footer(); ?>
