<?php
/**
 * Plugin Name: WooCommerce Admin Custom Order Fields
 * Plugin URI: http://www.woothemes.com/products/woocommerce-admin-custom-order-fields/
 * Description: Easily add custom fields to your WooCommerce orders and display them in the Orders admin, the My Orders section, and even order emails!
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com
 * Version: 1.1.1
 * Text Domain: wc-admin-custom-order-fields
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2012-2014 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Admin-Custom-Order-Fields
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2012-2014, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '31cde5f743a6d0ef83cc108f4b85cf8b', '272218' );

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '2.0.2', __( 'WooCommerce Admin Custom Order Fields', 'woocommerce-admin-custom-order-fields' ), __FILE__, 'init_woocommerce_admin_custom_order_fields' );

function init_woocommerce_admin_custom_order_fields() {

/**
 * # WooCommerce Admin Custom Order Fields Main Plugin Class
 *
 * ## Plugin Overview
 *
 * The WooCommerce Admin Custom Order Fields allows custom order fields to be
 * defined and configured by a shop manager and displaed within the WooCommerce Order Admin
 *
 * ## Admin Considerations
 *
 * A 'Custom Order Fields' sub-menu item added to the 'WooCommerce' menu item, along with a meta-box on the Edit Order
 * page, used for entering data into the custom fields defined
 *
 * ## Frontend Considerations
 *
 * If a custom field has the `visible` attribute, it will be displayed after the order table, in both emails and
 * the my account > orders > view order screen
 *
 * ## Database
 *
 * ### Custom Fields
 *
 * + `wc_admin_custom_order_fields` - a serialized array containing all the custom fields defined, in this format:
 *
 * ```
 * [ int|field_id ] => {
 *      label => string, field title
 *      type => string, text|textarea|select|multiselect|radio|checkbox|date
 *      description => string, text to display as a help bubble
 *      default => string, available if type is text or textarea or date ('now' is a special indicator for date fields)
 *      options => array of available options, if type is select or multiselect or radio or checkbox {
 *           default => bool, true if option is a default, false otherwise
 *           label => string, the label for the option
 *           value => string, the sanitized value for the option, generated using sanitize_key() on the label
 *      }
 *      required => bool, true if the field is required. Note this doesn't prevent the order from being saved, but simply adds a red star next to the field label
 *      visible => bool, true to show in order emails & frontend
 *      listable => bool, true to show field in the Orders list page, false otherwise
 *      sortable => bool, true if the field is sortable (text/textarea/radio/date only, listable fields only)
 *      filterable => bool, true if the field is filterable (listable fields only, no textarea)
 *      is_numeric => bool, true if field value/default is numeric (available if the type is 'text')
 *      scope => string `order` by default at the moment, not editable by admin
 * }
 * ```
 *
 * ### Options table
 *
 * + `wc_admin_custom_order_fields_version` - the current plugin version, set on install/upgrade
 * + `wc_admin_custom_order_fields_next_field_id` - a sequential counter for the custom field IDs
 * + `wc_admin_custom_order_fields_welcome` - a flag to display the welcome notice on the field editor screen
 *
 * ### Order Meta
 *
 * When information is entered into the custom fields and the order is saved, the field data is saved to the order meta
 * using the `_wc_acof_<field_id>` meta key. This allow field labels to be changed without affecting the saved data.
 *
 */
class WC_Admin_Custom_Order_Fields extends SV_WC_Plugin {


	/** plugin version number */
	const VERSION = '1.1.1';

	/** plugin id */
	const PLUGIN_ID = 'admin_custom_order_fields';

	/** plugin text domain */
	const TEXT_DOMAIN = 'woocommerce-admin-custom-order-fields';

	/** @var \WC_Admin_Custom_Order_Fields_Admin instance */
	public $admin;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0
	 * @return \WC_Admin_Custom_Order_Fields
	 */
	public function __construct() {

		parent::__construct(
		  self::PLUGIN_ID,
		  self::VERSION,
		  self::TEXT_DOMAIN
		);

		// include required files
		$this->includes();

		// display any publicly-visible custom order data in the frontend/emails
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_order_details_after_order_table' ) );
		add_action( 'woocommerce_email_after_order_table',         array( $this, 'add_order_details_after_order_table' ) );
	}


	/**
	 * Include required files
	 *
	 * @since 1.0
	 */
	private function includes() {

		require_once( 'includes/class-wc-custom-order-field.php' );

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$this->admin_includes();
		}
	}


	/**
	 * Include required admin files
	 *
	 * @since 1.0
	 */
	private function admin_includes() {

		// load order list table/edit order customizations
		require_once( 'includes/admin/class-wc-admin-custom-order-fields-admin.php' );
		$this->admin = new WC_Admin_Custom_Order_Fields_Admin();
	}


	/**
	 * Load plugin text domain.
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::load_translation()
	 */
	public function load_translation() {

		load_plugin_textdomain( 'woocommerce-admin-custom-order-fields', false, dirname( plugin_basename( $this->get_file() ) ) . '/i18n/languages' );
	}


	/** Frontend methods ******************************************************/


	/**
	 * Display any publicly viewable order fields in the frontend/order emails
	 *
	 * @since 1.0
	 * @param WC_Order $order the order object
	 */
	public function add_order_details_after_order_table( $order ) {

		$visible = true;

		foreach ( $this->get_order_fields( $order->id ) as $order_field ) {

			if ( $order_field->is_visible() && $order_field->get_value_formatted() ) {

				if ( ! $visible) {
					echo '<dl id="order-custom-fields">';
					$visible = true;
				}

				echo '<dt>' . __( $order_field->label, self::TEXT_DOMAIN ) . '</dt><dd>' . __( $order_field->get_value_formatted(), self::TEXT_DOMAIN ) . '</dd>';
			}
		}

		if ( $visible) echo "</dl>";
	}


	/** Admin methods ******************************************************/


	/**
	 * Render a notice for the user to read the docs before adding custom fields
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::render_admin_notices()
	 */
	public function render_admin_notices() {

		// show any dependency notices
		parent::render_admin_notices();

		// add notice for selecting export format
		if ( $this->is_plugin_settings() && ! $this->is_message_dismissed( 'read-the-docs' )  ) {

			$dismiss_link = sprintf( '<a href="#" class="js-wc-plugin-framework-%s-message-dismiss" data-message-id="%s">%s</a>', $this->get_id(), 'read-the-docs', __( 'Dismiss', WC_Admin_Custom_Order_Fields::TEXT_DOMAIN ) );

			$this->add_dismissible_notice(
			  sprintf( __( 'Thanks for installing Admin Custom Order Fields! Before you get started, please %sread the documentation%s or %s', WC_Admin_Custom_Order_Fields::TEXT_DOMAIN ),
				'<a href="' . $this->get_documentation_url() . '">', '</a>', $dismiss_link ),
				'read-the-docs'
			);
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Returns any configured order fields
	 *
	 * @since 1.0
	 * @param int $order_id optional order identifier, if provided any set values are loaded
	 * @return array of WC_Custom_Order_Field objects
	 */
	public function get_order_fields( $order_id = null ) {

		$order_fields = array();

		// get the order object if we can
		$order = $order_id ? new WC_Order( $order_id ) : null;

		$custom_order_fields = get_option( 'wc_admin_custom_order_fields' );

		if ( ! is_array( $custom_order_fields ) ) {
			$custom_order_fields = array();
		}

		foreach ( $custom_order_fields as $field_id => $field ) {

			$order_field = new WC_Custom_Order_Field( $field_id, $field );

			// if getting the fields for an order, does the order have a value set?
			if ( $order instanceof WC_Order ) {

				if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_2_1() ) {

					// WC 2.1+ uses magic methods which already prefix the key with a leading underscore
					// which needs to be removed prior to getting the value
					$meta_key = ltrim( $order_field->get_meta_key(), '_' );

					if ( isset( $order->$meta_key ) ) {

						$order_field->set_value( maybe_unserialize( $order->$meta_key ) );
					}

				} else {

					// WC 2.0 retains the WC_Order::order_custom_fields array
					if ( isset( $order->order_custom_fields[ $order_field->get_meta_key() ][0] ) ) {

						$order_field->set_value( maybe_unserialize( $order->order_custom_fields[ $order_field->get_meta_key() ][0] ) );
					}
				}
			}

			$order_fields[ $field_id ] = $order_field;
		}

		return $order_fields;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {

		return __( 'WooCommerce Admin Custom Order Fields', self::TEXT_DOMAIN );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {

		return __FILE__;
	}


	/**
	 * Gets the URL to the settings page
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @param string $_ unused
	 * @return string URL to the settings page
	 */
	public function get_settings_url( $_ = '' ) {

		return admin_url( 'admin.php?page=wc_admin_custom_order_fields' );
	}


	/**
	 * Returns true if on the gateway settings page
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the settings page
	 */
	public function is_plugin_settings() {

		return ( isset( $_GET['page'] ) && 'wc_admin_custom_order_fields' == $_GET['page'] );
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Install default settings
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::install()
	 */
	protected function install() {

		add_option( 'wc_admin_custom_order_fields_next_field_id', 1 );
		add_option( 'wc_admin_custom_order_fields_welcome', 1 );
	}


	/**
	 * Upgrade to $installed_version
	 *
	 * @since 1.1
	 * @param string $installed_version
	 * @see SV_WC_Plugin::upgrade()
	 */
	protected function upgrade( $installed_version ) {

		// upgrade to 1.1
		if ( version_compare( $installed_version, '1.1', '<' ) ) {

			delete_option( 'wc_admin_custom_order_fields_welcome' );
		}
	}


} // end \WC_Custom_Order_Fields class


/**
 * The WC_Admin_Custom_Order_Fields global object
 * @name $wc_admin_custom_order_fields
 * @global WC_Admin_Custom_Order_Fields $GLOBALS['wc_admin_custom_order_fields']
 */
$GLOBALS['wc_admin_custom_order_fields'] = new WC_Admin_Custom_Order_Fields();

} // init_woocommerce_admin_custom_order_fields()
