<?php
	//SCRIPT TO SEND OUT EMAILS TO ALL PROCESSING ORDERS WHO DID NOT ORDER FROM THE HOLIDAY INN
    //Load Wordpress
	$parse_uri = explode( 'wp-content', __FILE__ );
	$public_html = $parse_uri[0];
	require_once( $public_html . 'wp-load.php' );
	global $woocommerce;
	$hi_ids = array( 892, 894, 893 );
	$order_count = wp_count_posts( 'shop_order' );
	$orders_at_a_time = 50;
	$sent = 0;

	for( $offset = 0; $offset <= $order_count->publish; $offset += $orders_at_a_time ) :
		$args = array(
			'post_type' => 'shop_order',
			'posts_per_page' => $orders_at_a_time,
			'offset' => $offset,
		);

		$query = new WP_Query( $args );

		while( $query->have_posts() ) :  $query->the_post();
			$order_id = get_the_ID();
			$order = new WC_Order();
			$order->get_order( $order_id );
			$items = $order->get_items();
			$send_email = true;

			if( $order->status != 'processing' ) :
				$send_email = false;
			endif;

			foreach( $items as $item ) :
				if( in_array( $item['product_id'], $hi_ids ) ) :
					$send_email = false;
				endif;
			endforeach;

			if( $send_email ) :
				 $sent++;
				echo get_the_ID() . "\n\n";
				//$mailer = $woocommerce->mailer();
				//$email = $mailer->emails['WC_Email_Customer_Processing_Order'];
				//$email->trigger( $order_id );
			endif;
		endwhile;
		sleep( 5 );
	endfor;


?>
