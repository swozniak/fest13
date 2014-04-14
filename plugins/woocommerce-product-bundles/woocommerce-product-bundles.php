<?php
/*
Plugin Name: WooCommerce Product Bundles
Plugin URI: http://woothemes.com/woocommerce
Description: WooCommerce extension for creating configurable product bundles, kits and assemblies.
Author: SomewhereWarm
Author URI: http://www.somewherewarm.net/
Version: 4.1.1
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'fbca839929aaddc78797a5b511c14da9', '18716' );

if ( is_woocommerce_active() ) {

	/**
	 * Product Bundles Main Class
	 *
	 * @class 	WC_Bundles
	 * @version 4.1.1
	 */

	class WC_Bundles {

		var $version 	= '4.1.1';
		var $required 	= '2.0.18';

		var $addons_prefix 	= '';
		var $nyp_prefix 	= '';

		public function __construct() {

			add_action( 'plugins_loaded', array( $this, 'woo_bundles_plugins_loaded' ) );
			add_action( 'init', array( $this, 'woo_bundles_init' ) );
			add_action( 'admin_init', array( $this, 'woo_bundles_admin_init' ) );
		}

		function woo_bundles_plugin_url() {
			return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		}

		function woo_bundles_plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}


		function woo_bundles_plugins_loaded() {

			global $woocommerce;

			// WC 2 check
			if ( version_compare( $woocommerce->version, $this->required ) < 0 ) {
				add_action( 'admin_notices', array( $this, 'woo_bundles_admin_notice' ) );
				return false;
			}

			if ( version_compare( $woocommerce->version, '2.0.22' ) > 0 )
				include( 'includes/wc-21-functions.php' );
			else
				include( 'includes/wc-20-functions.php' );

			include( 'includes/class-wc-bundle-helpers.php' );

			include( 'includes/class-wc-product-bundle.php' );

			// Admin jquery
			add_action( 'admin_enqueue_scripts', array( $this, 'woo_bundles_admin_scripts' ), 11 );
			// Front end variation select box jquery for multiple variable products
			add_action( 'wp_enqueue_scripts', array( $this, 'woo_bundles_frontend_scripts' ), 100 );

			// Creates the admin panel tab 'Bundled Products'
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'woo_bundles_product_write_panel_tab' ) );

			// Creates the panel for selecting bundled product options
			add_action( 'woocommerce_product_write_panels', array( $this, 'woo_bundles_product_write_panel' ) );
			add_action( 'woocommerce_product_options_stock', array( $this, 'woo_bundles_stock_group' ) );

			add_filter( 'product_type_options', array( $this, 'woo_bundles_type_options' ) );

			// Processes and saves the necessary post metas from the selections made above
			add_action( 'woocommerce_process_product_meta_bundle', array( $this, 'woo_bundles_process_bundle_meta' ) );

			// Allows the selection of the 'bundled product' type
			add_filter( 'product_type_selector', array( $this, 'woo_bundles_product_selector_filter' ) );

			// Bundle containers should not affect order status
			add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'woo_bundles_container_items_need_no_processing' ), 10, 3 );

			// Front End Hooks

			// Load bundle data from session into the cart
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'woo_bundles_get_cart_data_from_session' ), 10, 2 );

			// Sync quantities of bundled items with bundle quantity
			add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'woo_bundles_update_quantity_in_cart' ), 1, 2 );
			add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'woo_bundles_update_quantity_in_cart' ) );

			// Validate bundle add-to-cart
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'woo_bundles_validation' ), 10, 6 );

			// Add bundle-specific cart item data
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woo_bundles_add_cart_item_data' ), 10, 2 );

			// Add bundled items to the cart
			add_action( 'woocommerce_add_to_cart', array( $this, 'woo_bundles_add_bundle_to_cart' ), 10, 6 );

			// Front End Hooks (contd.)

			// Single product template for product bundles
			add_action( 'woocommerce_bundle_add_to_cart', array( $this, 'woo_bundles_add_to_cart' ) );

			// Sync quantities of bundled items with bundle quantity
			add_filter( 'woocommerce_cart_item_quantity', array( $this, 'woo_bundles_cart_item_quantity' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'woo_bundles_cart_item_remove_link' ), 10, 2 );

			// Add 'part of' text in cart
			add_filter( 'woocommerce_get_item_data',  array( $this, 'woo_bundles_get_item_data' ), 10, 2 );

			// Filter add_to_cart_url & add_to_cart_text when product type is 'bundle'
			if ( version_compare( $woocommerce->version, '2.0.22' ) < 0 ) {
				add_filter( 'add_to_cart_url', array( $this, 'woo_bundles_loop_add_to_cart_url' ), 10 );
				add_filter( 'add_to_cart_class', array( $this, 'woo_bundles_add_to_cart_class' ), 10 );
				add_filter( 'add_to_cart_text', array( $this, 'woo_bundles_add_to_cart_text' ), 10 );
			} else {
				add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'woo_bundles_loop_add_to_cart_link' ), 10, 2 );
			}

			// Filter price output shown in cart, review-order & order-details templates
			add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'woo_bundles_order_item_subtotal' ), 10, 3 );

			if ( version_compare( $woocommerce->version, '2.0.22' ) > 0 ) {
				add_filter( 'woocommerce_cart_item_price', array( $this, 'woo_bundles_cart_item_price_html' ), 10, 3 );
			} else {
				add_filter( 'woocommerce_cart_item_price_html', array( $this, 'woo_bundles_cart_item_price_html' ), 10, 3 );
			}

			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'woo_bundles_item_subtotal' ), 10, 3 );
			add_filter( 'woocommerce_checkout_item_subtotal', array( $this, 'woo_bundles_item_subtotal' ), 10, 3 );

			// Change the tr class attributes when displaying bundled items in templates
			if ( version_compare( $woocommerce->version, '2.0.22' ) > 0 ) {
				add_filter( 'woocommerce_cart_item_class', array( $this, 'woo_bundles_table_item_class' ), 10, 3 );
				add_filter( 'woocommerce_order_item_class', array( $this, 'woo_bundles_table_item_class' ), 10, 3 );
			} else {
			// Deprecated
				add_filter( 'woocommerce_cart_table_item_class', array( $this, 'woo_bundles_table_item_class' ), 10, 3 );
				add_filter( 'woocommerce_order_table_item_class', array( $this, 'woo_bundles_table_item_class' ), 10, 3 );
				add_filter( 'woocommerce_checkout_table_item_class', array( $this, 'woo_bundles_table_item_class' ), 10, 3 );
			}

			// Modify cart items for bundled pricing strategy
			add_filter( 'woocommerce_add_cart_item', array( $this, 'woo_bundles_add_cart_item_filter' ), 10, 2 );

			// Modify order items to include bundled_by info
			add_action( 'woocommerce_add_order_item_meta', array( $this, 'woo_bundles_add_order_item_meta' ), 10, 2 );
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'woo_bundles_hidden_order_item_meta' ) );

			// Set empty price message
			add_filter( 'woocommerce_empty_price_html', array( $this, 'woo_bundles_empty_price' ), 10, 2 );

			// Filter cart widget items
			add_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'woo_bundles_cart_widget_filter' ), 10, 3 );

			// Filter cart item count
			add_filter( 'woocommerce_cart_contents_count',  array( $this, 'woo_bundles_cart_contents_count' ) );

			// Support for Product Addons
			add_action( 'woocommerce_bundled_product_add_to_cart', array( $this, 'woo_bundles_addons_support' ), 10, 2 );
			add_filter( 'product_addons_field_prefix', array( $this, 'woo_bundles_addons_cart_prefix' ), 10, 2 );

			// Support for NYP
			add_action( 'woocommerce_bundled_product_add_to_cart', array( $this, 'woo_bundles_nyp_price_input_support' ), 9, 2 );
			add_filter( 'nyp_field_prefix', array( $this, 'woo_bundles_nyp_cart_prefix' ), 10, 2 );

			// Filter order item count
			add_filter( 'woocommerce_get_item_count',  array( $this, 'woo_bundles_order_item_count' ), 10, 3 );

			// Put back cart item data to allow re-ordering of bundles
			add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'woo_bundles_order_again' ), 10, 3 );

			// QuickView support
			add_action( 'wc_quick_view_enqueue_scripts', array( $this, 'woo_bundles_qv' ) );

			// Transients clean
			add_action( 'woocommerce_delete_product_transients', array( $this, 'woo_bundles_clean_transients' ), 10 );

			// Price Filter results
			add_filter( 'woocommerce_price_filter_results', array( $this, 'woo_bundles_price_filter_results' ), 10, 3 );

			// microdata
			add_action( 'woocommerce_single_product_summary', array( $this, 'woo_bundles_loop_price_9' ), 9 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'woo_bundles_loop_price_11' ), 11 );

			// Debug
			// add_action( 'woocommerce_before_cart_contents', array($this, 'woo_bundles_before_cart') );

		}

		/**
		 * Display a warning message if WC version check fails.
		 */
		function woo_bundles_admin_notice() {
		    echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Product Bundles requires at least WooCommerce %s in order to function. Please upgrade WooCommerce.', 'woo-bundles'), $this->required ) . '</p></div>';
		}

		/**
		 * Activation script
		 **/
		function woo_bundles_admin_init() {

			// if 'bundle' term exists, get rid of it
			$bundle_term_id = term_exists( 'bundle' );

			if ( $bundle_term_id && ! get_term_by( 'slug', 'bundle', 'product_type' ) ) {

				$taxonomies = get_taxonomies( '', 'names' );

				foreach ( $taxonomies as $taxonomy ) {
					$bundle_term = get_term_by( 'id', $bundle_term_id, $taxonomy );
					if ( $bundle_term ) {
						wp_update_term( $bundle_term->term_id, $taxonomy, array( 'slug' => 'bundle-99' ) );
						return;
					}
				}
			}

		}


		function woo_bundles_init() {

			load_plugin_textdomain( 'woo-bundles', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			// Filter bundled item attributes based on active variation filters
			add_filter( 'woocommerce_attribute',  array( $this, 'woo_bundles_attribute' ), 10, 3 );
		}


		/**
		 * Modify microdata get_price call
		 */
		function woo_bundles_loop_price_9() {
			global $product;

			if ( $product->is_type( 'bundle' ) && $product->per_product_pricing_active )
				$product->microdata_display = true;
		}

		function woo_bundles_loop_price_11() {
			global $product;

			if ( $product->is_type( 'bundle' ) && $product->per_product_pricing_active )
				$product->microdata_display = false;
		}

		/**
		 * Updates post_meta storage scheme
		 */
		function serialize_bundle_meta( $bundle_id ) {

			global $wpdb;

			$bundled_item_ids 	= maybe_unserialize( get_post_meta( $bundle_id, '_bundled_ids', true ) );
			$default_attributes = maybe_unserialize( get_post_meta( $bundle_id, '_bundle_defaults', true ) );
			$allowed_variations = maybe_unserialize( get_post_meta( $bundle_id, '_allowed_variations', true ) );

			$bundle_data = array();

			foreach ( $bundled_item_ids as $bundled_item_id ) {

				$bundle_data[ $bundled_item_id ] = array();

				$filtered 			= get_post_meta( $bundle_id, 'filter_variations_' . $bundled_item_id, true );
				$o_defaults			= get_post_meta( $bundle_id, 'override_defaults_' . $bundled_item_id, true );
				$hide_thumbnail		= get_post_meta( $bundle_id, 'hide_thumbnail_' . $bundled_item_id, true );
				$item_o_title 		= get_post_meta( $bundle_id, 'override_title_' . $bundled_item_id, true );
				$item_title 		= get_post_meta( $bundle_id, 'product_title_' . $bundled_item_id, true );
				$item_o_desc 		= get_post_meta( $bundle_id, 'override_description_' . $bundled_item_id, true );
				$item_desc			= get_post_meta( $bundle_id, 'product_description_' . $bundled_item_id, true );
				$item_qty			= get_post_meta( $bundle_id, 'bundle_quantity_' . $bundled_item_id, true );
				$discount			= get_post_meta( $bundle_id, 'bundle_discount_' . $bundled_item_id, true );
				$visibility			= get_post_meta( $bundle_id, 'visibility_' . $bundled_item_id, true );

				$sep = explode( '_', $bundled_item_id );

				$bundle_data[ $bundled_item_id ][ 'product_id' ] 				= $sep[0];


				$bundle_data[ $bundled_item_id ][ 'filter_variations' ] 		= $filtered == 'yes' ? 'yes' : 'no';

				if ( isset( $allowed_variations[ $bundled_item_id ] ) )
					$bundle_data[ $bundled_item_id ][ 'allowed_variations' ] 	= $allowed_variations[ $bundled_item_id ];


				$bundle_data[ $bundled_item_id ][ 'override_defaults' ] 		= $o_defaults == 'yes' ? 'yes' : 'no';

				if ( isset( $default_attributes[ $bundled_item_id ] ) )
					$bundle_data[ $bundled_item_id ][ 'bundle_defaults' ] 		= $default_attributes[ $bundled_item_id ];


				$bundle_data[ $bundled_item_id ][ 'hide_thumbnail' ] 			= $hide_thumbnail == 'yes' ? 'yes' : 'no';


				$bundle_data[ $bundled_item_id ][ 'override_title' ] 			= $item_o_title == 'yes' ? 'yes' : 'no';

				if ( $item_o_title == 'yes' )
					$bundle_data[ $bundled_item_id ][ 'product_title' ] 		= $item_title;


				$bundle_data[ $bundled_item_id ][ 'override_description' ] 		= $item_o_desc == 'yes' ? 'yes' : 'no';

				if ( $item_o_desc == 'yes' )
					$bundle_data[ $bundled_item_id ][ 'product_description' ] 	= $item_desc;


				$bundle_data[ $bundled_item_id ][ 'bundle_quantity' ] 			= $item_qty;
				$bundle_data[ $bundled_item_id ][ 'bundle_discount' ] 			= $discount;

				$bundle_data[ $bundled_item_id ][ 'visibility' ] 				= $visibility == 'hidden' ? 'hidden' : 'visible';

				$bundle_data[ $bundled_item_id ][ 'hide_filtered_variations' ] 	= 'no';
			}

			update_post_meta( $bundle_id, '_bundle_data', $bundle_data );

			$wpdb->query( $wpdb->prepare( "DELETE FROM `$wpdb->postmeta` WHERE `post_id` LIKE %s AND (
				`meta_key` LIKE %s OR
				`meta_key` LIKE %s OR
				`meta_key` LIKE %s OR
				`meta_key` LIKE %s OR
				`meta_key` LIKE %s OR
				`meta_key` LIKE %s OR
				`meta_key` LIKE %s OR
				`meta_key` LIKE %s OR
				`meta_key` LIKE %s OR
				`meta_key` LIKE %s OR
				`meta_key` LIKE %s OR
				`meta_key` LIKE ('_bundled_ids') OR
				`meta_key` LIKE ('_bundle_defaults') OR
				`meta_key` LIKE ('_allowed_variations')
			)", $bundle_id, 'filter_variations_%', 'override_defaults_%', 'bundle_quantity_%', 'bundle_discount_%', 'hide_thumbnail_%', 'override_title_%', 'product_title_%', 'override_description_%', 'product_description_%', 'hide_filtered_variations_%', 'visibility_%' ) );

			return $bundle_data;
		}

		/**
		 * Add detailed bundle price filter results
		 */
		function woo_bundles_price_filter_results( $results, $min, $max ) {

			global $wpdb;

			// Clean out bundles
			$args = array(
				'post_type' 	=> 'product',
				'tax_query' => array(
					array(
						'taxonomy' => 'product_type',
						'field' => 'slug',
						'terms' => 'bundle'
						)
				),
				'fields'        => 'ids'
			);

			$bundle_ids 	= get_posts( $args );

			$clean_results 	= array();

			if ( ! empty ( $bundle_ids ) ) {

				foreach ( $results as $key => $result ) {

					if ( $result->post_type == 'product' && in_array( $result->ID, $bundle_ids ) )
						continue;

					$clean_results[ $key ] = $result;
				}
			}

			$bundle_results = array();

			$bundle_results = $wpdb->get_results( $wpdb->prepare( "
	        	SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta meta_1 ON ID = meta_1.post_id
				INNER JOIN $wpdb->postmeta meta_2 ON ID = meta_2.post_id
				WHERE post_type IN ( 'product' )
					AND post_status = 'publish'
					AND meta_1.meta_key = '_max_bundle_price' AND meta_1.meta_value > %d
					AND meta_2.meta_key = '_min_bundle_price' AND meta_2.meta_value < %d
			", $min, $max ), OBJECT_K );

			$merged_results = $clean_results + $bundle_results;

			return $merged_results;
		}

		/**
		 * Clear bundle transients for said product if active (2.1)
		 */
		function woo_bundles_clean_transients( $post_id ){

			global $wpdb;

			if ( $post_id > 0 ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE %s OR `option_name` LIKE %s", '_transient_wc_bundled_item_' . $post_id . '_%', '_transient_timeout_wc_bundled_item_' . $post_id . '_%' ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE %s OR `option_name` LIKE %s", '_transient_wc_bundled_item_%_' . $post_id, '_transient_timeout_wc_bundled_item_%_' . $post_id ) );
			} else {
				$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_bundled_item_%') OR `option_name` LIKE ('_transient_timeout_wc_bundled_item_%')" );
			}
		}

		/**
		 * Load quickview script
		 */
		function woo_bundles_qv() {

			if ( ! is_product() ) {

				$this->woo_bundles_frontend_scripts();
				wp_enqueue_script( 'wc-add-to-cart-bundle' );
				wp_enqueue_style( 'wc-bundle-css' );

			}

		}

		/**
		 * Reinialize cart item data for re-ordering purchased orders
		 */
		function woo_bundles_order_again( $cart_item_data, $order_item, $order ) {

			if ( isset( $order_item[ 'bundled_by' ] ) && isset( $order_item[ 'stamp' ] ) )
				$cart_item_data[ 'is_bundled' ] = 'yes';

			if ( ! isset( $order_item[ 'bundled_by' ] ) && isset( $order_item[ 'stamp' ] ) )
				$cart_item_data[ 'stamp' ] = maybe_unserialize( $order_item[ 'stamp' ] );

			return $cart_item_data;
		}

		/**
		 * Filters the reported number of order items
		 */
		function woo_bundles_order_item_count( $count, $type, $order ) {

			global $woocommerce;

			$subtract = 0;

			foreach ( $order->get_items() as $item ) {

				// If it's a bundled item
				if ( isset( $item[ 'bundled_by' ] ) ) {

					// find bundle item by its stamp
					foreach ( $order->get_items() as $order_item ) {

						if ( $order_item[ 'stamp' ] == $item[ 'stamp' ] && ! isset( $order_item[ 'bundled_by' ] ) ) {
							$bundle_product_id = $order_item[ 'product_id' ];
							$per_product_priced_bundle = $order_item[ 'per_product_pricing' ];
							break;
						}
					}

					$per_product_pricing = isset( $per_product_priced_bundle ) && ! empty( $per_product_priced_bundle ) ? $per_product_priced_bundle : get_post_meta( $bundle_product_id, '_per_product_pricing_active', true );

					if ( $per_product_pricing == 'no' )
						$subtract += $item[ 'qty' ];
				}

				// If it's a bundle (parent item)
				if ( ! isset( $item[ 'bundled_by' ] ) && isset( $item[ 'stamp' ] ) ) {

					$per_product_pricing = isset( $item[ 'per_product_pricing' ] ) ? $item[ 'per_product_pricing' ] : get_post_meta( $item[ 'product_id' ], '_per_product_pricing_active', true );

					if ( $per_product_pricing == 'yes' )
						$subtract += $item[ 'qty' ];

				}


			}

			return $count - $subtract;

		}

		/**
		 * Bundle Containers need no processing - let it be decided by bundled items only
		 **/
		function woo_bundles_container_items_need_no_processing( $is_needed, $product, $order_id ) {
			if ( $product->is_type( 'bundle' ) ) {
				return false;
			}
			return $is_needed;
		}

		/**
		 * Support for bundled item addons
		 **/
		function woo_bundles_addons_support( $product_id, $item_id ) {

			global $Product_Addon_Display;

			if ( ! empty( $Product_Addon_Display ) )
				$Product_Addon_Display->display( $product_id, $item_id . '-' );

		}

		/**
		 * Support for bundled item nyp
		 **/
		function woo_bundles_nyp_price_input_support( $product_id, $item_id ) {

			global $product;

			if ( $product->product_type == 'bundle' && $product->per_product_pricing_active == false )
				return;

			if ( function_exists( 'WC_Name_Your_Price' ) ) {

				// Get product type
				$terms 			= get_the_terms( $product_id, 'product_type' );
				$product_type 	= ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

				if ( $product_type == 'simple' )
					WC_Name_Your_Price()->display->display_price_input( $product_id, '-' . $item_id );
			}

		}

		/**
		 * Sets a prefix for unique add-ons
		 **/
		function woo_bundles_addons_cart_prefix( $prefix, $product_id ) {

			if ( ! empty( $this->addons_prefix ) )
				return $this->addons_prefix . '-';

			return $prefix;
		}

		/**
		 * Sets a prefix for unique add-ons
		 **/
		function woo_bundles_nyp_cart_prefix( $prefix, $product_id ) {

			if ( ! empty( $this->nyp_prefix ) )
				return '-' . $this->nyp_prefix;

			return $prefix;
		}

		/**
		 * Validate bundle add-to-cart
		 **/
		function woo_bundles_validation( $add, $product_id, $product_quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {

			global $woocommerce;

			// Get product type
			$terms 			= get_the_terms( $product_id, 'product_type' );
			$product_type 	= ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

			// prevent bundled items from getting validated - they will be added by the container item
			if ( isset( $cart_item_data[ 'is_bundled' ] ) && isset( $_GET['order_again'] ) )
				return false;

			if ( $product_type == 'bundle' ) {

				$bundle_data = maybe_unserialize( get_post_meta( $product_id, '_bundle_data', true ) );

				// Check request and prepare variation stock check data
				$stock_check_data = array();

				foreach ( $bundle_data as $bundled_item_id => $bundled_item_data ) {

					$id = $bundled_item_data[ 'product_id' ];

					$the_bundled_product = get_product( $id );

					if ( ! $the_bundled_product || ! $the_bundled_product->is_purchasable() )
						continue;

					$bundled_product_type = $the_bundled_product->product_type;

					$item_quantity 	= $bundled_item_data[ 'bundle_quantity' ];
					$item_quantity 	= ( isset( $item_quantity ) && $item_quantity > 0 ) ? (int) $item_quantity : 1;
					$quantity		= $item_quantity * $product_quantity;

					if ( $bundled_product_type == 'variable' ) {

						$variation_id = isset( $cart_item_data[ 'stamp' ][ $bundled_item_id ][ 'variation_id' ] ) && isset( $_GET[ 'order_again' ] ) ? $cart_item_data[ 'stamp' ][ $bundled_item_id ][ 'variation_id' ] : $_REQUEST[ 'bundle_variation_id' ][ $bundled_item_id ] ;

						if ( isset( $variation_id ) && is_numeric( $variation_id ) && $variation_id > 1 ) {

							$stock_check_data[ $id ][ 'type' ] = 'variable';

							$variation_stock = get_post_meta( $variation_id, '_stock', true );

							if ( get_post_meta( $variation_id, '_price', true ) === '' ) {
								wc_bundles_add_notice( sprintf( __( 'Sorry, the selected variation of &quot;%s&quot; cannot be purchased.', 'woo-bundles' ), get_the_title( $id ) ), 'error' );
								return false;
							}

							if ( !isset( $stock_check_data[ $id ][ 'variations' ] ) )
								$stock_check_data[$id][ 'variations' ] = array();

							if ( !isset( $stock_check_data[ $id ][ 'managed_quantities' ] ) )
								$stock_check_data[ $id ][ 'managed_quantities' ] = array();

							if ( !in_array( $variation_id, $stock_check_data[ $id ][ 'variations' ] ) )
								$stock_check_data[ $id ][ 'variations' ][] = $variation_id;

							// If stock is managed on a variation level
							if ( isset( $variation_stock ) && $variation_stock !== '' ) {

								// If a stock-managed variation is added to the cart multiple times,
								// its stock must be checked for the sum of all quantities
								if ( isset( $stock_check_data[ $id ][ 'managed_quantities' ][ $variation_id ] ) )
									$stock_check_data[ $id ][ 'managed_quantities' ][ $variation_id ] += $quantity;
								else
									$stock_check_data[ $id ][ 'managed_quantities' ][ $variation_id ] = $quantity;

							}
							else {

								// Non-stock-managed variations of the same item
								// must be stock-checked together
								if ( isset( $stock_check_data[ $id ][ 'quantity' ] ) )
									$stock_check_data[ $id ][ 'quantity' ] += $quantity;
								else {
									$stock_check_data[ $id ][ 'quantity' ] = $quantity;
								}
							}

						}
						else {
	    					wc_bundles_add_notice( __( 'Please choose product options&hellip;', 'woocommerce' ), 'error' );
							return false;
						}

						// Verify all attributes for the variable product were set - TODO: verify with filters

						$attributes = ( array ) maybe_unserialize( get_post_meta( $id, '_product_attributes', true ) );
			    		$variations = array();
			    		$all_set 	= true;

			    		$variation_data = array();

						$custom_fields = get_post_meta( $variation_id );

						// Get the variation attributes from meta
						foreach ( $custom_fields as $name => $value ) {
							if ( ! strstr( $name, 'attribute_' ) )
								continue;

							$variation_data[ $name ] = sanitize_title( $value[0] );
						}


						// Verify all attributes
						foreach ( $attributes as $attribute ) {
						    if ( ! $attribute['is_variation'] )
						    	continue;

						    $taxonomy = 'attribute_' . sanitize_title( $attribute['name'] );

							if ( ! empty( $_REQUEST[ 'bundle_' . $taxonomy ][ $bundled_item_id ] ) ) {

						        // Get value from post data
						        // Don't use woocommerce_clean as it destroys sanitized characters
						        $value = sanitize_title( trim( stripslashes( $_REQUEST[ 'bundle_' . $taxonomy ][ $bundled_item_id ] ) ) );

						        // Get valid value from variation
						        $valid_value = $variation_data[ $taxonomy ];

						        // Allow if valid
						        if ( $valid_value == '' || $valid_value == $value ) {
						            continue;
						        }

							} elseif ( isset( $cart_item_data[ 'stamp' ][ $bundled_item_id ][ 'attributes' ] ) && isset( $cart_item_data[ 'stamp' ][ $bundled_item_id ][ 'variation_id' ] )  && isset( $_GET[ 'order_again' ] ) ) {

								$value = sanitize_title( trim( stripslashes( $cart_item_data[ 'stamp' ][ $bundled_item_id ][ 'attributes' ][ esc_html( $attribute[ 'name' ] ) ] ) ) ); // $taxonomy in WC 2.1

						        $valid_value = $variation_data[ $taxonomy ];

						        if ( $valid_value == '' || $valid_value == $value ) {
						            continue;
						        }
							}

						    $all_set = false;
						}

						if ( ! $all_set ) {
							wc_bundles_add_notice( __( 'Please choose product options&hellip;', 'woocommerce' ), 'error' );
							return false;
						}


					} elseif ( $bundled_product_type == 'simple' ) {

						$stock_check_data[ $id ][ 'type' ] = 'simple';

						if ( isset( $stock_check_data[ $id ][ 'quantity' ] ) )
							$stock_check_data[ $id ][ 'quantity' ] += $quantity;
						else {
							$stock_check_data[ $id ][ 'quantity' ] = $quantity;
						}
					}


					// Validate add-ons
					global $Product_Addon_Cart;

					if ( ! empty( $Product_Addon_Cart ) ) {

						$this->addons_prefix = $bundled_item_id;

						if ( ! $Product_Addon_Cart->validate_add_cart_item( true, $id, $quantity ) )
							return false;

						$this->addons_prefix = '';
					}

					// Validate nyp

					if ( get_post_meta( $product_id, '_per_product_pricing_active', true ) == 'yes' && function_exists( 'WC_Name_Your_Price' ) ) {

						$this->nyp_prefix = $bundled_item_id;

						if ( ! WC_Name_Your_Price()->cart->validate_add_cart_item( true, $id, $quantity ) )
							return false;

						$this->nyp_prefix = '';
					}

				}


				// Check stock for bundled items one by one
				// If out of stock, don't proceed

				foreach ( $stock_check_data as $item_id => $data ) {

					if ( $data['type'] == 'variable' ) {

						foreach( $data['variations'] as $variation_id ) {

							if ( array_key_exists( $variation_id, $data[ 'managed_quantities' ] ) )
								$quantity = $data[ 'managed_quantities' ][ $variation_id ];
							else
								$quantity = $data[ 'quantity' ];

							if ( ! $this->validate_stock( $item_id, $variation_id, $quantity, false, false ) )
								return false;

						}

					}
					elseif ( $data[ 'type' ] == 'simple' ) {

						// if out of stock, don't proceed
						if ( ! $this->validate_stock( $item_id, '', $data[ 'quantity' ], false, false ) ) {
							return false;
						}

					}

				}

			}

			return $add;
		}


		/**
		 * Adds bundle specific cart-item data
		 * The 'stamp' var is a unique identifier for that particular bundle configuration
		 **/
		function woo_bundles_add_cart_item_data( $cart_item_data, $product_id ) {

			global $woocommerce_bundle_helpers;

			// Get product type
			$terms 			= get_the_terms( $product_id, 'product_type' );
			$product_type 	= ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

			if ( $product_type == 'bundle' ) {

				$bundle_data = maybe_unserialize( get_post_meta( $product_id, '_bundle_data', true ) );

				// Create a unique stamp id with the bundled items' configuration
				$stamp = array();

				foreach ( $bundle_data as $bundled_product_id => $bundled_product_data ) {

					$id = $bundled_product_data[ 'product_id' ];

					$the_bundled_product = get_product( $id );

					if ( ! $the_bundled_product || ! $the_bundled_product->is_purchasable() )
						continue;

					$bundled_product_type = $the_bundled_product->product_type;

					$stamp[ $bundled_product_id ][ 'product_id' ] 	= $id;
					$stamp[ $bundled_product_id ][ 'type' ] 		= $bundled_product_type;

					$bundled_product_quantity 	= $bundled_product_data[ 'bundle_quantity' ];
					$bundled_product_quantity 	= ( isset( $bundled_product_quantity ) && $bundled_product_quantity > 0 ) ? (int) $bundled_product_quantity : 1;

					$stamp[ $bundled_product_id ][ 'quantity' ]	= $bundled_product_quantity;

					$bundled_product_discount	= $bundled_product_data [ 'bundle_discount' ];

					$stamp[ $bundled_product_id ][ 'discount' ]	= $bundled_product_discount;

					if ( $bundled_product_type == 'variable' ) {

						if ( isset( $cart_item_data[ 'stamp' ][ $bundled_product_id ][ 'attributes' ] ) && isset( $_GET[ 'order_again' ] ) ) {

							$stamp[ $bundled_product_id ][ 'attributes' ] 	= $cart_item_data[ 'stamp' ][ $bundled_product_id ][ 'attributes' ];
							$stamp[ $bundled_product_id ][ 'variation_id' ] = $cart_item_data[ 'stamp' ][ $bundled_product_id ][ 'variation_id' ];

							continue;
						}

						$attr_stamp 	= array();
						$attributes 	= ( array ) maybe_unserialize( get_post_meta( $id, '_product_attributes', true ) );

						foreach ( $attributes as $attribute ) {

							if ( ! $attribute[ 'is_variation' ] )
								continue;

							$taxonomy 	= 'attribute_' . sanitize_title( $attribute[ 'name' ] );

							// has already been checked for validity in function 'woo_bundles_validation'
							$value 		= sanitize_title( trim( stripslashes( $_REQUEST[ 'bundle_' . $taxonomy ][ $bundled_product_id ] ) ) );

							if ( $attribute[ 'is_taxonomy' ] ) {
								if ( $woocommerce_bundle_helpers->is_wc_21() )
									$attr_stamp[ $taxonomy ] = $value;
								else
									$attr_stamp[ esc_html( $attribute['name'] ) ] = $value;

							} else {
							    // For custom attributes, get the name from the slug
							    $options = array_map( 'trim', explode( wc_bundles_delimiter(), $attribute[ 'value' ] ) );
							    foreach ( $options as $option ) {
							    	if ( sanitize_title( $option ) == $value ) {
							    		$value = $option;
							    		break;
							    	}
							    }
								if ( $woocommerce_bundle_helpers->is_wc_21() )
									$attr_stamp[ $taxonomy ] = $value;
								else
									$attr_stamp[ esc_html( $attribute['name'] ) ] = $value;
							}

						}

						$stamp[ $bundled_product_id ][ 'attributes' ] 	= $attr_stamp;
						$stamp[ $bundled_product_id ][ 'variation_id' ] = $_REQUEST[ 'bundle_variation_id' ][ $bundled_product_id ];
					}
				}

				$cart_item_data[ 'stamp' ] = $stamp;

				// Prepare additional data for later use
				$cart_item_data[ 'bundled_items' ] = array();

				return $cart_item_data;

			} else {
				return $cart_item_data;
			}

		}


		/**
		 * Adds bundled items to the cart.
		 * The 'bundled by' var is added to each item to identify between bundled and non-bundled instances of products.
		 **/
		function woo_bundles_add_bundle_to_cart( $item_cart_key, $bundle_id, $bundle_quantity, $variation_id, $variation, $cart_item_data ) {

			global $woocommerce;

			if ( isset( $cart_item_data[ 'stamp' ] ) && ! isset( $cart_item_data[ 'bundled_by' ] ) ) {

				// Only attempt to add bundled items if they don't already exist
				foreach ( $woocommerce->cart->cart_contents as $key => $value ) {
					if ( isset( $value[ 'bundled_by' ] ) && $item_cart_key == $value[ 'bundled_by' ] ) {
						return;
					}
				}

				$GLOBALS[ 'bundled_items' ] = array();

				// This id is unique, so that bundled and non-bundled versions of the same product will be added separately to the cart.
				$bundled_item_cart_data = array( 'bundled_item_id' => '', 'bundled_by' => $item_cart_key, 'stamp' => $cart_item_data[ 'stamp' ], 'dynamic_pricing_allowed' => 'no' );

				// Now add all items - yay
				foreach ( $cart_item_data[ 'stamp' ] as $bundled_item_id => $bundled_item_stamp ) {

					// identifier needed for fetching post meta
					$bundled_item_cart_data[ 'bundled_item_id' ] = $bundled_item_id;

					$item_quantity 	= $bundled_item_stamp[ 'quantity' ];
					$quantity		= $item_quantity * $bundle_quantity ;

					$bundled_product_type = $bundled_item_stamp[ 'type' ];

					if ( $bundled_product_type == 'simple' ) {
						$variation_id 	= '';
						$variations		= array();
					}
					elseif ( $bundled_product_type == 'variable' ) {

						$variation_id 	= $bundled_item_stamp[ 'variation_id' ];
						$variations		= $bundled_item_stamp[ 'attributes' ];

					}

					// Set addons and nyp prefix
					$this->addons_prefix = $this->nyp_prefix = $bundled_item_id;
					// Add to cart
					$woocommerce->cart->add_to_cart( $bundled_item_stamp[ 'product_id' ], $quantity, $variation_id, $variations, $bundled_item_cart_data );
					// Reset addons and nyp prefix
					$this->addons_prefix = $this->nyp_prefix = '';
				}

				$woocommerce->cart->cart_contents[ $item_cart_key ][ 'bundled_items' ] = $GLOBALS[ 'bundled_items' ];
				unset( $GLOBALS[ 'bundled_items' ] );
			}

			// Runs when adding bundled items - adds child data to parent
			if ( isset( $cart_item_data[ 'bundled_by' ] ) && ! empty( $cart_item_data[ 'bundled_by' ] ) ) {

				$parent_item = $woocommerce->cart->cart_contents[ $cart_item_data[ 'bundled_by' ] ];

				if ( ! empty( $parent_item ) ) {
					if ( ! in_array( $item_cart_key, $GLOBALS[ 'bundled_items' ] ) )
						$GLOBALS[ 'bundled_items' ][] = $item_cart_key;
				}
			}

		}

		/**
		 * Replaces add_to_cart button url with something more appropriate.
		 **/
		function woo_bundles_loop_add_to_cart_url( $url ) {

			global $product;

			if ( $product->is_type( 'bundle' ) )
				return $product->add_to_cart_url();

			return $url;
		}

		/**
		 * Adds product_type_simple class for Ajax add to cart when all items are simple.
		 **/
		function woo_bundles_add_to_cart_class( $class ) {

			global $product;

			if ( $product->is_type( 'bundle' ) ) {

				if ( ! empty( $product->available_bundle_variations ) ) {

					return '';

				} else {

					return $class . ' product_type_simple';
				}
			}

			return $class;
		}

		/**
		 * Replaces add_to_cart text with something more appropriate.
		 **/
		function woo_bundles_add_to_cart_text( $text ) {

			global $product;

			if ( $product->is_type( 'bundle' ) )
				return $product->add_to_cart_text();

			return $text;
		}

		/**
		 * Adds QuickView support
		 */
		function woo_bundles_loop_add_to_cart_link( $link, $product ) {

			if ( $product->is_type( 'bundle' ) ) {

				if ( $product->is_in_stock() && $product->all_items_in_stock && empty( $product->available_bundle_variations ) )
					return str_replace( 'product_type_bundle', 'product_type_bundle product_type_simple', $link );
				else
					return str_replace( 'add_to_cart_button', '', $link );
			}

			return $link;
		}

		/**
		 * Do not show bundles or bundled items, depending on the chosen pricing method
		 **/
		function woo_bundles_cart_widget_filter( $show, $cart_item, $cart_item_key ) {

			global $woocommerce;

			if ( isset( $cart_item['bundled_by'] ) ) {
				// not really necessary since we know its going to be there
				$bundle_key = $woocommerce->cart->find_product_in_cart( $cart_item['bundled_by'] );
				if ( ! empty( $bundle_key ) ) {
					$product_id = $woocommerce->cart->cart_contents[ $bundle_key ]['product_id'];
					if ( get_post_meta( $product_id, '_per_product_pricing_active', true ) == 'no' )
						return false;
				}
			}

			if ( !isset( $cart_item['bundled_by'] ) && isset( $cart_item['stamp'] ) ) {
				if ( get_post_meta( $cart_item['product_id'], '_per_product_pricing_active', true ) == 'yes' )
						return false;
			}

			return $show;

		}

		/**
		 * Filters the reported number of cart items depending on pricing strategy
		 * - per-item price: container is subtracted
		 * - bundle price: items are subtracted
		 **/
		function woo_bundles_cart_contents_count( $count ) {

			global $woocommerce;

			$cart = $woocommerce->cart->get_cart();

			$subtract = 0;

			foreach ( $cart as $key => $value ) {

				if ( isset( $value['bundled_by'] ) ) {

					$bundle_cart_id = $value['bundled_by'];
					$bundle_product_id = $cart[$bundle_cart_id]['product_id'];

					$per_product_pricing = ( get_post_meta( $bundle_product_id, '_per_product_pricing_active', true ) == 'yes' ) ? true : false;

					if ( ! $per_product_pricing ) {
						$subtract += $value['quantity'];
					}
				}

				if ( isset( $value['stamp'] ) && !isset( $value['bundled_by'] ) ) {

					$bundle_product_id = $value['product_id'];

					$per_product_pricing = ( get_post_meta( $bundle_product_id, '_per_product_pricing_active', true ) == 'yes' ) ? true : false;

					if ( $per_product_pricing ) {
						$subtract += $value['quantity'];
					}
				}
			}

			return $count - $subtract;

		}

		/**
		 * Hide attributes if they correspond to inactive variations
		 **/
		function woo_bundles_attribute( $output, $attribute, $values ) {

			global $product;

			if ( $product->is_type( 'bundle' ) && isset( $GLOBALS[ 'listing_attributes_of' ] ) ) {

				if ( $attribute[ 'is_variation' ] ) {

					$attribute_name = $attribute[ 'name' ];

					if ( $product->bundle_data[ $GLOBALS[ 'listing_attributes_of' ] ][ 'filter_variations' ] == 'yes' && is_array( $product->filtered_variation_attributes[ $GLOBALS[ 'listing_attributes_of' ] ] ) && array_key_exists( $attribute_name, $product->filtered_variation_attributes[ $GLOBALS[ 'listing_attributes_of' ] ] ) ) {

						return wpautop( wptexturize( implode( ', ', $product->filtered_variation_attributes[ $GLOBALS[ 'listing_attributes_of' ] ][ $attribute_name ][ 'descriptions' ] ) ) );
					}

				}


			}

			return $output;
		}

		/**
		 * Change the tr class of bundled items in all templates to allow their styling
		 **/
		function woo_bundles_table_item_class( $classname, $values, $cart_item_key ) {

			if ( isset( $values['bundled_by'] ) )
				return $classname . ' bundled_table_item';
			elseif ( isset( $values['stamp'] ) )
				return $classname . ' bundle_table_item';

			return $classname;
		}

		/**
		 * Hides composite metadata
		 */
		function woo_bundles_hidden_order_item_meta( $hidden ) {
			return array_merge( $hidden, array( '_bundled_by', '_per_product_pricing' ) );
		}

		/**
		 * Add bundled_by info to order items
		 **/
		function woo_bundles_add_order_item_meta( $order_item_id, $cart_item_values ) {

			global $woocommerce;

			if ( isset( $cart_item_values['bundled_by'] ) ) {

				wc_bundles_add_order_item_meta( $order_item_id, '_bundled_by', $cart_item_values['bundled_by'] );

				// not really necessary since we know its going to be there

				$product_key = $woocommerce->cart->find_product_in_cart( $cart_item_values['bundled_by'] );

				if ( ! empty( $product_key ) ) {
					$product_name = $woocommerce->cart->cart_contents[ $product_key ]['data']->post->post_title;
					wc_bundles_add_order_item_meta( $order_item_id, __( 'Included with', 'woo-bundles' ), __( $product_name ) );
				}

			}

			if ( isset( $cart_item_values['stamp'] ) && ! isset( $cart_item_values['bundled_by'] ) ) {

				if ( $cart_item_values['data']->per_product_pricing_active == true )
					wc_bundles_add_order_item_meta( $order_item_id, '_per_product_pricing', 'yes' );
				else
					wc_bundles_add_order_item_meta( $order_item_id, '_per_product_pricing', 'no' );
			}

			if ( isset( $cart_item_values['stamp'] ) )
				wc_bundles_add_order_item_meta( $order_item_id, '_stamp', $cart_item_values['stamp'] );

		}

		/**
		 * Hide the subtotal of order-items (order-details.php) depending on the bundles's pricing strategy
		 **/
		function woo_bundles_order_item_subtotal( $subtotal, $item, $order ) {

			// If it's a bundled item
			if ( isset( $item['bundled_by'] ) ) {

				// find bundle item by its stamp
				foreach ( $order->get_items() as $order_item ) {

					if ( $order_item[ 'stamp' ] == $item[ 'stamp' ] && ! isset( $order_item[ 'bundled_by' ] ) ) {
						$bundle_product_id = $order_item[ 'product_id' ];
						$per_product_priced_bundle = $order_item[ 'per_product_pricing' ];
						break;
					}
				}

				$per_product_pricing = isset( $per_product_priced_bundle ) && ! empty( $per_product_priced_bundle ) ? $per_product_priced_bundle : get_post_meta( $bundle_product_id, '_per_product_pricing_active', true );

				if ( $per_product_pricing == 'no' && $item[ 'line_subtotal' ] == 0 )
					return '';
				else
					return  __( 'Subtotal', 'woocommerce-bto' ) . ': ' . $subtotal;
			}

			// If it's a bundle (parent item)
			if ( ! isset( $item[ 'bundled_by' ] ) && isset( $item[ 'stamp' ] ) ) {

				if ( isset( $item[ 'subtotal_updated' ] ) )
					return $subtotal;

				foreach ( $order->get_items() as $order_item ) {

					if ( $order_item['stamp'] == $item['stamp'] && isset( $order_item['bundled_by'] ) ) {

						$item[ 'line_subtotal' ] 		+= $order_item[ 'line_subtotal' ];
						$item[ 'line_subtotal_tax' ] 	+= $order_item[ 'line_subtotal_tax' ];
					}
				}

				$item[ 'subtotal_updated' ] = 'yes';

				return $order->get_formatted_line_subtotal( $item );
			}

			return $subtotal;
		}

		/**
		 * Outputs a formatted subtotal
		 */
		function format_product_subtotal( $product, $subtotal ) {

			global $woocommerce;

			$cart = $woocommerce->cart;

			$taxable = $product->is_taxable();

			// Taxable
			if ( $taxable ) {

				if ( $cart->tax_display_cart == 'excl' ) {

					$product_subtotal = wc_bundles_price( $subtotal );

					if ( $cart->prices_include_tax && $cart->tax_total > 0 )
						$product_subtotal .= ' <small class="tax_label">' . $woocommerce->countries->ex_tax_or_vat() . '</small>';

				} else {

					$product_subtotal = wc_bundles_price( $subtotal );

					if ( ! $cart->prices_include_tax && $cart->tax_total > 0 )
						$product_subtotal .= ' <small class="tax_label">' . $woocommerce->countries->inc_tax_or_vat() . '</small>';
				}

			// Non-taxable
			} else {
				$product_subtotal = wc_bundles_price( $subtotal );
			}

			return $product_subtotal;
		}

		/**
		 * Same logic as above and below
		 **/
		function woo_bundles_cart_item_price_html( $price, $values, $cart_item_key ) {

			global $woocommerce;

			if ( isset( $values[ 'bundled_by' ] ) ) {
				$bundle_cart_key = $values[ 'bundled_by' ];
				if ( $woocommerce->cart->cart_contents[ $bundle_cart_key ][ 'data' ]->per_product_pricing_active == false && $values[ 'data' ]->price == 0 )
					return '';
			}

			if ( isset( $values[ 'bundled_items' ] ) ) {

				if ( $values[ 'data' ]->per_product_pricing_active == true ) {

					$bundled_items_price 	= 0;
					$bundle_price 			= get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $values[ 'data' ]->get_price_excluding_tax() : $values[ 'data' ]->get_price_including_tax();

					foreach ( $values[ 'bundled_items' ] as $bundled_item_key ) {

						$value = $woocommerce->cart->cart_contents[ $bundled_item_key ];

						$bundled_items_price += get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $value[ 'data' ]->get_price_excluding_tax( $value[ 'quantity' ] / $values[ 'quantity' ] ) : $value[ 'data' ]->get_price_including_tax( $value[ 'quantity' ] / $values[ 'quantity' ] );
					}

					$price = $bundle_price + $bundled_items_price;
					return wc_bundles_price( $price );

				}

			}

			return $price;
		}

		/**
		 * Same logic as above in cart.php & review-order.php templates
		 **/
		function woo_bundles_item_subtotal( $subtotal, $values, $cart_item_key ) {

			global $woocommerce;

			if ( isset( $values[ 'bundled_by' ] ) ) {
				$bundle_cart_key = $values[ 'bundled_by' ];
				if ( $woocommerce->cart->cart_contents[ $bundle_cart_key ][ 'data' ]->per_product_pricing_active == false && $values[ 'data' ]->price == 0 )
					return '';
				else
					return __( 'Subtotal', 'woocommerce-bto' ) . ': ' . $subtotal;
			}

			if ( isset( $values[ 'bundled_items' ] ) ) {

				$bundled_items_price 	= 0;
				$bundle_price 			= get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $values[ 'data' ]->get_price_excluding_tax( $values[ 'quantity' ] ) : $values[ 'data' ]->get_price_including_tax( $values[ 'quantity' ] );

				foreach ( $values[ 'bundled_items' ] as $bundled_item_key ) {

					$value = $woocommerce->cart->cart_contents[ $bundled_item_key ];

					$bundled_items_price += get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $value[ 'data' ]->get_price_excluding_tax( $value[ 'quantity' ] ) : $value[ 'data' ]->get_price_including_tax( $value[ 'quantity' ] );
				}

				$subtotal = $bundle_price + $bundled_items_price;

				return $this->format_product_subtotal( $values[ 'data' ], $subtotal );

			}

			return $subtotal;
		}

		/**
		 * Self-explanatory
		 **/
		function woo_bundles_empty_price( $price, $product ) {

			if ( ( $product->product_type == 'bundle' ) && ( get_post_meta( $product->id, '_per_product_pricing_active', true ) == 'no' ) )
				return __( 'Price not set', 'woo-bundles' );

			return $price;
		}

		/**
		 * Similar to the forced-sells logic, only it takes into account bundled products that are sold individually
		 **/
		function woo_bundles_update_quantity_in_cart( $cart_item_key, $quantity = 0 ) {
			global $woocommerce;

			if ( isset( $woocommerce->cart->cart_contents[ $cart_item_key ] ) && ! empty( $woocommerce->cart->cart_contents[ $cart_item_key ] ) ) {

				if ( $quantity == 0 || $quantity < 0 ) {
					$quantity = 0;
				} else {
					$quantity = $woocommerce->cart->cart_contents[ $cart_item_key ][ 'quantity' ];
				}

				if ( isset( $woocommerce->cart->cart_contents[ $cart_item_key ][ 'stamp' ] ) && ! empty( $woocommerce->cart->cart_contents[ $cart_item_key ][ 'stamp' ] ) ) {

					// unique bundle stamp added to all bundled items & the grouping item
					$stamp = $woocommerce->cart->cart_contents[ $cart_item_key ][ 'stamp' ];

					// change the quantity of all bundled items that belong to the same bundle config
					foreach ( $woocommerce->cart->cart_contents as $key => $value ) {
						if ( isset( $value[ 'bundled_by' ] ) && isset( $value[ 'stamp' ] ) && $cart_item_key == $value[ 'bundled_by' ] && $stamp == $value[ 'stamp' ] ) {
							if ( $value[ 'data' ]->is_sold_individually() && $quantity > 0 ) {
								$woocommerce->cart->set_quantity( $key, 1 );
							} else {
								$bundle_quantity = $value[ 'stamp' ][ $value[ 'bundled_item_id' ] ][ 'quantity' ];
								$woocommerce->cart->set_quantity( $key, $quantity * $bundle_quantity );
							}
						}
					}

				}

			}

		}

		/**
		 * When the bundle is fix-priced, all bundled items' prices are set to 0
		 * When shipping is bundled, all bundled items are marked as virtual when they are added to the cart
		 * Otherwise, the bundle has already been marked as virtual in the first place
		 **/
		function woo_bundles_add_cart_item_filter( $cart_data, $id ) {

			global $woocommerce;

			$cart_contents = $woocommerce->cart->get_cart();

			if ( isset( $cart_data[ 'bundled_by' ] ) ) {

				$bundle_cart_id = $cart_data[ 'bundled_by' ];

				$per_product_pricing = ( $cart_contents[ $bundle_cart_id ][ 'data' ]->per_product_pricing_active == true ) ? true : false;
				$per_product_shipping = ( $cart_contents[ $bundle_cart_id ][ 'data' ]->per_product_shipping_active == true ) ? true : false;

				if ( $per_product_pricing == false ) {
					$cart_data['data']->price = 0;
				} else {
					if ( ! empty( $cart_data[ 'stamp' ][ $cart_data[ 'bundled_item_id' ] ][ 'discount' ] ) ) {

						$discount 		= $cart_data[ 'stamp' ][ $cart_data[ 'bundled_item_id' ] ][ 'discount' ];
						$price 			= $cart_data[ 'data' ]->price;
						$regular_price 	= $cart_data[ 'data' ]->regular_price;

						$product_regular_price 	= empty( $regular_price ) ? ( double ) $price : ( double ) $regular_price;

						$cart_data[ 'data' ]->price = empty( $discount ) || empty( $regular_price ) ? ( double ) $price : $product_regular_price * ( 100 - $discount ) / 100;

					}
				}

				if ( $per_product_shipping == false ) {
					$cart_data['data']->virtual = 'yes';
				}

			}

			return $cart_data;
		}

		/**
		 * Add all bundle-related session data to the cart
		 **/
		function woo_bundles_get_cart_data_from_session( $cart_item, $item_session_values ) {

			global $woocommerce;

			$cart_contents = $woocommerce->cart->get_cart();

			if ( isset( $item_session_values[ 'bundled_items' ] ) && ! empty( $item_session_values[ 'bundled_items' ] ) )
				$cart_item[ 'bundled_items' ] = $item_session_values[ 'bundled_items' ];

			if ( isset( $item_session_values[ 'stamp' ] ) ) {
				$cart_item[ 'stamp' ] = $item_session_values[ 'stamp' ];
			}

			if ( isset( $item_session_values[ 'bundled_by' ] ) ) {

				// load 'bundled_by' field
				$cart_item[ 'bundled_by' ] = $item_session_values[ 'bundled_by' ];

				// load product bundle post meta identifier
				$cart_item[ 'bundled_item_id' ] = $item_session_values[ 'bundled_item_id' ];

				// load dynamic pricing permission
				$cart_item[ 'dynamic_pricing_allowed' ] = $item_session_values[ 'dynamic_pricing_allowed' ];

				// now modify item depending on bundle pricing & shipping options
				$bundle_cart_id = $cart_item[ 'bundled_by' ];

				$per_product_pricing = ( $cart_contents[ $bundle_cart_id ][ 'data' ]->per_product_pricing_active == true ) ? true : false;
				$per_product_shipping = ( $cart_contents[ $bundle_cart_id ][ 'data' ]->per_product_shipping_active == true ) ? true : false;

				if ( $per_product_pricing == false ) {
					$cart_item[ 'data' ]->price = 0;
				} else {
					if ( ! empty( $cart_item[ 'stamp' ][ $cart_item[ 'bundled_item_id' ] ][ 'discount' ] ) ) {

						$discount 		= $cart_item[ 'stamp' ][ $cart_item[ 'bundled_item_id' ] ][ 'discount' ];
						$price 			= $cart_item[ 'data' ]->price;
						$regular_price 	= $cart_item[ 'data' ]->regular_price;

						$product_regular_price 	= empty( $regular_price ) ? ( double ) $price : ( double ) $regular_price;

						$cart_item[ 'data' ]->price = empty( $discount ) || empty( $regular_price ) ? ( double ) $price : $product_regular_price * ( 100 - $discount ) / 100;

					}
				}

				if ( $per_product_shipping == false ) {
					$cart_item[ 'data' ]->virtual = 'yes';
				}
			}

			return $cart_item;
		}

		/**
		 * Add "included with" metadata
		 **/
		function woo_bundles_get_item_data( $data, $cart_item ) {
			global $woocommerce;

			if ( isset ( $cart_item['bundled_by'] ) && isset ( $cart_item['stamp'] ) ) {
				// not really necessary since we know its going to be there
				$product_key = $woocommerce->cart->find_product_in_cart( $cart_item['bundled_by'] );
				if ( ! empty( $product_key ) ) {
					$product_name = get_post( $woocommerce->cart->cart_contents[ $product_key ]['product_id'] )->post_title;
					$data[] = array(
							'name'    => __( 'Included with', 'woo-bundles' ),
							'display' => __( $product_name )
					);
				}
			}

			return $data;
		}

		/**
		 * Bundled items can't be removed individually
		 **/
		function woo_bundles_cart_item_remove_link( $link, $cart_item_key ) {
			global $woocommerce;

			if ( isset ( $woocommerce->cart->cart_contents[ $cart_item_key ]['bundled_by'] ) )
				return '';

			return $link;
		}

		/**
		 * Bundled item quantities can't be changed individually
		 **/
		function woo_bundles_cart_item_quantity( $quantity, $cart_item_key ) {
			global $woocommerce;

			if ( isset ( $woocommerce->cart->cart_contents[ $cart_item_key ]['stamp'] ) ) {
				if ( isset ( $woocommerce->cart->cart_contents[ $cart_item_key ]['bundled_by'] ) )
					return $woocommerce->cart->cart_contents[ $cart_item_key ]['quantity'];
			}
			return $quantity;
		}

		/**
		 * Add-to-cart template for product bundles
		 **/
		function woo_bundles_add_to_cart() {

			global $woocommerce, $product, $post;

			// Enqueue variation scripts
			wp_enqueue_script( 'wc-add-to-cart-bundle' );

			wp_enqueue_style( 'wc-bundle-css' );

			if ( ! empty( $product->bundle_data ) )
				wc_bundles_get_template( 'single-product/add-to-cart/bundle.php', array(
					'available_variations' 		=> $product->get_available_bundle_variations(),
					'attributes'   				=> $product->get_bundle_attributes(),
					'selected_attributes' 		=> $product->get_selected_bundle_attributes(),
					'bundle_price_data' 		=> $product->get_bundle_price_data(),
					'bundled_products' 			=> $product->get_bundled_products(),
					'bundled_item_quantities' 	=> $product->get_bundled_item_quantities()
				), false, $this->woo_bundles_plugin_path() . '/templates/' );

		}

		/**
		 * Admin & Frontend scripts
		 **/
		function woo_bundles_admin_scripts() {

			global $woocommerce_bundle_helpers;

			if ( $woocommerce_bundle_helpers->is_wc_21() ) {
				wp_register_script( 'woo_bundles_writepanel', $this->woo_bundles_plugin_url() . '/assets/js/bundled-product-write-panels.js', array( 'jquery', 'jquery-ui-datepicker', 'woocommerce_admin_meta_boxes' ), $this->version );
				wp_register_style( 'woo_bundles_css', $this->woo_bundles_plugin_url() . '/assets/css/bundles-write-panels.css', array( 'woocommerce_admin_styles' ), $this->version );
			} else {
				wp_register_script( 'woo_bundles_writepanel', $this->woo_bundles_plugin_url() . '/assets/js/bundled-product-write-panels.js', array( 'jquery', 'jquery-ui-datepicker', 'woocommerce_writepanel' ), $this->version );
				wp_register_style( 'woo_bundles_css', $this->woo_bundles_plugin_url() . '/assets/css/bundles-write-panels-20.css', array( 'woocommerce_admin_styles' ), $this->version );
			}

			// Get admin screen id
			$screen = get_current_screen();

			// WooCommerce admin pages
			if ( in_array( $screen->id, array( 'product' ) ) )
				wp_enqueue_script( 'woo_bundles_writepanel' );

			if ( in_array( $screen->id, array( 'edit-product', 'product' ) ) )
				wp_enqueue_style( 'woo_bundles_css' );
		}

		function woo_bundles_frontend_scripts() {

			wp_register_script( 'wc-add-to-cart-bundle', $this->woo_bundles_plugin_url() . '/assets/js/add-to-cart-bundle.js', array( 'jquery', 'wc-add-to-cart-variation' ), $this->version, true );
			wp_register_style( 'wc-bundle-css', $this->woo_bundles_plugin_url() . '/assets/css/bundles-frontend.css', false, $this->version );
			wp_register_style( 'wc-bundle-style', $this->woo_bundles_plugin_url() . '/assets/css/bundles-style.css', false, $this->version );
			wp_enqueue_style( 'wc-bundle-style' );
		}

		/**
		 * Process, verify and save bundle data
		 **/
		function woo_bundles_process_bundle_meta( $post_id ) {

			global $woocommerce;

			// Bundle Pricing

			$date_from = ( isset( $_POST['_sale_price_dates_from'] ) ) ? $_POST['_sale_price_dates_from'] : '';
			$date_to = ( isset( $_POST['_sale_price_dates_to'] ) ) ? $_POST['_sale_price_dates_to'] : '';

			// Dates
			if ( $date_from ) :
				update_post_meta( $post_id, '_sale_price_dates_from', strtotime($date_from) );
			else :
				update_post_meta( $post_id, '_sale_price_dates_from', '' );
			endif;

			if ( $date_to ) :
				update_post_meta( $post_id, '_sale_price_dates_to', strtotime($date_to) );
			else :
				update_post_meta( $post_id, '_sale_price_dates_to', '' );
			endif;

			if ( $date_to && !$date_from ) :
				update_post_meta( $post_id, '_sale_price_dates_from', strtotime( 'NOW', current_time('timestamp') ) );
			endif;

			// Update price if on sale

			 if ( ! isset( $_POST['_sale_price'] ) )
 				$_POST['_sale_price'] = '';

	 		if ( ! isset( $_POST['_regular_price'] ) )
	 			$_POST['_regular_price'] = '';

			if ( $_POST['_sale_price'] != '' && $date_to == '' && $date_from == '' ) {
				update_post_meta( $post_id, '_price', stripslashes( $_POST['_sale_price'] ) );
			} else {
				update_post_meta( $post_id, '_price', stripslashes( $_POST['_regular_price'] ) );
			}

			if ( $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) :
				update_post_meta( $post_id, '_price', stripslashes( $_POST['_sale_price'] ) );
			endif;

			if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) :
				update_post_meta( $post_id, '_price', stripslashes( $_POST['_regular_price'] ) );
				update_post_meta( $post_id, '_sale_price_dates_from', '' );
				update_post_meta( $post_id, '_sale_price_dates_to', '') ;
			endif;


			// Per-Item Pricing

			if ( isset( $_POST['_per_product_pricing_active'] ) ) {
				update_post_meta( $post_id, '_per_product_pricing_active', 'yes' );
				delete_post_meta( $post_id, '_regular_price' );
				delete_post_meta( $post_id, '_sale_price' );
				delete_post_meta( $post_id, '_price' );
			} else {
				update_post_meta( $post_id, '_per_product_pricing_active', 'no' );
				update_post_meta( $post_id, '_regular_price', stripslashes( $_POST['_regular_price'] ) );
				update_post_meta( $post_id, '_sale_price', stripslashes( $_POST['_sale_price'] ) );
			}



			// Shipping
			// Non-Bundled (per-item) Shipping

			if ( isset( $_POST['_per_product_shipping_active'] ) ) {
				update_post_meta( $post_id, '_per_product_shipping_active', 'yes' );
				update_post_meta( $post_id, '_virtual', 'yes' );
				update_post_meta( $post_id, '_weight', '' );
				update_post_meta( $post_id, '_length', '' );
				update_post_meta( $post_id, '_width', '' );
				update_post_meta( $post_id, '_height', '' );
			} else {
				update_post_meta( $post_id, '_per_product_shipping_active', 'no' );
				update_post_meta( $post_id, '_virtual', 'no' );
				update_post_meta( $post_id, '_weight', stripslashes( $_POST['_weight'] ) );
				update_post_meta( $post_id, '_length', stripslashes( $_POST['_length'] ) );
				update_post_meta( $post_id, '_width', stripslashes( $_POST['_width'] ) );
				update_post_meta( $post_id, '_height', stripslashes( $_POST['_height'] ) );
			}


			// Process Bundled Product Configuration
			$bundle_data 			= array();
			$ordered_bundle_data 	= array();

			if ( isset( $_POST[ 'bundled_ids' ] ) ) {

				// Now start saving new data
				$bundled_ids = array();
				$times = array();
				$save_defaults = array();

				$ids = array_map ( 'absint', $_POST[ 'bundled_ids' ] );

				foreach ( $ids as $id ) {

					$terms 			= get_the_terms( $id, 'product_type' );
					$product_type 	= ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

					if ( ( $id && $id > 0 ) && ( $product_type == 'simple' || $product_type == 'variable' ) && ( $post_id != $id ) ) {

						// only allow multiple instances of variable items
						if ( in_array( $id, $bundled_ids ) && $product_type != 'variable' )
							continue;

						// allow bundling the same variable item id multiple times by adding a suffix
						if ( ! isset( $times[ $id ] ) ) {

							$times[ $id ] = 1;
							$val = $id;

						}
						else {

							// only allow multiple instances of non-sold-individually items
							if ( get_post_meta( $id, '_sold_individually', true ) == 'yes' ) {

								wc_bundles_add_admin_error( sprintf( __( '\'%s\' (#%s) is sold individually and cannot be bundled more than once.', 'woo-bundles' ), get_the_title( $id ), $id ) );
								continue;

							}

							$times[ $id ] += 1;
							$val = $id . '_' . $times[$id];

						}

						$bundled_ids[] = $val;

						$bundle_data[ $val ] = array();

						$bundle_data[ $val ][ 'product_id' ] = $id;

						// Save thumbnail preferences first
						if ( isset( $_POST[ 'hide_thumbnail_' . $val ] ) ) {
							$bundle_data[ $val ][ 'hide_thumbnail' ] = 'yes';
						} else {
							$bundle_data[ $val ][ 'hide_thumbnail' ] = 'no';
						}

						// Save title preferences
						if ( isset( $_POST[ 'override_title_'.$val ] ) ) {
							$bundle_data[ $val ][ 'override_title' ] = 'yes';
							$bundle_data[ $val ][ 'product_title' ] = isset( $_POST[ 'product_title_' . $val ] ) ? $_POST[ 'product_title_' . $val ] : '';
						} else {
							$bundle_data[ $val ][ 'override_title' ] = 'no';
						}

						// Save description preferences
						if ( isset( $_POST[ 'override_description_' . $val ] ) ) {
							$bundle_data[ $val ][ 'override_description' ] = 'yes';
							$bundle_data[ $val ][ 'product_description' ] = isset( $_POST[ 'product_description_' . $val ] ) ? wp_kses_post( stripslashes( $_POST[ 'product_description_' . $val ] ) ) : '';
						} else {
							$bundle_data[ $val ][ 'override_description' ] = 'no';
						}

						// Save quantity data
						if ( isset( $_POST[ 'bundle_quantity_' . $val ] ) ) {

							if ( is_numeric( $_POST[ 'bundle_quantity_' . $val ] ) ) {

								$quantity = (int) $_POST[ 'bundle_quantity_' . $val ];
								if ( $quantity > 0 && $_POST[ 'bundle_quantity_'.$val ] - $quantity == 0 ) {

									if ( get_post_meta( $id, '_downloadable', true ) == 'yes' && get_post_meta( $id, '_virtual', true ) == 'yes' && get_option( 'woocommerce_limit_downloadable_product_qty' ) == 'yes' && $quantity != 1 ) {

										wc_bundles_add_admin_error( sprintf( __('\'%s\' (#%s) is sold individually and cannot be bundled more than once.', 'woo-bundles'), get_the_title( $id ), $id ) );
										$bundle_data[ $val ][ 'bundle_quantity' ] = 1;

									}
									else {
										$bundle_data[ $val ][ 'bundle_quantity' ] = (int) $_POST[ 'bundle_quantity_'.$val ];
									}
								}
								else
									wc_bundles_add_admin_error( sprintf( __('The quantity you entered for \'%s%s\' (#%s) was not valid and has been reset. Please enter a positive integer value.', 'woo-bundles'), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );
							}
						} else {
							// if its not there, it means the product was just added
							$bundle_data[ $val ][ 'bundle_quantity' ] = 1;
						}

						// Save sale price data
						if ( isset( $_POST[ 'bundle_discount_' . $val ] ) ) {

							if ( is_numeric( $_POST[ 'bundle_discount_' . $val ] ) ) {

								$discount = ( float ) wc_bundles_format_decimal( $_POST[ 'bundle_discount_' . $val ] );

								if ( $discount < 0 || $discount > 100 ) {
									wc_bundles_add_admin_error( sprintf( __( 'The discount value you entered for \'%s%s\' (#%s) was not valid and has been reset. Please enter a positive number between 0-100.', 'woo-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );
									$bundle_data[ $val ][ 'bundle_discount' ] = '';
								} else {
									$bundle_data[ $val ][ 'bundle_discount' ] = $discount;
								}
							} else {
								$bundle_data[ $val ][ 'bundle_discount' ] = '';
							}
						} else {
							$bundle_data[ $val ][ 'bundle_discount' ] = '';
						}

						// Save data related to variable items
						if ( $product_type == 'variable' ) {

							// Save variation filtering options
							if ( isset( $_POST[ 'filter_variations_' . $val ] ) ) {

								if ( isset( $_POST[ 'allowed_variations' ][ $val ] ) && count( $_POST[ 'allowed_variations' ][ $val ] ) > 0 ) {

									$bundle_data[ $val ][ 'filter_variations' ] = 'yes';

									$bundle_data[ $val ][ 'allowed_variations' ] = $_POST[ 'allowed_variations' ][ $val ];

									if ( isset( $_POST[ 'hide_filtered_variations_' . $val ] ) )
										$bundle_data[ $val ][ 'hide_filtered_variations' ] = 'yes';
									else
										$bundle_data[ $val ][ 'hide_filtered_variations' ] = 'no';
								}
								else {
									$bundle_data[ $val ][ 'filter_variations' ] = 'no';
									wc_bundles_add_admin_error( __('Please select at least one variation for each bundled product you want to filter.', 'woo-bundles') );
								}
							} else {
								$bundle_data[ $val ][ 'filter_variations' ] = 'no';
							}

							// Save defaults options
							if ( isset( $_POST[ 'override_defaults_' . $val ] ) ) {

								if ( isset( $_POST[ 'default_attributes' ][ $val ] ) ) {

									// if filters are set, check that the selections are valid

									if ( isset( $_POST[ 'filter_variations_' . $val ] ) && isset( $_POST[ 'allowed_variations' ][ $val ] ) ) {

										$allowed_variations = $_POST[ 'allowed_variations' ][ $val ];

										// the array to store all valid attribute options of the iterated product
										$filtered_attributes = array();

										// populate array with valid attributes
										foreach ( $allowed_variations as $variation ) {

											$product_custom_fields = get_post_custom( $variation );

											foreach ( $product_custom_fields as $name => $value ) :

												if ( ! strstr( $name, 'attribute_' ) ) continue;
												$attribute_name = substr( $name, strlen('attribute_') );

												// ( populate array )
												if ( ! isset( $filtered_attributes[ $attribute_name ] ) ) {
													$filtered_attributes[ $attribute_name ][] = $value[0];
												} elseif ( ! in_array( $value[0], $filtered_attributes[ $attribute_name ] ) ) {
													$filtered_attributes[ $attribute_name ][] = $value[0];
												}

											endforeach;

										}

										// check validity
										foreach ( $_POST[ 'default_attributes' ][ $val ] as $name => $value ) {
											if ( $value == '' ) continue;
											if ( ! in_array( $value, $filtered_attributes[ sanitize_title( $name ) ] ) && ! in_array( '', $filtered_attributes[ sanitize_title( $name ) ] ) ) {

												// set option to "Any"
												$_POST[ 'default_attributes' ][ $val ][ sanitize_title( $name ) ] = '';

												// throw an error
												wc_bundles_add_admin_error( sprintf( __( 'The \'%s\' default option that you selected for \'%s%s\' (#%s) is inconsistent with the set of active variations. Always double-check your preferences before saving, and always save any changes made to the variation filters before choosing new defaults.', 'woo-bundles' ), ucwords( wc_bundles_attribute_label($name) ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );

												continue;
											}
										}

									}

									$bundle_data[ $val ][ 'override_defaults' ] = 'yes';
								}
							} else {

								$bundle_data[ $val ][ 'override_defaults' ] = 'no';
							}
						}

						// Save visibility preferences
						if ( isset( $_POST[ 'visibility_' . $val ] ) ) {

							if ( $_POST[ 'visibility_' . $val ] == 'visible' ) {

								$bundle_data[ $val ][ 'visibility' ] = 'visible';

							} elseif ( $_POST[ 'visibility_' . $val ] == 'hidden' ) {

								if ( $product_type == 'variable' ) {

									if ( isset( $_POST[ 'default_attributes' ][ $val ] ) ) {

										foreach ( $_POST[ 'default_attributes' ][ $val ] as $default_name => $default_value ) {
											if ( ! $default_value ) {
												$_POST[ 'visibility_' . $val ] = 'visible';
												wc_bundles_add_admin_error( sprintf( __( '\'%s%s\' (#%s) cannot be hidden unless all default options of the product are defined.', 'woo-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );
											}
										}

										$bundle_data[ $val ][ 'visibility' ] = $_POST[ 'visibility_' . $val ];

									} else {
										$bundle_data[ $val ][ 'visibility' ] = 'visible';
									}

								} else {
									$bundle_data[ $val ][ 'visibility' ] = 'hidden';
								}

							}

						} else {

							$bundle_data[ $val ][ 'visibility' ] = 'visible';
						}

					}

				}

				if ( isset( $_POST[ 'default_attributes' ] ) ) {
					// take out empty attributes (any set) to prepare for saving

					foreach ( $_POST[ 'default_attributes' ] as $item_id => $defaults ) {

						if ( ! isset( $bundle_data[ $item_id ] ) )
							continue;

						$bundle_data[ $item_id ][ 'bundle_defaults' ] = array();

						foreach ( $defaults as $default_name => $default_value ) {
							if ( $default_value ) {
								$bundle_data[ $item_id ][ 'bundle_defaults' ][ sanitize_title( $default_name ) ] = $default_value;
							}
						}
					}
				}


				// Ordering

				if ( isset( $_POST[ 'bundle_order' ] ) ) {

					$posted_ids = $ids;

					$sort_data 	= array_map ( 'stripslashes', $_POST[ 'bundle_order' ] );
					asort( $sort_data );

					foreach ( $sort_data as $sorted_item_id => $item_order ) {

						if ( isset( $bundle_data[ $sorted_item_id ] ) ) {
							$ordered_bundle_data[ $sorted_item_id ] = $bundle_data[ $sorted_item_id ];
						}
					}

					foreach ( $bundle_data as $item_id => $item_data ) {

						if ( ! isset( $ordered_bundle_data[ $item_id ] ) ) {
							$ordered_bundle_data[ $item_id ] = $bundle_data[ $item_id ];
						}
					}

				} else {

					$ordered_bundle_data = $bundle_data;
				}

				update_post_meta( $post_id, '_bundle_data', $ordered_bundle_data );

			} else {

				delete_post_meta( $post_id, '_bundle_data' );

				wc_bundles_add_admin_error( __( 'Please add at least one product to the bundle before publishing. To add products, click on the Bundled Products tab.', 'woo-bundles' ) );

				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );

				return;
			}

		}

		/**
		 * Add 'bundle' type to the menu
		 **/
		function woo_bundles_product_selector_filter( $options ) {
			$options['bundle'] = __( 'Product bundle', 'woo-bundles' );
			return $options;
		}

		/**
		 * Aadd Bundled Products write panel tab
		 **/
		function woo_bundles_product_write_panel_tab() {
			echo '<li class="bundled_product_tab show_if_bundle bundled_product_options linked_product_options"><a href="#bundled_product_data">'.__( 'Bundled Products', 'woo-bundles' ).'</a></li>';
		}

		/**
		 * Aadd Bundled Products stock note
		 **/
		function woo_bundles_stock_group() {
			global $woocommerce, $post; ?>

			<p class="form-field show_if_bundle bundle_stock_msg">
				<?php _e( 'Note', 'woo-bundles' ); echo '<img class="help_tip" data-tip="' . __( 'By default, the sale of a product within a bundle has the same effect on its stock as an individual sale. There are no separate inventory settings for bundled items. However, this pane can be used to enable stock management on a bundle level. This can be very useful for allocating bundle stock quota, or for keeping track of bundled item sales.', 'woo-bundles' ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />'; ?>
			</p><?php

		}

		/**
		 * Product bundle options for post-1.6.2 product data section
		 **/
		function woo_bundles_type_options( $options ) {

			$options['per_product_shipping_active'] = array(
				'id' 			=> '_per_product_shipping_active',
				'wrapper_class' => 'show_if_bundle',
				'label' 		=> __( 'Non-Bundled Shipping', 'woo-bundles' ),
				'description' 	=> __( 'If your bundle consists of items that are assembled or packaged together, leave the box un-checked and just define the shipping properties of the product bundle below. If, however, the bundled items are shipped individually, their shipping properties must be retained. In this case, the box must be checked. \'Non-Bundled Shipping\' should also be selected when the bundle consists of virtual items, which are not shipped.', 'woo-bundles' ),
				'default'		=> 'no'
			);

			$options['per_product_pricing_active'] = array(
				'id' 			=> '_per_product_pricing_active',
				'wrapper_class' => 'show_if_bundle bundle_pricing',
				'label' 		=> __( 'Per-Item Pricing', 'woo-bundles' ),
				'description' 	=> __( 'When enabled, the bundle will be priced per-item, based on standalone item prices and tax rates.', 'woo-bundles' ),
				'default'		=> 'no'
			);

			return $options;
		}

		/**
		 * Write panel for Product Bundles
		 **/
		function woo_bundles_product_write_panel() {
			global $woocommerce_bundle_helpers, $woocommerce, $post, $wpdb;

			?>
				<div id="bundled_product_data" class="panel woocommerce_options_panel">

					<div class="options_group">

						<div class="wc-bundled_products">

							<div class="bundled_products_info">

							<?php _e( 'Bundled Products', 'woo-bundles' ); echo '<img class="help_tip" data-tip="' . __( 'Select the products that you want to include in your bundle, kit, or assembly. Any simple or variable product can be added - physical, or downloadable. Important: v2.0 has introduced the ability to bundle multiple instances of the same variable product and configure each instance separately - for details, check out the online documentation.', 'woo-bundles' ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />'; ?>

							</div>

							<div class="bundled_products_selector">

								<select id="bundled_ids" name="bundled_ids[]" class="ajax_chosen_select_products" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woo-bundles' ); ?>">
									<?php
										$bundle_data = maybe_unserialize( get_post_meta( $post->ID, '_bundle_data', true ) );

										$bundled_variable_num = 0;

										if ( ! empty( $bundle_data  ) ) {

											foreach ( $bundle_data as $item_id => $item_data ) {

												$product_id = $item_data[ 'product_id' ];

												$sep = explode( '_', $item_id );

												$terms 			= get_the_terms( $product_id, 'product_type' );
												$product_type 	= ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

												if ( $product_type == 'variable' ) { $bundled_variable_num++; }

												$title 	= get_the_title( $product_id ) . ( (string) $product_id != (string) $item_id ? ' #' . $sep[1] : '' );
												$sku 	= get_post_meta( $product_id, '_sku', true );

												if ( ! $title )
													continue;

												if ( isset( $sku ) && $sku ) $sku = ' (SKU: ' . $sku . ')';
												echo '<option value="' . $product_id . '" selected="selected">' . $title . $sku . '</option>';
											}
										}
									?>
								</select>
							</div>
						</div>

					</div>
					<div class="options_group wc-metaboxes-wrapper">

						<?php
						if ( ! empty( $bundle_data ) ) {
						?>

						<p class="toolbar">
							<?php _e( 'Bundle Configuration', 'woo-bundles' ); echo '<img class="help_tip" data-tip="' . __( 'Use these settings to configure basic bundled product parameters such as quantities / discounts, apply variation filters and default option overrides and fine-tune the appearance of your Bundle.', 'woo-bundles' ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />'; ?>
							<a href="#" class="close_all"><?php _e('Close all', 'woocommerce'); ?></a>
							<a href="#" class="expand_all"><?php _e('Expand all', 'woocommerce'); ?></a>
						</p>

						<?php
						}
						?>

						<div class="wc-bundled-items wc-metaboxes">

							<?php
							if ( ! empty( $bundle_data ) ) {

								$sorting = 0;

								foreach ( $bundle_data as $item_id => $item_data ) {

									$allowed_variations = isset( $item_data[ 'allowed_variations' ] ) ? $item_data[ 'allowed_variations' ] : '';
									$default_attributes = isset( $item_data[ 'bundle_defaults' ] ) ? $item_data[ 'bundle_defaults' ] : '';

									$sep 		= explode( '_', $item_id );
									$product_id = $item_data[ 'product_id' ];

									$title 	= get_the_title( $product_id ) . ( (string) $product_id != (string) $item_id ? ' #' . $sep[1] : '' );
									$sku 	= get_post_meta( $product_id, '_sku', true );

									if ( isset( $sku ) && $sku ) $sku = ' &ndash; SKU: ' . $sku;

									if ( ! $title )
										continue;
									?>

									<div class="wc-bundled-item wc-metabox closed" rel="<?php echo $sorting; ?>">
										<h3>
											<div class="handlediv" title="<?php echo __( 'Click to toggle', 'woocommerce' ); ?>"></div>
											<strong class="item-title"><?php echo $title . ' &ndash; #'. $product_id . ( ! empty( $sku ) ? $sku : '' ); ?></strong>
										</h3>
										<div class="item-data wc-metabox-content">
											<input type="hidden" name="bundle_order[<?php echo $item_id; ?>]" class="bundled_item_position" value="<?php echo $sorting; ?>" />
											<?php
												$bundled_product = get_product( $product_id );

												if ( $bundled_product->is_type( 'variable' ) ) : ?>

													<div class="filtering">

													<?php

													$filtered = ( $item_data[ 'filter_variations' ] == 'yes' ) ? true : false;

													?>
													<?php echo '<p class="tips"><a class="tips" data-tip="' . __( 'Check to enable only a subset of the available variations.', 'woo-bundles' ) . '">[?]</a></p>'; ?>

													<?php woocommerce_wp_checkbox( array( 'id' => 'filter_variations_' . $item_id, 'value' => $item_data[ 'filter_variations' ], 'wrapper_class' => 'filter_variations', 'label' => __( 'Filter Variations', 'woo-bundles' ), 'description' => '' ) ); ?>

													</div>


													<div class="bundle_variation_filters indented">

														<select multiple="multiple" name="allowed_variations[<?php echo $item_id; ?>][]" style="width: 450px; display: none; " data-placeholder="Choose variations…" title="Variations" class="chosen_select" > <?php

														$args = array(
															'post_type'		=> 'product_variation',
															'post_status' 	=> array( 'private', 'publish' ),
															'numberposts' 	=> -1,
															'orderby' 		=> 'menu_order',
															'order' 		=> 'asc',
															'post_parent' 	=> $product_id,
															'fields' 		=> 'ids'
														);

														$variations = get_posts( $args );
														$attributes = maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );

														// filtered variation attributes
														$filtered_attributes = array();

														foreach ( $variations as $variation ) {

															$description = '';

															$variation_data = get_post_meta( $variation );

															foreach ( $attributes as $attribute ) {

																// Only deal with attributes that are variations
																if ( ! $attribute[ 'is_variation' ] )
																	continue;

																// Get current value for variation (if set)
																$variation_selected_value = isset( $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ][0] ) ? $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ][0] : '';

																// Name will be something like attribute_pa_color
																$description_name 	= esc_html( wc_bundles_attribute_label( $attribute[ 'name' ] ) );
																$description_value 	= __( 'Any', 'woocommerce' ) . ' ' . $description_name;

																// Get terms for attribute taxonomy or value if its a custom attribute
																if ( $attribute[ 'is_taxonomy' ] ) {

																	$post_terms = wp_get_post_terms( $product_id, $attribute[ 'name' ] );

																	foreach ( $post_terms as $term ) {

																		if ( $variation_selected_value == $term->slug ) {
																			$description_value = apply_filters( 'woocommerce_variation_option_name', esc_html( $term->name ) );
																		}

																		if ( $variation_selected_value == $term->slug || $variation_selected_value == '' ) {
																			if ( $filtered && is_array( $allowed_variations ) && in_array( $variation, $allowed_variations ) ) {
																				if ( ! isset( $filtered_attributes[ $attribute[ 'name' ] ] ) ) {
																					$filtered_attributes[ $attribute[ 'name' ] ] [] = $variation_selected_value;
																				} elseif ( ! in_array( $variation_selected_value, $filtered_attributes[ $attribute[ 'name' ] ] ) ) {
																					$filtered_attributes[ $attribute[ 'name' ] ] [] = $variation_selected_value;
																				}
																			}
																		}

																	}

																} else {

																	$options = array_map( 'trim', explode( wc_bundles_delimiter(), $attribute[ 'value' ] ) );

																	foreach ( $options as $option ) {
																		if ( sanitize_title( $variation_selected_value ) == sanitize_title( $option ) ) {
																			$description_value = esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) );
																		}

																		if ( sanitize_title( $variation_selected_value ) == sanitize_title( $option ) || $variation_selected_value == '' ) {
																			if ( $filtered && is_array( $allowed_variations ) && in_array( $variation, $allowed_variations ) ) {
																				if ( ! isset( $filtered_attributes[ $attribute[ 'name' ] ] ) ) {
																					$filtered_attributes[ $attribute[ 'name' ] ] [] = sanitize_title( $variation_selected_value );
																				} elseif ( ! in_array( sanitize_title( $variation_selected_value ), $filtered_attributes[ $attribute[ 'name' ] ] ) ) {
																					$filtered_attributes[ $attribute[ 'name' ] ] [] = sanitize_title( $variation_selected_value );
																				}
																			}
																		}

																	}

																}

																$description .= $description_name . ': ' . $description_value . ', ';

															}

															if ( is_array( $allowed_variations ) && in_array( $variation, $allowed_variations ) )
																$selected = 'selected="selected"';
															else $selected = '';

															echo '<option value="' . $variation . '" ' . $selected . '>#' . $variation . ' - ' . rtrim( $description, ', ') . '</option>';
														} ?>

														</select>


														<?php
															//woocommerce_wp_checkbox( array( 'id' => 'hide_filtered_variations_'.$item_id, 'wrapper_class' => 'hide_filtered_variations', 'label' => __('Hide Filtered-Out Options', 'woo-bundles'), 'description' => '<img class="help_tip" data-tip="' . __('Check to remove any filtered-out variation options from this item\'s drop-downs. If you leave the box unchecked, the options corresponding to filtered-out variations will be disabled but still visible.', 'woo-bundles') .'" src="'.$woocommerce->plugin_url().'/assets/images/help.png" />' ) );
														?>

													</div>


													<div class="defaults">

														<?php echo '<p class="tips"><a class="tips" data-tip="' . __( 'In effect for this bundle only. The available options are in sync with the filtering settings above. Always save any changes made above before configuring this section.', 'woo-bundles' ) .'" src="'.$woocommerce->plugin_url().'/assets/images/help.png" />[?]</a></p>'; ?>

														<?php woocommerce_wp_checkbox( array( 'id' => 'override_defaults_' . $item_id, 'value' => $item_data[ 'override_defaults' ], 'wrapper_class' => 'override_defaults', 'label' => __( 'Override Default Selections', 'woo-bundles' ), 'description' => '' ) ); ?>

													</div>

													<div class="bundle_selection_defaults indented"> <?php

															foreach ( $attributes as $attribute ) {

																// Only deal with attributes that are variations
																if ( ! $attribute['is_variation'] )
																	continue;

																// Get current value for variation (if set)
																$variation_selected_value = ( isset( $default_attributes[ sanitize_title( $attribute[ 'name' ] ) ] ) ) ? $default_attributes[ sanitize_title( $attribute[ 'name' ] ) ] : '';

																// Name will be something like attribute_pa_color
																echo '<select name="default_attributes[' . $item_id . '][' . sanitize_title( $attribute['name'] ).']"><option value="">'.__( 'No default', 'woocommerce' ) . ' ' . wc_bundles_attribute_label( $attribute['name'] ).'&hellip;</option>';

																// Get terms for attribute taxonomy or value if its a custom attribute
																if ( $attribute[ 'is_taxonomy' ] ) {

																	$post_terms = wp_get_post_terms( $product_id, $attribute[ 'name' ] );

																	sort( $post_terms );
																	foreach ( $post_terms as $term ) {
																		if ( $filtered && isset( $filtered_attributes[ $attribute[ 'name' ] ] ) && ! in_array( '', $filtered_attributes[ $attribute[ 'name' ] ] ) ) {
																			if ( ! in_array( $term->slug, $filtered_attributes[ $attribute[ 'name' ] ] ) )
																				continue;
																		}
																		echo '<option ' . selected( $variation_selected_value, $term->slug, false ) . ' value="' . esc_attr( $term->slug ) . '">' . apply_filters( 'woocommerce_variation_option_name', esc_html( $term->name ) ) . '</option>';
																	}

																} else {

																	$options = array_map( 'trim', explode( wc_bundles_delimiter(), $attribute[ 'value' ] ) );

																	sort( $options );
																	foreach ( $options as $option ) {
																		if ( $filtered && isset( $filtered_attributes[ $attribute[ 'name' ] ] ) && ! in_array( '', $filtered_attributes[ $attribute['name'] ] ) ) {
																			if ( ! in_array( sanitize_title( $option ), $filtered_attributes[ $attribute['name'] ] ) )
																				continue;
																		}
																		echo '<option ' . selected( sanitize_title( $variation_selected_value ), sanitize_title( $option ), false ) . ' value="' . esc_attr( sanitize_title( $option ) ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
																	}

																}

																echo '</select>';
															}
															?>
													</div>
												<?php
												endif;

												$item_quantity = $item_data[ 'bundle_quantity' ];

												if ( empty( $item_quantity ) )
													$item_quantity = 1;

												$item_discount = $item_data[ 'bundle_discount' ];

												$per_product_pricing = get_post_meta( $post->ID, '_per_product_pricing_active', true ) == 'yes' ? true : false;
											?>

											<div class="quantity">

												<?php echo '<p class="tips"><a class="tips" data-tip="' . __( 'Defines the quantity of this bundled product.', 'woo-bundles' ) .'" src="'.$woocommerce->plugin_url().'/assets/images/help.png" />[?]</a></p>'; ?>

												<p class="form-field">
													<label><?php echo __( 'Quantity', 'woocommerce' ); ?></label>
													<input type="number" class="bundle_quantity" size="5" name="bundle_quantity_<?php echo $item_id; ?>" value="<?php echo $item_quantity; ?>" step="any" min="0" />
												</p>

											</div>

											<div class="discount">

												<?php echo '<p class="tips"><a class="tips" data-tip="' . __( 'Discount applied to the regular price of this bundled product when Per-Item Pricing is active. Note: If a Discount is applied to a bundled product which has a sale price defined, the sale price will be overridden.', 'woo-bundles' ) .'" src="'.$woocommerce->plugin_url().'/assets/images/help.png" />[?]</a></p>'; ?>

												<p class="form-field">
													<label><?php echo __( 'Discount %', 'woocommerce' ); ?></label>
													<input type="text" <?php echo $per_product_pricing ? '' : 'disabled="disabled"'; ?> class="input-text bundle_discount wc_input_decimal" size="5" name="bundle_discount_<?php echo $item_id; ?>" value="<?php echo $item_discount; ?>" />
												</p>

											</div>

											<div class="item_visibility">

												<?php echo '<p class="tips"><a class="tips" data-tip="' . __( 'Hides this bundled product from the front-end. This option will only work if you have defined default selections above.', 'woo-bundles' ) .'" src="'.$woocommerce->plugin_url().'/assets/images/help.png" />[?]</a></p>'; ?>

												<label for="item_visibility"><?php _e( 'Front-End Visibility', 'woo-bundles' ); ?></label>
												<select name="visibility_<?php echo $item_id; ?>">
													<?php
													$visible = ( $item_data[ 'visibility' ] == 'hidden' ) ? false : true;
													echo '<option ' . selected( $visible, true, false ) .' value="visible">' . __( 'Visible', 'woo-bundles' ) . '</option>';
													echo '<option ' . selected( $visible, false, false ) .' value="hidden">' . __( 'Hidden', 'woo-bundles' ) . '</option>';
													?>
												</select>
											</div>

											<div class="images">

												<?php echo '<p class="tips"><a class="tips" data-tip="' . __( 'Check this option to hide the thumbnail image of this bundled product.', 'woo-bundles' ) .'" src="'.$woocommerce->plugin_url().'/assets/images/help.png" />[?]</a></p>'; ?>

												<?php woocommerce_wp_checkbox( array( 'id' => 'hide_thumbnail_' . $item_id, 'value' => $item_data[ 'hide_thumbnail' ], 'wrapper_class' => 'hide_thumbnail', 'label' => __( 'Hide Product Thumbnail', 'woo-bundles' ), 'description' => '' ) ); ?>

											</div>

											<div class="override_title">

												<?php echo '<p class="tips"><a class="tips" data-tip="' . __( 'Check this option to override the default product title.', 'woo-bundles' ) .'" src="'.$woocommerce->plugin_url().'/assets/images/help.png" />[?]</a></p>'; ?>

												<?php woocommerce_wp_checkbox( array( 'id' => 'override_title_' . $item_id, 'value' => $item_data[ 'override_title' ], 'wrapper_class' => 'override_title', 'label' => __( 'Override Title', 'woo-bundles' ), 'description' => '' ) ); ?>

												<div class="custom_title indented">

													<?php woocommerce_wp_text_input( array( 'id' => 'product_title_' . $item_id, 'value' => isset( $item_data[ 'product_title' ] ) ? $item_data[ 'product_title' ] : '', 'class' => 'product_title', 'label' => __( 'Product Title:', 'woo-bundles' ) ) ); ?>

												</div>

											</div>


											<div class="override_description">

												<?php echo '<p class="tips"><a class="tips" data-tip="' . __( 'Check this option to override the default short product description.', 'woo-bundles' ) .'" src="'.$woocommerce->plugin_url().'/assets/images/help.png" />[?]</a></p>'; ?>

												<?php woocommerce_wp_checkbox( array( 'id' => 'override_description_' . $item_id, 'value' => $item_data[ 'override_description' ], 'wrapper_class' => 'override_description', 'label' => __( 'Override Short Description', 'woo-bundles' ), 'description' => '' ) ); ?>

												<div class="custom_description indented">

													<?php woocommerce_wp_textarea_input(  array( 'id' => 'product_description_' . $item_id, 'value' => isset( $item_data[ 'product_description' ] ) ? $item_data[ 'product_description' ] : '', 'class' => 'product_description', 'label' => __( 'Product Short Description:', 'woo-bundles' ) ) ); ?>

												</div>

											</div>


										</div>
									</div>
								<?php
								$sorting++;
								}
							} else { ?>
								<div id="bundle-options-message" class="inline woocommerce-message">
									<div class="squeezer">
										<?php echo $woocommerce_bundle_helpers->is_wc_21() ? '<p>' : '<h4>'; _e( 'To configure additional options, first select some products and then save your changes.', 'woocommerce-bto' ); echo $woocommerce_bundle_helpers->is_wc_21() ? '</p>' : '</h4>'; ?>
										<p class="submit"><a class="button-primary" href="<?php echo 'http://docs.woothemes.com/document/bundles'; ?>" target="_blank"><?php _e( 'Learn more', 'woocommerce' ); ?></a></p>
									</div>
								</div>
								<?php
							}
							?>
						</div>
					</div> <!-- options group -->
				</div>
				<?php
		}

		/**
		 * Check stock before attempting to call the add_to_cart function
		 * Some double checking happens, but it's better than partially adding items to the cart
		 **/
		function validate_stock( $product_id, $variation_id, $quantity, $exclude_cart, $silent ) {

			global $woocommerce;

			if ( $variation_id > 0 ) {
				$product_data = get_product( $variation_id, array( 'product_type' => 'variation') );
			} else {
				$product_data = get_product( $product_id, array( 'product_type' => 'simple') );
			}

			// Stock check - only check if we're managing stock and backorders are not allowed.
			if ( ! $product_data->is_in_stock() ) {
				if ( ! $silent )
					wc_bundles_add_notice( sprintf( __( 'You cannot add this product to the cart since "%s" is out of stock.', 'woo-bundles' ), $product_data->get_title() ), 'error' );
				return false;
			}
			elseif ( ! $product_data->has_enough_stock( $quantity ) ) {
				if ( ! $silent )
					wc_bundles_add_notice( sprintf( __( 'You cannot add that amount to the cart since there is not enough stock of "%s". We have %s in stock.', 'woo-bundles' ), $product_data->get_title(), $product_data->get_stock_quantity() ), 'error' );
				return false;
			}

			// Stock check - this time accounting for whats already in-cart.
			if ( $exclude_cart )
				return true;

			$product_qty_in_cart = $woocommerce->cart->get_cart_item_quantities();

			if ( $product_data->managing_stock() ) {

				// Variations
				if ( $variation_id && $product_data->variation_has_stock ) {

					if ( isset( $product_qty_in_cart[ $variation_id ] ) && ! $product_data->has_enough_stock( $product_qty_in_cart[ $variation_id ] + $quantity ) ) {
						if ( ! $silent )
							wc_bundles_add_notice( sprintf( __( '<a href="%s" class="button">%s</a>You cannot add that amount to the cart since there is not enough stock of "%s" &mdash; we have %s in stock and you already have %s in your cart.', 'woo-bundles' ), get_permalink(woocommerce_get_page_id('cart')), __( 'View Cart &rarr;', 'woocommerce' ), $product_data->get_title(), $product_data->get_stock_quantity(), $product_qty_in_cart[ $variation_id ] ), 'error' );
						return false;
					}

				// Products
				} else {

					if ( isset( $product_qty_in_cart[ $product_id ] ) && ! $product_data->has_enough_stock( $product_qty_in_cart[ $product_id ] + $quantity ) ) {
						if ( ! $silent )
							wc_bundles_add_notice( sprintf( __( '<a href="%s" class="button">%s</a>You cannot add that amount to the cart since there is not enough stock of "%s" &mdash; we have %s in stock and you already have %s in your cart.', 'woo-bundles' ), get_permalink(woocommerce_get_page_id('cart')), __( 'View Cart &rarr;', 'woocommerce' ), $product_data->get_title(), $product_data->get_stock_quantity(), $product_qty_in_cart[ $product_id ] ), 'error' );
						return false;
					}

				}

			}

			return true;

		}

		// debugging only
		function woo_bundles_before_cart() {

			global $woocommerce;

			$cart = $woocommerce->cart->get_cart();

			print_r( $_SESSION['stock_check_data'] );
			echo '<br/>';
			echo '<br/>';

			echo 'Cart Contents Total: ' . $woocommerce->cart->cart_contents_total . '<br/>';
			echo 'Cart Tax Total: ' . $woocommerce->cart->tax_total . '<br/>';
			echo 'Cart Total: ' . $woocommerce->cart->get_cart_total() . '<br/>';

			foreach ( $cart as $key => $data ) {
				echo '<br/>Cart Item - '.$key.' (' . count($data) . ' items):<br/>';

				echo 'Price: ' . $data['data']->get_price();
				echo '<br/>';

				foreach ( $data as $datakey => $value ) {
					print_r ( $datakey ); if ( is_numeric( $value ) || is_string( $value ) ) echo ': ' . $value; echo ' | ';
				}
			}
		}

	}

	$GLOBALS['woocommerce_bundles'] = new WC_Bundles();
}
