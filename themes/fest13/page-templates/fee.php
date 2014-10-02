<?php
/**
 * Template Name: Fee
 *
 */
?>

<?php
	//Only for peej
	if( get_current_user_id() == 3 ) :	
		$fee = 2.5;
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
		$order_count = $orders->found_posts;
		$missed = get_missed_orders( $orders );
		$total_fee = '$' . number_format( ( ( $order_count - $missed ) * $fee ), 2 ); 
	?>

		TOTAL ORDERS: <?php echo number_format( $order_count ); ?>
		<br>
		ORDERS MISSED: <?php echo number_format( $missed ); ?> (orders that contained hotels placed before 4/22/2014 12:20am UTC)
		<br>
		<br>
		ORDERS WITH FEE: <?php echo number_format( $order_count - $missed ); ?>
		<br>
		FEE: <strong><?php echo $total_fee; ?></strong>
	<?php endif; ?>
