<?php
	$args = array ( 
		'tax_query' => array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'slug',
				'terms' => array( $product_slug ),
			)
		)
	);

	$products = get_products( $args );
?>
 
<div class="row">
	<h2><?php echo $product_name; ?></h2>
	<?php while( $products->have_posts() ) : $products->the_post(); ?>
		<div class="col-xs-12 col-md-4">
			<?php //publish_product( get_the_ID() ); ?>
			<?php $id = get_the_ID(); ?>
			<?php echo do_shortcode("[product id=$id]"); ?>
		</div>
	<?php endwhile; ?>
</div>

<?php 
	if( $product_slug == 'ticket' ) :
		$product_slug = 'single-day-ticket';
		$product_name = 'Single Day Tickets';
		include( locate_template( 'template-parts/store/single-day-tickets.php' ) );
	endif; 
?>
