<?php

	add_filter( 'woocommerce_calc_tax', 'add_fee_to_tax' );
	function add_fee_to_tax( $taxes ) {
		if( $taxes[11] ) {
			$taxes[11] = $taxes[11] + 2.5;
		}

		 return $taxes;
	}
?>
