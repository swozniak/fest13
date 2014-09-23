<?php
/**
 * Template Name: Fest 13 - Names
 *
 */
?>
<?php 
if( current_user_can( 'edit_shop_orders' ) ) :
	$names = array();
	$count = 0;
	$product_id = $_GET['product_id'];
	$product = new WC_Product( $product_id );
	$product_title = $product->post->post_title;
		
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
		'meta_key' => '_billing_last_name',
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'meta_query' => array(
			array(
				'key' => '_billing_last_name',
			),
		),
	);
	$orders = new WP_Query( $query );
	 while( $orders->have_posts() ) : $orders->the_post(); 
		 $order = new WC_Order( get_the_ID() ); 
		 foreach( $order->get_items() as $item ) : 
			 if( $item['product_id'] == $product_id ) :
				$order_meta = get_post_meta( $order->id );
				$names[$order->id] = array(
					'last' => $order_meta['_billing_last_name'][0],
					'first' => $order_meta['_billing_first_name'][0],
					'pickup_names' => $order_meta['Pickup Names'][0],
					'email' => $order_meta['_billing_email'][0],
					'qty' => $item['qty'],
				);
				$count += $item['qty'];
			endif;
		 endforeach; 
	 endwhile; 
?>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title><?php echo $product_title; ?></title>
	</head>
	<body class="names">
		<h1><?php echo "$product_title ($count)"; ?></h1>
		<table border="1" cellspacing="0" cellpadding="3px">
			<thead align="left">
				<th>Tickets</th>
				<th>Last Name</th>
				<th>First Name</th>
				<th>Pickup Names</th>
				<th>Email</th>
			</thead>
			<?php foreach( $names as $name ) : ?>
				<tr>
					<td align="center"><?php echo $name['qty']; ?></td>
					<td><?php echo $name['last']; ?></td>
					<td><?php echo $name['first']; ?></td>
					<td><?php echo $name['pickup_names']; ?></td>
					<td><?php echo $name['email']; ?></td>
				</tr>
			<?php endforeach; ?>	
		</table>
	</body>
</html>

<?php endif; ?>
