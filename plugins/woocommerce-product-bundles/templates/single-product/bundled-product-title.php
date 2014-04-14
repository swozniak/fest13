<?php
/**
 * Bundled Product Title
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<h2 class="bundled_product_title product_title">
	<?php
		if ( $override_title == 'yes' )
			$title = $custom_title;
		else
			$title = $bundled_product->post->post_title;

		echo __( $title ) . ( ( $quantity > 1 ) ? ' &times; '. $quantity : '' );
	?>
</h2>
