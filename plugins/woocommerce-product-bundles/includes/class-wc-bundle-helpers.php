<?php
/**
 * Product Bundle Helper Functions
 *
 * @class 	WC_Bundle_Helpers
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class WC_Bundle_Helpers {

	var $is_wc_21;

	function __construct() {

		global $woocommerce;

		if ( version_compare( $woocommerce->version, '2.0.22' ) > 0 )
			$this->is_wc_21 = true;
		else
			$this->is_wc_21 = false;
	}

	function is_wc_21() {

		return $this->is_wc_21;
	}

	/**
	 * Bundled product availability
	 */
	function get_bundled_product_availability( $product, $quantity ) {

		$availability = $class = "";

		if ( $product->managing_stock() ) {
			if ( $product->is_in_stock() && $product->get_total_stock() >= $quantity ) {

				if ( $product->get_total_stock() > get_option( 'woocommerce_notify_no_stock_amount' ) ) {

					$format_option = get_option( 'woocommerce_stock_format' );

					switch ( $format_option ) {
						case 'no_amount' :
							$format = __( 'In stock', 'woocommerce' );
						break;
						case 'low_amount' :
							$low_amount = get_option( 'woocommerce_notify_low_stock_amount' );

							$format = ( $product->get_total_stock() <= $low_amount ) ? __( 'Only %s left in stock', 'woocommerce' ) : __( 'In stock', 'woocommerce' );
						break;
						default :
							$format = __( '%s in stock', 'woocommerce' );
						break;
					}

					$availability = sprintf( $format, $product->stock );

					if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
						$availability .= ' ' . __( '(backorders allowed)', 'woocommerce' );
					}

				} else {

					if ( $product->backorders_allowed() ) {
						if ( $product->backorders_require_notification() ) {
							$availability = __( 'Available on backorder', 'woocommerce' );
							$class        = 'available-on-backorder';
						} else {
							$availability = __( 'In stock', 'woocommerce' );
						}
					} else {
						$availability = __( 'Out of stock', 'woocommerce' );
						$class        = 'out-of-stock';
					}

				}

			} elseif ( $product->backorders_allowed() ) {
				$availability = __( 'Available on backorder', 'woocommerce' );
				$class        = 'available-on-backorder';
			} else {
				$availability = __( 'Out of stock', 'woocommerce' );
				$class        = 'out-of-stock';
			}
		} elseif ( ! $product->is_in_stock() ) {
			$availability = __( 'Out of stock', 'woocommerce' );
			$class        = 'out-of-stock';
		}

		return apply_filters( 'woocommerce_get_availability', array( 'availability' => $availability, 'class' => $class ), $product );
	}


	function get_bundled_product_price( $product, $price ) {

		if ( ! $this->is_wc_21() || $price == 0 )
			return $price;

		if ( get_option( 'woocommerce_tax_display_shop' ) == 'excl' )
			$product_price = $product->get_price_excluding_tax( 1, $price );
		else
			$product_price = $product->get_price_including_tax( 1, $price );

		return $product_price;
	}


	function get_bundled_regular_product_price( $product ) {

		if ( $this->is_wc_21() )
			$product_price = $product->get_regular_price();
		else
			$product_price = apply_filters( 'woocommerce_get_regular_price', $product->regular_price, $product );

		return $product_price;
	}


}

$GLOBALS[ 'woocommerce_bundle_helpers' ] = new WC_Bundle_Helpers();
