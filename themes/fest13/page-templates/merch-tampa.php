<?php
/**
 * Template Name: Merch Tampa
 *
 */

if( current_user_can( 'edit_shop_orders' ) ) :
	$counts = array();
	$merch = array( 'shirt', 'hoodie', 'accessory' );
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
	while( $orders->have_posts() ) : $orders->the_post(); 
		$order = new WC_Order( get_the_ID() ); 
		foreach( $order->get_items() as $item ) : 
			if( has_term( $merch, 'product_cat', get_post( $item['product_id'] ) ) ) :	
				if( has_tampa( $order->get_items() ) ) :	
					if( isset( $item['variation_id'] ) && !empty( $item['variation_id'] ) ) :
						if( isset( $item['pa_room-type'] ) && !empty( $item['pa_room-type'] ) ) :
							$counts[$item['name']]['variations'][$item['pa_room-type']] += $item['qty'];
						elseif( isset( $item['pa_size'] ) && !empty( $item['pa_size'] ) ) :
							$counts[$item['name']]['variations'][$item['pa_size']] += $item['qty'];
						elseif( isset( $item['pa_color'] ) && !empty( $item['pa_color'] ) ) :
							$counts[$item['name']]['variations'][$item['pa_color']] += $item['qty'];
						elseif( isset( $item['pa_case-design'] ) && !empty( $item['pa_case-design'] ) ) :
							$counts[$item['name']]['variations'][$item['pa_case-design']] += $item['qty'];
						endif;
					else :
						$counts[$item['name']]['qty'] += $item['qty'];
					endif;
				endif;
			endif;
		endforeach;
	endwhile;
endif;
?>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>FEST 13 MERCH COUNTS TAMPA</title>
	</head>
	<body class="names">
		<h1>FEST 13 MERCH COUNTS TAMPA</h1>
		<table border="1" cellspacing="0" cellpadding="4px">
			<thead align="left">
				<th>Counts</th>
				<th>Product</th>
			</thead>
			<?php foreach( $counts as $name => $count ) : ?>
				<?php if( isset( $count['variations'] ) ) : ?>
					<tr>
						<td style="background-color: #CCC;"></td>
						<td style="background-color: #CCC;"><strong><?php echo $name; ?></strong></td>
					</tr>
					<?php foreach( $count['variations'] as $var_name => $var_qty ) : ?>
						<tr>
							<td align="center"><?php echo number_format( $var_qty ); ?></td>
							<td><?php echo $var_name; ?></td>
						</tr>
					<?php endforeach; ?>

				<?php else: ?>
					<tr>
						<td style="background-color: #CCC;"align="center"><?php echo number_format( $count['qty'] ); ?></td>
						<td style="background-color: #CCC;"><strong><?php echo $name; ?></strong></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>	
		</table>
	</body>
</html>

<?php function has_tampa( $order_items ) {
	$has_tampa = false;
	foreach( $order_items as $item ) :
		if( has_term( 'pre-fest', 'product_cat', get_post( $item['product_id'] ) ) ) :	
			$has_tampa = true;
		endif;
	endforeach;

	return $has_tampa;
}
?>
