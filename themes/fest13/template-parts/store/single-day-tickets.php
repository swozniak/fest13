<?php
	$args = array ( 
		'post_status' => 'publish',
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
<?php if( $products->post_count ) : ?> 
	<section class="single-day-tickets">
		<div class="row">
			<h2><?php echo $product_name; ?></h2>
			<?php while( $products->have_posts() ) : $products->the_post(); ?>
				<div class="col-xs-12 col-md-12">
					<div class="single-day-ticket">
						<?php //publish_product( get_the_ID() ); ?>
						<?php $id = get_the_ID(); ?>
						<div class="description">
							<div class="ticket-thumbnail">
								<?php echo get_the_post_thumbnail( $id, array( 300, 300 ) ); ?>
							</div><!-- end .ticket-thumbnail -->
							<div class="content">
								<?php echo get_the_content(); ?>
							</div><!-- end .content -->
						</div><!-- end .description -->
						<?php echo do_shortcode("[product id=$id]"); ?>
					</div><!-- end .single-day-ticket -->
				</div>
			<?php endwhile; ?>
		</div>
	</section>
<?php endif; ?>
