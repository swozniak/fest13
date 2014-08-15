<?php
/**
 * Template Name: Fest 13 - Bands
 *
 */

get_fest13_header();

function no_articles_allowed( $a, $b ) {
    $list = array(
        'The ' => '',
        //'A ' => '',
    );
    $patterns = array();
    $replacement = array();
    foreach ( $list as $from => $to ){
        $from = '/\b' . $from . '\b/';
        $patterns[] = $from;
        $replacement[] = $to;
    }
     $a = preg_replace( $patterns, $replacement, $a->post_title );
     $b = preg_replace( $patterns, $replacement, $b->post_title );

     return strcmp( $a, $b );
}
?>

	<div class="col-xs-12" id="content-column">
		<div class="page-header">	
			<h1>Bands</h1>
		</div>

		<div class="row" id="bands-tools">
			<div class="col-xs-12 col-md-6 bands-filter">
				<span class="filter-prefest">ONLY SHOW BANDS PLAYING PRE-FEST?</span>
				<button id="prefest-checkbox" <?php if ( $_GET['filter'] === 'prefest' ) { echo 'class="active"'; } ?> />
			</div>
			<div class="col-xs-12 col-md-6 bands-search">
				Search By Name: <input id="band-search" />
			</div>
		</div>

		<div class="row" id="bands-container">
		<?php
			$args = array( 
					'posts_per_page' => 666,
					'post_type' => 'bands',
					'order'=> 'ASC',
					'orderby' => 'title',
					'post_status' => 'publish' 
				);
			$postslist = get_posts( $args );
			usort( $postslist, 'no_articles_allowed' );

			foreach ( $postslist as $post ) :
				setup_postdata( $post );

				$custom = get_post_custom();
				$shown_on = unserialize( $custom['wpcf-band-listed-on'][0] );

				$playing_prefest = $shown_on['wpcf-fields-checkboxes-option-352a822dea3d782a96163ec95d4c356c-2'];
				$playing_fest = $shown_on['wpcf-fields-checkboxes-option-9dad90b4c10f6c8bae9de86653e3c68a-1'];

				$photo_id = get_attachment_id_from_src($custom['wpcf-photo'][0]);
		    ?> 
				<div class="band col-xs-12 col-sm-6 col-md-4" data-name="<?php echo $post->post_title; ?>" data-prefest="<?php if ($playing_prefest) { echo 'true'; } else { echo 'false'; } ?>">
					<div class="bands-image-container">
						<a href="<?php echo '/' . $post->post_type . '/' . $post->post_name; ?>">
							<?php 
								$photo_urls = wp_get_attachment_image_src( $photo_id, 'thumbnail' );
								$photo_url = $photo_urls[0];
							?>
							<img class="bands-image" src="<?php bloginfo( 'template_directory' ); ?>/img/placeholder.png" data-src="<?php echo $photo_url; ?>" />
							<noscript>
								<img class="bands-image" src="<?php echo $photo_url ?>" />
							</noscript>
						</a>
					</div>
					<div>
						<h2 class="bands-name-container"><a class="bands-name" href="<?php echo site_url($post->post_type . '/' . $post->post_name); ?>"><?php the_title(); ?></a></h2>
						<h5 class="bands-hometown-container"><a class="bands-hometown" href="<?php echo site_url($post->post_type . '/' . $post->post_name); ?>"><?php echo $custom['wpcf-hometown'][0]; ?></a></h5>
					</div>
				</div>
			<?php
			endforeach; 
			wp_reset_postdata();
			?>
		</div>
	</div>

<?php
get_footer();
?>