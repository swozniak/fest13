<?php
/**
 * Product Bundle Class
 *
 * @class 	WC_Product_Bundle
 * @version 4.1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class WC_Product_Bundle extends WC_Product {

	var $bundle_data;

	var $bundled_products;

	var $min_price;
	var $max_price;

	var $min_bundle_price;
	var $max_bundle_price;
	var $min_bundle_regular_price;
	var $max_bundle_regular_price;

	var $min_bundle_price_excl_tax;
	var $min_bundle_price_incl_tax;

	var $bundle_attributes;
	var $available_bundle_variations;
	var $selected_bundle_attributes;

	var $has_filters;
	var $filtered_variation_attributes = array();

	var $bundled_item_quantities = array();
	var $bundled_item_discounts = array();

	var $per_product_pricing_active;
	var $per_product_shipping_active;

	var $all_items_sold_individually;
	var $all_items_in_stock;
	var $has_items_on_backorder;

	var $on_sale;

	var $bundle_price_data;

	var $bundle_availability_data = array();

	var $contains_nyp;

	var $processing_item_id;

	var $item_id_for_price_filter;

	var $enable_bundle_transients;

	var $microdata_display = false;

	function __construct( $bundle ) {

		global $woocommerce_bundles, $woocommerce_bundle_helpers;

		$this->product_type = 'bundle';

		parent::__construct( $bundle );

		$this->bundle_data = maybe_unserialize( get_post_meta( $this->id, '_bundle_data', true ) );

		$bundled_item_ids = maybe_unserialize( get_post_meta( $this->id, '_bundled_ids', true ) );

		if ( empty( $this->bundle_data ) && ! empty( $bundled_item_ids ) )
			$this->bundle_data = $woocommerce_bundles->serialize_bundle_meta( $this->id );

		$this->has_filters 	= false;

		$this->contains_nyp = false;
		$this->on_sale 		= false;

		$this->all_items_sold_individually 	= true;
		$this->all_items_in_stock			= true;
		$this->has_items_on_backorder		= false;

		if ( ! empty( $this->bundle_data ) ) {
			foreach( $this->bundle_data as $bundled_item_id => $bundled_item_data ) {

				// Store 'variation filtering' boolean variables
				if ( isset( $bundled_item_data[ 'filter_variations' ] ) && $bundled_item_data[ 'filter_variations' ] == 'yes' )
					$this->has_filters = true;

				// Store bundled item quantities
				$this->bundled_item_quantities[ $bundled_item_id ] = ( int ) $bundled_item_data[ 'bundle_quantity' ];

				// Store bundled item discounts
				$this->bundled_item_discounts[ $bundled_item_id ] = $bundled_item_data[ 'bundle_discount' ];
			}
		}

		if ( $this->has_filters ) {

			// create array of attributes based on active variations

			foreach ( $this->bundle_data as $item_id => $bundled_item_data ) {

				if ( ! isset( $bundled_item_data[ 'filter_variations' ] ) || $bundled_item_data[ 'filter_variations' ] == 'no' )
					continue;

				$allowed_variations = $bundled_item_data[ 'allowed_variations' ];

				$product_id = $bundled_item_data[ 'product_id' ];

				$attributes = ( array ) maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );

				// filtered variation attributes (stores attributes of active variations)
				$filtered_attributes = array();

				$this->filtered_variation_attributes[ $item_id ] = array();

				// make array of active variation attributes
				foreach ( $allowed_variations as $allowed_variation_id ) {

					$description = '';

					$variation_data = get_post_meta( $allowed_variation_id );

					foreach ( $attributes as $attribute ) {

						// Only deal with attributes that are variations
						if ( ! $attribute[ 'is_variation' ] )
							continue;

						// Get current value for variation (if set)
						$variation_selected_value = isset( $variation_data[ 'attribute_' . sanitize_title( $attribute[ 'name' ] ) ][0] ) ? $variation_data[ 'attribute_' . sanitize_title( $attribute[ 'name' ] ) ][0] : '';

						// Get terms for attribute taxonomy or value if its a custom attribute
						if ( $attribute[ 'is_taxonomy' ] ) {

							$post_terms = wp_get_post_terms( $product_id, $attribute[ 'name' ] );

							foreach ( $post_terms as $term ) {

								if ( $variation_selected_value == $term->slug || $variation_selected_value == '' ) {

									if ( $variation_selected_value == '' )
										$description = 'Any';
									else
										$description = $term->name;

									if ( ! isset( $filtered_attributes[ $attribute[ 'name' ] ] ) ) {

										$filtered_attributes[ $attribute[ 'name' ] ][ 'descriptions' ][] 	= $description;
										$filtered_attributes[ $attribute[ 'name'] ]['slugs' ][] 			= sanitize_title( $description );

									} elseif ( ! in_array( $description, $filtered_attributes[ $attribute[ 'name' ] ][ 'descriptions' ] ) ) {

										$filtered_attributes[ $attribute[ 'name' ] ][ 'descriptions' ][] 	= $description;
										$filtered_attributes[ $attribute[ 'name' ] ][ 'slugs' ][] 			= sanitize_title( $description );
									}
								}

							}

						} else {

							$options = array_map( 'trim', explode( '|', $attribute[ 'value' ] ) );

							foreach ( $options as $option ) {

								if ( sanitize_title( $variation_selected_value ) == sanitize_title( $option ) || $variation_selected_value == '' ) {

									if( $variation_selected_value == '' )
										$description = 'Any';
									else
										$description = $option;

									if ( ! isset( $filtered_attributes[ $attribute['name'] ] ) ) {

										$filtered_attributes[ $attribute[ 'name' ] ][ 'descriptions' ][] 	= $description;
										$filtered_attributes[ $attribute[ 'name' ] ][ 'slugs' ][] 			= sanitize_title( $description );

									} elseif ( ! in_array( $description, $filtered_attributes[ $attribute[ 'name' ] ][ 'descriptions' ] ) ) {

										$filtered_attributes[ $attribute[ 'name' ] ][ 'descriptions' ][] 	= $description;
										$filtered_attributes[ $attribute[ 'name' ] ][ 'slugs' ][] 			= sanitize_title( $description );
									}
								}

							}

						}

					}


					// clean up product attributes
			        foreach ( $attributes as $attribute ) {

			            if ( ! $attribute[ 'is_variation' ] )
			            	continue;

						if ( array_key_exists( $attribute[ 'name' ], $filtered_attributes ) && !in_array( 'Any', $filtered_attributes[ $attribute[ 'name' ] ][ 'descriptions' ] ) )
							$this->filtered_variation_attributes[ $item_id ][ $attribute[ 'name' ] ] = $filtered_attributes[ $attribute[ 'name' ] ];
					}

				}

			}

		}

		$this->per_product_pricing_active 	= ( get_post_meta( $this->id, '_per_product_pricing_active', true ) == 'yes' ) ? true : false;
		$this->per_product_shipping_active 	= ( get_post_meta( $this->id, '_per_product_shipping_active', true ) == 'yes' ) ? true : false;

		$this->enable_bundle_transients = get_post_meta( $this->id, 'enable_bundle_transients', true ) == 'yes' ? true : false;

		$this->min_price = get_post_meta( $this->id, '_min_bundle_price', true );
		$this->max_price = get_post_meta( $this->id, '_max_bundle_price', true );

		$product_price = get_post_meta( $this->id, '_price', true );

		if ( ! empty( $this->bundle_data ) ) {

			$this->load_bundle_data();

			if ( $this->per_product_pricing_active ) {

				$this->price = 0;

				if ( $this->contains_nyp )
					$this->max_bundle_price = 100000000.0;

			} else {

				if ( class_exists( 'WC_Name_Your_Price_Helpers' ) && WC_Name_Your_Price_Helpers::is_nyp( $this ) ) {
					$this->min_bundle_price = get_post_meta( $this->id, '_min_price', true );
					$this->max_bundle_price = 100000000.0;
				} else {
					$this->min_bundle_price = $this->max_bundle_price = $this->get_price();
				}

			}

			if ( $this->min_price != $this->min_bundle_price )
				update_post_meta( $this->id, '_min_bundle_price', $this->min_bundle_price );

			if ( $this->max_price != $this->max_bundle_price )
				update_post_meta( $this->id, '_max_bundle_price', $this->max_bundle_price );

			if ( $product_price != $this->min_bundle_price )
				update_post_meta( $this->id, '_price', $this->min_bundle_price );

		}

	}

	function load_bundle_data() {

		global $woocommerce_bundles, $woocommerce_bundle_helpers;

		// stores bundle pricing strategy info and price table
		$this->bundle_price_data = array();

		$this->bundle_price_data[ 'currency_symbol' ] 					= get_woocommerce_currency_symbol();
		$this->bundle_price_data[ 'woocommerce_price_num_decimals' ] 	= ( int ) get_option( 'woocommerce_price_num_decimals' );
		$this->bundle_price_data[ 'woocommerce_currency_pos' ] 			= get_option( 'woocommerce_currency_pos' );
		$this->bundle_price_data[ 'woocommerce_price_decimal_sep' ] 	= stripslashes( get_option( 'woocommerce_price_decimal_sep' ) );
		$this->bundle_price_data[ 'woocommerce_price_thousand_sep' ] 	= stripslashes( get_option( 'woocommerce_price_thousand_sep' ) );
		$this->bundle_price_data[ 'woocommerce_price_trim_zeros' ] 		= false == apply_filters( 'woocommerce_price_trim_zeros', false ) ? 'no' : 'yes';

		$this->bundle_price_data[ 'total_description' ] 		= __( 'Total', 'woo-bundles' ) . ': ';

		$this->bundle_price_data[ 'partially_out_of_stock' ] 	= __( 'Out of stock', 'woo-bundles' );
		$this->bundle_price_data[ 'partially_on_backorder' ] 	= __( 'Available on backorder', 'woo-bundles' );

		$this->bundle_price_data[ 'free' ] = __( 'Free!', 'woocommerce' );

		$this->bundle_price_data[ 'per_product_pricing' ] = $this->per_product_pricing_active;
		$this->bundle_price_data[ 'prices' ] = array();
		$this->bundle_price_data[ 'regular_prices' ] = array();
		$this->bundle_price_data[ 'total' ] = $this->get_price() == '' ? -1 : $woocommerce_bundle_helpers->get_bundled_product_price( $this, $this->get_price() );
		$this->bundle_price_data[ 'regular_total' ] = $woocommerce_bundle_helpers->get_bundled_product_price( $this, $this->get_regular_price() );

		$this->bundle_attributes = array();
		$this->available_bundle_variations = array();
		$this->selected_bundle_attributes = array();
		$this->bundled_products = array();

		$this->min_bundle_price = '';
		$this->max_bundle_price = '';
		$this->min_bundle_regular_price = '';
		$this->max_bundle_regular_price = '';

		$this->min_bundle_price_excl_tax = '';
		$this->min_bundle_price_incl_tax = '';

		foreach ( $this->bundle_data as $bundled_item_id => $bundled_item_data ) {

			$this->processing_item_id = $bundled_item_id;

			// remove suffix
			$product_id = $bundled_item_data[ 'product_id' ];

			$bundled_product = get_product( $product_id );

			if ( ! $bundled_product || ! $bundled_product->is_purchasable() )
				continue;

			$this->bundled_products[ $bundled_item_id ] = $bundled_product;

			if ( $bundled_product->product_type == 'simple' ) {

				if ( ! $bundled_product->is_sold_individually() )
					$this->all_items_sold_individually = false;

				$this->bundle_availability_data[ $bundled_item_id ] = $woocommerce_bundle_helpers->get_bundled_product_availability( $bundled_product, $this->bundled_item_quantities[ $bundled_item_id ] );

				if ( $this->bundle_availability_data[ $bundled_item_id ][ 'class' ] == 'out-of-stock' ) {
					$this->all_items_in_stock = false;
				}

				if ( $this->bundle_availability_data[ $bundled_item_id ][ 'class' ] == 'available-on-backorder' ) {
					$this->has_items_on_backorder = true;
				}

				$this->add_bundled_product_get_price_filter( $bundled_item_id );

				$product_regular_price 	= $woocommerce_bundle_helpers->get_bundled_regular_product_price( $bundled_product );
				$product_price 			= $bundled_product->get_price();

				$this->remove_bundled_product_get_price_filter( $bundled_item_id );

				// Name your price support
				if ( class_exists( 'WC_Name_Your_Price_Helpers' ) && WC_Name_Your_Price_Helpers::is_nyp( $product_id ) ) {

					$product_price = $product_regular_price = WC_Name_Your_Price_Helpers::get_minimum_price( $product_id ) ? WC_Name_Your_Price_Helpers::get_minimum_price( $product_id ) : 0;

					$this->contains_nyp = true;

				}

				$product_regular_price = $woocommerce_bundle_helpers->get_bundled_product_price( $bundled_product, $product_regular_price );
				$bundled_product_price = $woocommerce_bundle_helpers->get_bundled_product_price( $bundled_product, $product_price );

				if ( $product_regular_price > $bundled_product_price )
					$this->on_sale = true;

				// price for simple products gets stored now, for variable products jquery gets the job done
				$this->bundle_price_data[ 'prices' ][ $bundled_product->id ] 			= $bundled_product_price;
				$this->bundle_price_data[ 'regular_prices' ][ $bundled_product->id ] 	= $product_regular_price;

				// no variation data to load - product is simple
				$this->min_bundle_price 		= $this->min_bundle_price + $this->bundled_item_quantities[ $bundled_item_id ] * $bundled_product_price;
				$this->min_bundle_regular_price = $this->min_bundle_regular_price + $this->bundled_item_quantities[ $bundled_item_id ] * $product_regular_price;

				$this->max_bundle_price 		= $this->max_bundle_price + $this->bundled_item_quantities[ $bundled_item_id ] * $bundled_product_price;
				$this->max_bundle_regular_price = $this->max_bundle_regular_price + $this->bundled_item_quantities[ $bundled_item_id ] * $product_regular_price;

				if ( $woocommerce_bundle_helpers->is_wc_21() ) {

					if ( get_option( 'woocommerce_tax_display_shop' ) == 'excl' ) {
						$this->min_bundle_price_excl_tax = $this->min_bundle_price_excl_tax + $this->bundled_item_quantities[ $bundled_item_id ] * $bundled_product_price;
						$this->min_bundle_price_incl_tax = $this->min_bundle_price_incl_tax + $bundled_product->get_price_including_tax( $this->bundled_item_quantities[ $bundled_item_id ], $product_price );
					} else {
						$this->min_bundle_price_incl_tax = $this->min_bundle_price_incl_tax + $this->bundled_item_quantities[ $bundled_item_id ] * $bundled_product_price;
						$this->min_bundle_price_excl_tax = $this->min_bundle_price_excl_tax + $bundled_product->get_price_excluding_tax( $this->bundled_item_quantities[ $bundled_item_id ], $product_price );
					}

				}

			}

			elseif ( $bundled_product->product_type == 'variable' ) {

				// prepare price variable for jquery

				$this->bundle_price_data[ 'prices' ][ $bundled_item_id ] 			= 0;
				$this->bundle_price_data[ 'regular_prices' ][ $bundled_item_id ] 	= 0;

				// get all available attributes and settings

				$this->bundle_attributes[ $bundled_item_id ] = $bundled_product->get_variation_attributes();

				$default_product_attributes = array();

				if ( $this->bundle_data[ $bundled_item_id ][ 'override_defaults' ] == 'yes' ) {
					$default_product_attributes = $this->bundle_data[ $bundled_item_id ][ 'bundle_defaults' ];
				} else {
					$default_product_attributes = ( array ) maybe_unserialize( get_post_meta( $product_id, '_default_attributes', true ) );
				}

				$this->selected_bundle_attributes[ $bundled_item_id ] = apply_filters( 'woocommerce_product_default_attributes', $default_product_attributes );

				// calculate min-max variation prices

				$min_variation_regular_price 	= '';
				$min_variation_price 			= '';
				$max_variation_regular_price 	= '';
				$max_variation_price 			= '';

				$min_variation_price_incl_tax 	= '';
				$min_variation_price_excl_tax 	= '';

				// filter variations array to add prices and modify price_html / stock data

				$this->add_bundled_product_get_price_filter( $bundled_item_id );
				add_filter( 'woocommerce_available_variation', array( $this, 'woo_bundles_available_variation' ), 10, 3 );

				if ( $this->enable_bundle_transients ) {
					$transient_name = 'wc_bundled_item_' . $bundled_item_id . '_' . $this->id;

					if ( false === ( $bundled_item_variations = get_transient( $transient_name ) ) ) {
						$bundled_item_variations = $bundled_product->get_available_variations();
						set_transient( $transient_name, $bundled_item_variations );
					}
				} else {
					$bundled_item_variations = $bundled_product->get_available_variations();
				}

				remove_filter( 'woocommerce_available_variation', array( $this, 'woo_bundles_available_variation' ), 10, 3 );
				$this->remove_bundled_product_get_price_filter( $bundled_item_id );

				// check stock status of variations - if all of them are out of stock, the product cannot be purchased

				$variation_in_stock_exists 		= false;
				$all_variations_on_backorder 	= true;

				// add only active variations

				foreach ( $bundled_item_variations as $variation_data ) {

					if ( ! empty( $variation_data ) )
						$this->available_bundle_variations[ $bundled_item_id ][] = $variation_data;
					else
						continue;

					// check stock status of variation - if one of them is in stock, the product can be purchased

					if ( $variation_data[ 'is_in_stock' ] )
						$variation_in_stock_exists = true;

					if ( ! $variation_data[ 'is_on_backorder' ] )
						$all_variations_on_backorder = false;

					// lowest price
					if ( ! is_numeric( $min_variation_regular_price ) || $variation_data[ 'regular_price' ] < $min_variation_regular_price )
						$min_variation_regular_price = $variation_data[ 'regular_price' ];
					if ( ! is_numeric( $min_variation_price ) || $variation_data[ 'price' ] < $min_variation_price )
						$min_variation_price = $variation_data[ 'price' ];

					// highest price
					if ( ! is_numeric( $max_variation_regular_price ) || $variation_data[ 'regular_price' ] > $max_variation_regular_price )
						$max_variation_regular_price = $variation_data[ 'regular_price' ];
					if ( ! is_numeric( $max_variation_price ) || $variation_data[ 'price' ] > $max_variation_price )
						$max_variation_price = $variation_data[ 'price' ];

					// taxed
					if ( $woocommerce_bundle_helpers->is_wc_21() ) {

						if ( ! is_numeric( $min_variation_price_incl_tax ) || $variation_data[ 'price_incl_tax' ] < $min_variation_price_incl_tax )
							$min_variation_price_incl_tax = $variation_data[ 'price_incl_tax' ];

						if ( ! is_numeric( $min_variation_price_excl_tax ) || $variation_data[ 'price_excl_tax' ] < $min_variation_price_excl_tax )
							$min_variation_price_excl_tax = $variation_data[ 'price_excl_tax' ];

					}
				}

				if ( $variation_in_stock_exists == false ) {
					$this->all_items_in_stock = false;
				}

				if ( $all_variations_on_backorder ) {
					$this->has_items_on_backorder = true;
				}

				$add = ( $min_variation_regular_price < $min_variation_price ) ? $min_variation_regular_price : $min_variation_price;

				$this->min_bundle_price 		= $this->min_bundle_price + $this->bundled_item_quantities[ $bundled_item_id ] * $add;
				$this->min_bundle_regular_price = $this->min_bundle_regular_price + $this->bundled_item_quantities[ $bundled_item_id ] * $min_variation_regular_price;

				$add = ( $max_variation_regular_price < $max_variation_price ) ? $max_variation_regular_price : $max_variation_price;

				$this->max_bundle_price 		= $this->max_bundle_price + $this->bundled_item_quantities[ $bundled_item_id ] * $add;
				$this->max_bundle_regular_price = $this->max_bundle_regular_price + $this->bundled_item_quantities[ $bundled_item_id ] * $max_variation_regular_price;

				if ( $woocommerce_bundle_helpers->is_wc_21() ) {
					$this->min_bundle_price_excl_tax = $this->min_bundle_price_excl_tax + $this->bundled_item_quantities[ $bundled_item_id ] * $min_variation_price_excl_tax;
					$this->min_bundle_price_incl_tax = $this->min_bundle_price_incl_tax + $this->bundled_item_quantities[ $bundled_item_id ] * $min_variation_price_incl_tax;
				}

			}

		}

	}

	function woo_bundles_available_variation( $variation_data, $bundled_product, $bundled_variation ) {

		global $woocommerce_bundles, $woocommerce_bundle_helpers;

		$bundled_item_id = $this->processing_item_id;

		// Update sold individually status

		if ( ! $bundled_variation->is_sold_individually() )
			$this->all_items_sold_individually = false;

		// Disable if certain conditions are met

		if ( $this->bundle_data[ $bundled_item_id ][ 'filter_variations' ] == 'yes' ) {
			if ( ! is_array( $this->bundle_data[ $bundled_item_id ][ 'allowed_variations' ] ) )
				return array();
			if ( ! in_array( $bundled_variation->variation_id, $this->bundle_data[ $bundled_item_id ][ 'allowed_variations' ] ) )
				return array();
		}

		if ( $bundled_variation->price === '' ) {
			return array();
		}

		// Modify product id for JS

		$variation_data[ 'product_id' ] = $bundled_item_id;

		// Add price info

		$variation_data[ 'regular_price' ] 	= $woocommerce_bundle_helpers->get_bundled_product_price( $bundled_variation, $woocommerce_bundle_helpers->get_bundled_regular_product_price( $bundled_variation ) );
		$variation_data[ 'price' ]			= $woocommerce_bundle_helpers->get_bundled_product_price( $bundled_variation, $bundled_variation->get_price() );

		if ( $woocommerce_bundle_helpers->is_wc_21() ) {

			if ( get_option( 'woocommerce_tax_display_shop' ) == 'excl' ) {

				$variation_data[ 'regular_price_excl_tax' ] = $variation_data[ 'regular_price' ];
				$variation_data[ 'regular_price_incl_tax' ] = $bundled_variation->get_price_including_tax( 1, $woocommerce_bundle_helpers->get_bundled_regular_product_price( $bundled_variation ) );

				$variation_data[ 'price_excl_tax' ] = $variation_data[ 'price' ];
				$variation_data[ 'price_incl_tax' ] = $bundled_variation->get_price_including_tax( 1, $bundled_variation->get_price() );

			} else {

				$variation_data[ 'regular_price_incl_tax' ] = $variation_data[ 'regular_price' ];
				$variation_data[ 'regular_price_excl_tax' ] = $bundled_variation->get_price_excluding_tax( 1, $woocommerce_bundle_helpers->get_bundled_regular_product_price( $bundled_variation ) );

				$variation_data[ 'price_incl_tax' ] = $variation_data[ 'price' ];
				$variation_data[ 'price_excl_tax' ] = $bundled_variation->get_price_excluding_tax( 1, $bundled_variation->get_price() );

			}

		}

		if ( $variation_data[ 'regular_price' ] > $variation_data[ 'price' ] )
			$this->on_sale = true;

		$variation_data[ 'price_html' ]	= $this->per_product_pricing_active ? '<span class="price">' . $bundled_variation->get_price_html() . '</span>' : '';

		$availability = $woocommerce_bundle_helpers->get_bundled_product_availability( $bundled_variation, $this->bundled_item_quantities[ $bundled_item_id ] );

		if ( $availability[ 'class' ] == 'out-of-stock' )
			$variation_data[ 'is_in_stock' ] = false;

		$variation_data[ 'is_on_backorder' ] = $availability[ 'class' ] == 'available-on-backorder' ? true : false;

		$availability_html 	= ( ! empty( $availability['availability'] ) ) ? apply_filters( 'woocommerce_stock_html', '<p class="stock ' . $availability['class'] . '">'. $availability['availability'].'</p>', $availability['availability']  ) : '';

		$variation_data[ 'availability_html' ] = $availability_html;

		return $variation_data;
	}

	function add_bundled_product_get_price_filter( $bundled_item_id ) {

		$this->item_id_for_price_filter = $bundled_item_id;

		add_filter( 'woocommerce_get_price', array( $this, 'bundled_product_get_price_filter' ), 100, 2 );
		add_filter( 'woocommerce_get_regular_price', array( $this, 'bundled_product_get_regular_price_filter' ), 100, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'bundled_product_get_price_html_filter' ), 10, 2 );
	}

	function bundled_product_get_price_html_filter( $price_html, $product ) {

		if ( ! empty ( $this->item_id_for_price_filter ) && ! isset( $product->is_filtered_price_html ) ) {

			if ( ! $this->per_product_pricing_active )
				return '';

			$product->sale_price 	= $product->get_price();
			$product->price 		= $product->get_price();

			$product->is_filtered_price_html = 'yes';

			return $product->get_price_html();
		}

		return $price_html;
	}

	function bundled_product_get_price_filter( $price, $product ) {

		if ( ! empty ( $this->item_id_for_price_filter ) ) {

			if ( ! $this->per_product_pricing_active )
				return 0;

			$bundled_item_id = $this->item_id_for_price_filter;

			$regular_price 	= $product->regular_price;
			$price 			= $product->price;

			$discount = $this->bundled_item_discounts[ $bundled_item_id ];

			return empty( $discount ) || empty( $regular_price ) ? $price : ( double ) $regular_price * ( 100 - $discount ) / 100;
		}

		return $price;
	}

	function bundled_product_get_regular_price_filter( $price, $product ) {

		if ( ! empty ( $this->item_id_for_price_filter ) ) {

			if ( ! $this->per_product_pricing_active )
				return 0;

			$regular_price 	= $product->regular_price;
			$price 			= $product->price;

			return empty( $regular_price ) ? ( double ) $price : ( double ) $regular_price;
		}

		return $price;
	}

	function remove_bundled_product_get_price_filter( $bundled_item_id ) {

		$this->item_id_for_price_filter = '';

		remove_filter( 'woocommerce_get_price', array( $this, 'bundled_product_get_price_filter' ), 100, 2 );
		remove_filter( 'woocommerce_get_regular_price', array( $this, 'bundled_product_get_regular_price_filter' ), 100, 2 );
		remove_filter( 'woocommerce_get_price_html', array( $this, 'bundled_product_get_price_html_filter' ), 10, 2 );
	}

	function get_bundle_price_data() {
		return $this->bundle_price_data;
	}

	function get_bundle_attributes() {
		return $this->bundle_attributes;
	}

	function get_bundled_item_quantities() {
		return $this->bundled_item_quantities;
	}

	function get_selected_bundle_attributes() {
		return $this->selected_bundle_attributes;
	}

	function get_available_bundle_variations() {
		return $this->available_bundle_variations;
	}

	function get_bundled_products() {
		return $this->bundled_products;
	}

	function get_price() {

		if ( $this->per_product_pricing_active )
			return $this->microdata_display ? $this->min_bundle_price : ( double ) 0;
		else
			return parent::get_price();
	}

	function get_regular_price() {

		if ( $this->per_product_pricing_active )
			return ( double ) 0;
		else
			return $this->regular_price;
	}

	function get_price_suffix() {

	 	global $woocommerce_bundle_helpers;

	 	if ( ! $woocommerce_bundle_helpers->is_wc_21() )
	 		return '';

		if ( $this->per_product_pricing_active ) {

			$price_display_suffix  = get_option( 'woocommerce_price_display_suffix' );

			if ( $price_display_suffix ) {
				$price_display_suffix = ' <small class="woocommerce-price-suffix">' . $price_display_suffix . '</small>';

				$find = array(
					'{price_including_tax}',
					'{price_excluding_tax}'
				);

				$replace = array(
					wc_bundles_price( $this->min_bundle_price_incl_tax ),
					wc_bundles_price( $this->min_bundle_price_excl_tax ),
				);

				$price_display_suffix = str_replace( $find, $replace, $price_display_suffix );
			}

			return apply_filters( 'woocommerce_get_price_suffix', $price_display_suffix, $this );

		} else {

			return parent::get_price_suffix();
		}

	}

	function get_price_html( $price = '' ) {

		global $woocommerce_bundle_helpers;

		if ( ! $woocommerce_bundle_helpers->is_wc_21() )
			return $this->get_price_html_20();

		if ( $this->per_product_pricing_active ) {

			// Get the price
			if ( $this->min_bundle_price === '' ) {

				$price = apply_filters( 'woocommerce_bundle_empty_price_html', '', $this );

			} else {

				// Main price
				$prices = array( $this->min_bundle_price, $this->max_bundle_price );

				if ( ! $this->contains_nyp )
					$price = $prices[0] !== $prices[1] ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $prices[0] ), wc_price( $prices[1] ) ) : wc_price( $prices[0] );
				else
					$price = wc_price( $prices[0] );

				// Sale
				$prices = array( $this->min_bundle_regular_price, $this->max_bundle_regular_price );
				sort( $prices );

				if ( ! $this->contains_nyp )
					$saleprice = $prices[0] !== $prices[1] ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $prices[0] ), wc_price( $prices[1] ) ) : wc_price( $prices[0] );
				else
					$saleprice = wc_price( $prices[0] );


				if ( $price !== $saleprice ) {
					$price = apply_filters( 'woocommerce_bundle_sale_price_html', $this->contains_nyp ? sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-bto' ), $this->get_price_html_from_text(), $this->get_price_html_from_to( $saleprice, $price ) . $this->get_price_suffix() ) : $this->get_price_html_from_to( $saleprice, $price ) . $this->get_price_suffix(), $this );
				} else {
					$price = apply_filters( 'woocommerce_bundle_price_html', $this->contains_nyp ? sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-bto' ), $this->get_price_html_from_text(), $price . $this->get_price_suffix() ) : $price . $this->get_price_suffix(), $this );
				}

			}

			return apply_filters( 'woocommerce_get_price_html', $price, $this );

		} else {

			return parent::get_price_html();
		}

	}

	function get_price_html_20( $price = '' ) {

		if ( $this->per_product_pricing_active ) {

			// Get the price
			if ( $this->min_bundle_price > 0 ) :
				if ( $this->is_on_sale() && $this->min_bundle_regular_price !== $this->min_bundle_price ) :

					if ( !$this->min_bundle_price || $this->min_bundle_price !== $this->max_bundle_price || $this->contains_nyp )
						$price .= $this->get_price_html_from_text();

					$price .= $this->get_price_html_from_to( $this->min_bundle_regular_price, $this->min_bundle_price ) . $this->get_price_suffix();

					$price = apply_filters('woocommerce_bundle_sale_price_html', $price, $this);

				else :

					if ( ! $this->min_bundle_price || $this->min_bundle_price !== $this->max_bundle_price || $this->contains_nyp )
						$price .= $this->get_price_html_from_text();

					$price .= wc_bundles_price( $this->min_bundle_price ) . $this->get_price_suffix();

					$price = apply_filters('woocommerce_bundle_price_html', $price, $this);

				endif;
			elseif ( $this->min_bundle_price === '' ) :

				$price = apply_filters('woocommerce_bundle_empty_price_html', '', $this);

			elseif ( $this->min_bundle_price == 0 ) :

				if ($this->is_on_sale() && isset($this->min_bundle_regular_price) && $this->min_bundle_regular_price !== $this->min_bundle_price ) :

					if ( ! $this->min_bundle_price || $this->min_bundle_price !== $this->max_bundle_price || $this->contains_nyp )
						$price .= $this->get_price_html_from_text();

					$price .= $this->get_price_html_from_to( $this->min_bundle_regular_price, __('Free!', 'woocommerce') );

					$price = apply_filters('woocommerce_bundle_free_sale_price_html', $price, $this);

				else :

					if ( !$this->min_bundle_price || $this->min_bundle_price !== $this->max_bundle_price || $this->contains_nyp )
						$price .= $this->get_price_html_from_text();

					$price .= __('Free!', 'woocommerce');

					$price = apply_filters('woocommerce_bundle_free_price_html', $price, $this);

				endif;

			endif;

			return apply_filters( 'woocommerce_get_price_html', $price, $this );

		} else {

			return parent::get_price_html();
		}

	}

	function is_on_sale() {

		$is_on_sale = false;

		if ( $this->per_product_pricing_active && ! empty( $this->bundle_data ) ) {

			if ( $this->on_sale )
				$is_on_sale = true;

		} else {

			if ( $this->sale_price && $this->sale_price == $this->price )
				$is_on_sale = true;
		}

		return apply_filters( 'woocommerce_bundle_is_on_sale', $is_on_sale, $this );
	}

	/**
	 * Returns whether or not the bundle has any attributes set
	 */
	function has_attributes() {
		// check bundle for attributes
		if ( sizeof( $this->get_attributes() ) > 0 ) :
			foreach ( $this->get_attributes() as $attribute ) :
				if ( isset( $attribute['is_visible'] ) && $attribute['is_visible'] ) return true;
			endforeach;
		endif;
		// check all bundled items for attributes
		if ( $this->get_bundled_products() ) {
			foreach ( $this->get_bundled_products() as $bundled_product ) {
				if ( sizeof( $bundled_product->get_attributes() ) > 0 ) :
					foreach ( $bundled_product->get_attributes() as $attribute ) :
						if ( isset( $attribute['is_visible'] ) && $attribute['is_visible'] )
							return true;
					endforeach;
				endif;
			}
		}
		return false;
	}

	function is_sold_individually() {
		return parent::is_sold_individually() || $this->all_items_sold_individually;
	}

	function get_availability() {

		$backend_availability_data = parent::get_availability();

		if ( ! is_admin() ) {

			$availability = $class = '';

			if ( ! $this->all_items_in_stock ) {

				$availability = __( 'Out of stock', 'woocommerce' );
				$class        = 'out-of-stock';

			} elseif ( $this->has_items_on_backorder ) {

				$availability = __( 'Available on backorder', 'woocommerce' );
				$class        = 'available-on-backorder';

			}

			if ( $backend_availability_data[ 'class' ] == 'out-of-stock' || $backend_availability_data[ 'class' ] == 'available-on-backorder' )
				return $backend_availability_data;
			elseif ( $class == 'out-of-stock' || $class == 'available-on-backorder' )
				return array( 'availability' => $availability, 'class' => $class );

		}

		return $backend_availability_data;
	}

	/**
	 * Lists a table of attributes for the bundle page
	 */
	function list_attributes() {

		// show attributes attached to the bundle only
		wc_bundles_get_template( 'single-product/product-attributes.php', array(
			'product' => $this
		), '', '' );

		foreach ( $this->get_bundled_products() as $bundled_item_id => $bundled_product ) {

			if ( ! $this->per_product_shipping_active )
				$bundled_product->length = $bundled_product->width = $bundled_product->weight = '';

			if ( $bundled_product->has_attributes() ) {

				$GLOBALS['listing_attributes_of'] = $bundled_item_id;

				echo '<h3>'.get_the_title( $bundled_product->id ).'</h3>';

				wc_bundles_get_template( 'single-product/product-attributes.php', array(
					'product' => $bundled_product
				), '', '' );
			}
		}
		unset( $GLOBALS['listing_attributes_of'] );
	}

	/**
	 * Get the add to url used mainly in loops.
	 */
	function add_to_cart_url() {

		$url = $this->is_purchasable() && $this->is_in_stock() && $this->all_items_in_stock && empty( $this->available_bundle_variations ) ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

		return apply_filters( 'bundle_add_to_cart_url', $url, $this );
	}

	/**
	 * Get the add to cart button text
	 */
	function add_to_cart_text() {

		$text = __( 'Read More', 'woocommerce' );

		if ( $this->is_purchasable() && $this->is_in_stock() && $this->all_items_in_stock ) {
			if ( empty( $this->available_bundle_variations ) )
				$text =  __( 'Add to cart', 'woocommerce' );
			else
				$text =  __( 'Select options', 'woocommerce' );
		}

		return apply_filters( 'bundle_add_to_cart_text', $text, $this );
	}

}
