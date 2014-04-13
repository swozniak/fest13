<?php
/**
 * Bundled Product Short Description
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! $bundled_product->post->post_excerpt && $override_description == 'yes' && $custom_description === '' ) return;
?>
<div class="bundled_product_excerpt product_excerpt">
	<?php
		if ( $override_description == 'yes' )
			$description = $custom_description;
		else
			$description = $bundled_product->post->post_excerpt;

		echo __( $description );
	?>
</div>
