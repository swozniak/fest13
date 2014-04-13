<?php

	add_filter( 'woocommerce_cart_get_taxes', 'add_fee' );
	function add_fee( $taxes ) {
		if( $taxes[11] ) {
			$taxes[11] = $taxes[11] + 2.5;
		}

		 return $taxes;
	}

	add_filter( 'woocommerce_calculated_total', 'add_fee_to_total' );
	function add_fee_to_total( $total ) {
		$total = $total + 2.5;
		return $total;
	}


?>
