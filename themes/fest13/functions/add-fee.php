<?php

	add_filter( 'woocommerce_calc_tax', 'add_fee_to_tax' );
	function add_fee_to_tax( $taxes ) {
		 //USA
		if( $taxes[11] ) {
			$taxes[11] = $taxes[11] + 2.5;
		}

		//International
		if( $taxes[12] ) {
			$taxes[12] = $taxes[12] + 2.5;
		}

		 return $taxes;
	}
?>
