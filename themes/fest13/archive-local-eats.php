<?php
/**
 * Template Name: Fest 13 - Local Eats (Archive)
 *
 */

$prefest = strpos( $_SERVER['REQUEST_URI'], 'prefest' );
if ($prefest) {
	get_header( 'prefest' );
} else {
	get_header();
}

$gainesville_local_eats = [];
$tampa_local_eats = [];

$args = array( 
		'posts_per_page' => 666,
		'post_type' => 'local-eats',
		'order'=> 'ASC',
		'orderby' => 'title',
		'post_status' => 'publish' 
	);
$postslist = get_posts( $args );
usort( $postslist, 'no_articles_allowed' );

foreach ( $postslist as $post ) :
	setup_postdata( $post );
	$custom = get_post_custom();
	$local_eat = (object) array_merge( (array) $post, (array) $custom );
	
	switch ($local_eat->{'wpcf-restaurant-city'}[0]) {
	case 'Gainesville':
		array_push( $gainesville_local_eats, $local_eat );
		break;
	case 'Tampa':
		array_push( $tampa_local_eats, $local_eat );
		break;
	}
endforeach; 
wp_reset_postdata();

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

function display_local_eats( $local_eats ) {
	foreach ( $local_eats as $local_eat ) : ?>
		<div class="local_eat col-xs-12 col-sm-6 col-md-4" data-name="<?php echo $local_eat->post_title; ?>">
			<div class="local_eats-image-container">
				<a href="<?php echo '/' . $local_eat->post_type . '/' . $local_eat->post_name; ?>">
					<?php 
						$photo_urls = wp_get_attachment_image_src( get_attachment_id_from_src( $local_eat->{'wpcf-restaurant-photo'}[0] ), 'thumbnail' );
						$photo_url = $photo_urls[0];
					?>
					<img class="local_eats-image" src="<?php echo $photo_url; ?>" />
				</a>
			</div>
			<div>
				<h2 class="local_eats-name-container"><a class="local_eats-name" href="<?php echo site_url( $local_eat->post_type . '/' . $local_eat->post_name ); ?>"><?php echo $local_eat->post_title; ?></a></h2>
				<h5 class="local_eats-hometown-container"><a class="local_eats-hometown" href="<?php echo site_url( $local_eat->post_type . '/' . $local_eat->post_name ); ?>"><?php echo $local_eat->{'wpcf-restaurant-address'}[0]; ?></a></h5>
			</div>
		</div>
<?php 
	endforeach; 
}
?>

<div class="col-xs-12" id="content-column">
	<div class="page-header">	
		<h1>Local Eats</h1>
	</div>

	<div class="row" id="local-eats-container"> 
	<?php 
	$prefest = strpos( $_SERVER['REQUEST_URI'], 'prefest' );

	if ($prefest) : ?>
		<?php display_local_eats( $tampa_local_eats ); ?>
	</div>
	<a href="/local-eats/">Check out the FEST 13 LOCAL EATS!</a>
	<?php else : ?>
		<?php display_local_eats( $gainesville_local_eats ); ?>
	</div>
	<a href="/prefest/local-eats/">Check out the PRE-FEST 2 LOCAL EATS!</a>
	<?php endif; ?>
</div>

<?php
get_footer();
?>
