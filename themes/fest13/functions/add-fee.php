<?php

	add_filter( 'woocommerce_calc_tax', 'add_fee_to_tax' );
	function add_fee_to_tax( $taxes ) {
		$tax_keys = array( 11, 12, 31, 32, 33, 34, 35, 36 );

		 foreach( $tax_keys as $tax_key ) :
			if( $taxes[$tax_key] ) :
				$taxes[$tax_key] = $taxes[$tax_key] + 2.5;
			endif;
		 endforeach;

		 return $taxes;
	}
?>
