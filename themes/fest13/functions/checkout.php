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


	/**
	 * Update the order meta with field value
	 */
	add_action( 'woocommerce_checkout_update_order_meta', 'pickup_names_checkout_field_update_order_meta' );
	 
	function pickup_names_checkout_field_update_order_meta( $order_id ) {
		if ( ! empty( $_POST['pickup_names'] ) ) {
			update_post_meta( $order_id, 'Pickup Names', sanitize_text_field( $_POST['pickup_names'] ) );
		}
	}	

	/**
	 * Display field value on the order edit page
	 */
	add_action( 'woocommerce_admin_order_data_after_billing_address', 'pickup_names_checkout_field_display_admin_order_meta', 10, 1 );
	 
	function pickup_names_checkout_field_display_admin_order_meta($order){
		echo '<p><strong>'.__('Pickup Names').':</strong> ' . get_post_meta( $order->id, 'Pickup Names', true ) . '</p>';
	}


	// Our hooked in function - $fields is passed via the filter!
	function custom_override_checkout_fields( $fields ) {
		 $fields['order']['order_comments']['placeholder'] = 'Please leave any special instructions we may need on registration day.';
		 return $fields;
	}
?>
