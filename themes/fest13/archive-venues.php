<?php
/**
 * Template Name: Fest 13 - Coming Soon
 *
 */

$prefest = strpos( $_SERVER['REQUEST_URI'], 'prefest' );
if ($prefest) {
	get_header( 'prefest' );
} else {
	get_header();
}

$gainesville_venues = [];
$tampa_venues = [];

$args = array( 
		'posts_per_page' => 666,
		'post_type' => 'venues',
		'order'=> 'ASC',
		'orderby' => 'title',
		'post_status' => 'publish' 
	);
$postslist = get_posts( $args );
usort( $postslist, 'no_articles_allowed' );

foreach ( $postslist as $post ) :
	setup_postdata( $post );
	$custom = get_post_custom();
	$venue = (object) array_merge( (array) $post, (array) $custom );
	
	switch ($venue->{'wpcf-city'}[0]) {
	case 'Gainesville':
		array_push( $gainesville_venues, $venue );
		break;
	case 'Tampa':
		array_push( $tampa_venues, $venue );
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

function display_venues( $venues ) {
	foreach ( $venues as $venue ) : ?>
		<div class="venue col-xs-12 col-sm-6 col-md-4" data-name="<?php echo $venue->post_title; ?>">
			<div class="venues-image-container">
				<a href="<?php echo '/' . $venue->post_type . '/' . $venue->post_name; ?>">
					<?php 
						$photo_urls = wp_get_attachment_image_src( get_attachment_id_from_src( $venue->{'wpcf-venue-photo'}[0] ), 'thumbnail' );
						$photo_url = $photo_urls[0];
					?>
					<img class="venues-image" src="<?php echo $photo_url; ?>" />
				</a>
			</div>
			<div>
				<h2 class="venues-name-container"><a class="venues-name" href="<?php echo site_url( $venue->post_type . '/' . $venue->post_name ); ?>"><?php echo $venue->post_title; ?></a></h2>
				<h5 class="venues-hometown-container"><a class="venues-hometown" href="<?php echo site_url( $venue->post_type . '/' . $venue->post_name ); ?>"><?php echo $venue->{'wpcf-address'}[0]; ?></a></h5>
			</div>
		</div>
<?php 
	endforeach; 
}
?>

<div class="col-xs-12" id="content-column">
	<div class="page-header">	
		<h1>Venues</h1>
	</div>

	<div class="row" id="venues-container"> 
	<?php 
	$prefest = strpos( $_SERVER['REQUEST_URI'], 'prefest' );

	if ($prefest) : ?>
		<?php display_venues( $tampa_venues ); ?>
	</div>
	<a href="/venues">Check out the FEST 13 venues!</a>
	<?php else : ?>
		<?php display_venues( $gainesville_venues ); ?>
	</div>
	<a href="/prefest/venues">Check out the PRE-FEST 2 venues!</a>
	<?php endif; ?>
</div>

<?php
get_footer();
?>
