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
		<div class="col-xs-12 col-md-12">
			<?php //publish_product( get_the_ID() ); ?>
			<?php $id = get_the_ID(); ?>
			<div class="description">
				<div class="ticket-thumbnail">
					<?php echo get_the_post_thumbnail( $id, array( 200, 200 ) ); ?>
				</div><!-- end .ticket-thumbnail -->
				<div class="content">
					<?php echo get_the_content(); ?>
				</div><!-- end .content -->
			</div><!-- end .description -->
			<?php echo do_shortcode("[product id=$id]"); ?>
			
		</div>
	<?php endwhile; ?>
</div>
