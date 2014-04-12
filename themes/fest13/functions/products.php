<?php
	function get_products( $args = array() ) {
		$defaults = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'order' => 'ASC',
		);	

		$args = wp_parse_args( $args, $defaults );
		$products = new WP_QUERY( $args );

		return $products;
	}

	function publish_product( $id ) {
		wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		return true;
	}
?>
