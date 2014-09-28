<?php
/* LOAD WORDPRESS
 * ******************************************************************************/
$parse_uri = explode( 'wp-content', __FILE__ );
$public_html = $parse_uri[0];
include_once( $public_html . 'wp-load.php' );
$stdout = fopen('php://stdout', 'w');

$query = array(
	'post_type' => 'shop_order',
	'posts_per_page' => -1,
	'tax_query' => array(
		array(
			'taxonomy' => 'shop_order_status',
			'field' => 'slug',
			'terms' => array('processing', 'completed' )
		),
	),
);
$orders = new WP_Query( $query );
 while( $orders->have_posts() ) : $orders->the_post(); 
	 $order = new WC_Order( get_the_ID() );
endwhile;

fclose( $stdout );


?>
