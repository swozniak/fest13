<?php
	/**
	 * Add the field to the checkout
	 */
	add_action( 'woocommerce_after_order_notes', 'pickup_names_checkout_field' );
	 
	function pickup_names_checkout_field( $checkout ) {
	 
		echo '<div id="pickup_names_checkout_field"><h3><span style="color: red;">*</span> Who needs to pick up the ticket(s)? <span style="color: red;">*</span></h3>';
	 
		woocommerce_form_field( 'pickup_names', array(
			'type'          => 'text',
			'class'         => array('pickup-names form-row-wide'),
			'label'         => __('Let us know the names of everyone allowed to pickup tickets/merch for this order.'),
			'placeholder'   => __('Enter names for ticket pickup'),
			), $checkout->get_value( 'pickup_names' ));
	 
		echo '</div>';
	 
	}

	/**
	 * Process the checkout
	 */
	add_action('woocommerce_checkout_process', 'pickup_names_checkout_field_process');
	 
	function pickup_names_checkout_field_process() {
		// Check if set, if its not set add an error.
		if ( ! $_POST['pickup_names'] )
			wc_add_notice( __( 'Please enter names for ticket pickup.' ), 'error' );
	}

	add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

	// Our hooked in function - $fields is passed via the filter!
	function custom_override_checkout_fields( $fields ) {
		 $fields['order']['order_comments']['placeholder'] = 'Please leave any special instructions we may need on registration day.';
		 return $fields;
	}
?>
