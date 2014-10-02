<?php
/* LOAD WORDPRESS
 * ******************************************************************************/
$parse_uri = explode( 'wp-content', __FILE__ );
$public_html = $parse_uri[0];
include_once( $public_html . 'wp-load.php' );
$stdout = fopen('php://stdout', 'w');
$stop_time = mktime( 0, 20, 00, 4, 22, 2014 ); 
$hotels = array( 893, 892, 903, 900, 897, 896, 895, 894 );
$missed = 0;

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
	'order' => 'ASC',
);
$orders = new WP_Query( $query );
 while( $orders->have_posts() ) : $orders->the_post(); 
	 $order = new WC_Order( get_the_ID() );
	 $has_hotel = false;
	 $date = strtotime( $order->order_date );
	 if( $date < $stop_time ) :
		$items = $order->get_items();	
		foreach( $items as $item ) :
			if( in_array( $item['product_id'], $hotels ) ) :
				$has_hotel = true;				
			endif;
		endforeach;	
	 endif;
	 if( $has_hotel ) :
		 $missed++;
	 endif;
endwhile;
$amount = money_format( '%i', ( $missed * 2.5 ) );
fwrite( $stdout, "\nMissed $missed orders for a total of \$$amount\n\n" );
fclose( $stdout );


?>
