<?php
function get_missed_orders( $orders ) {
	$stop_time = mktime( 0, 20, 00, 4, 22, 2014 ); 
	$hotels = array( 893, 892, 903, 900, 897, 896, 895, 894 );
	$missed = 0;

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

	return $missed;
}
?>
