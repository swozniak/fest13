<?php

	//CHECK TO SEE IF WE SHOULD SHOW OR HIDE THE STORE
	function check_store_visibility() {
		$store_options_id = get_store_options_id();

		if( get_field( 'hide-store-from-everyone', $store_options_id ) ) :
			hide_store();
		elseif ( get_field( 'hide-store-from-users', $store_options_id ) ) :
			if( !current_user_can( 'manage_options' ) ) :
				hide_store();
			endif;
		endif;
		return true;
	}

	//HIDE THE STORE AND EXIT
	function hide_store() {
		include( locate_template( 'template-parts/store/hide-store.php' ) );
		exit();
	}

	//GET POST ID OF THE STORE OPTIONS PAGE
	function get_store_options_id() {
		$post = get_page_by_path( 'store-options' );
		return $post->ID;
	}
?>
