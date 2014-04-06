<?php
/**
 * Template Name: Fest 13 - Venues
 *
 */

get_header();
?>

<style>
	a, a:hover, a:active { 
		color: inherit;
	}
</style>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
		<h1>Venues</h1>

		<?php
			$args = array( 'post_type' => 'venue', 'order'=> 'ASC', 'orderby' => 'title', 'post_status' => 'publish' );
			$postslist = get_posts( $args );
			foreach ( $postslist as $post ) :
			  setup_postdata( $post );
			  $custom = get_post_custom();
		    ?> 
		    <pre><?php print_r( $post ); print_r( $custom ); ?></pre>

			<?php
			endforeach; 
			wp_reset_postdata();
		?>
		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_footer();
