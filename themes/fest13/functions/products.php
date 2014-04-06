<?php
	function get_products( $args = array() ) {
		$defaults = array(
			'post_type' => 'product',
			'post_status' => 'publish, draft',
			'posts_per_page' => -1,
			'order' => 'ASC',
			//'tax_query' => array(
				//array(
					//'taxonomy' => 'product_cat',
					//'field' => 'slug',
					//'terms' => array( 'ticket' ),
					//'operator' => 'AND',
				//)
			//)
		);	

		$args = wp_parse_args( $args, $defaults );
		$products = new WP_QUERY( $args );

		return $products;
	}
?>
