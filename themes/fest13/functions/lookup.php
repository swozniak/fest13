<?php
	add_action( 'wp_ajax_nopriv_lookup_orders', 'lookup_orders' );
	add_action( 'wp_ajax_lookup_orders', 'lookup_orders' );
	function lookup_orders() {
		$lookup_query = $_POST['lookup_query'];
		$orders = '';
		// Do output buffering here
		echo json_encode( $orders );
		exit;
	}

	function get_orders( $lookup_query ) {
		
	}
?>
