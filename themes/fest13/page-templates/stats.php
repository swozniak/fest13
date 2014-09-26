<?php
/**
 * Template Name: Fest 13 - Stats
 *
 */
?>
<?php 
	$stats = array();
	$countries = new WC_Countries;
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
		 $stats['countries'][$order->billing_country]['orders']++;
		 foreach( $order->get_items() as $item ) :
			$stats['countries'][$order->billing_country][$item['name']] += $item['item_meta']['_qty'][0];
		 endforeach;	 
	endwhile;
?>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>FEST 13 Stats</title>
	</head>
	<body class="names">
		<h1>FEST 13 STATS</h1>
		<h3>COUNTRIES</h3>
<?php //echo '<pre>' . print_r($stats, true) . '</pre>'; ?>

		<table border="1" cellspacing="0" cellpadding="3px">
			<thead align="left">
				<th>Country</th>
				<th>Orders</th>
				<th>Fest 13 Tickets</th>
				<th>Pre Fest Tickets</th>
			</thead>
			<?php foreach( $stats['countries'] as $abbr => $country_items ) : ?>
				<tr>
					<td><?php echo $countries->countries[$abbr]; ?></td>
					<td><?php echo number_format( $country_items['orders'] ); ?></td>
					<td><?php echo number_format( $country_items['FEST 13 Ticket - 3 day pass'] ); ?></td>
					<td><?php echo number_format( $country_items['Big Pre-Fest in Little Ybor Ticket - 2 day pass'] ); ?></td>
				</tr>
			<?php endforeach; ?>	
		</table>
	</body>
</html>
