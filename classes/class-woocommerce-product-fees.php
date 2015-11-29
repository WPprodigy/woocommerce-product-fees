<?php
/**
 * WooCommerce Product Fees
 *
 * Add the fees at checkout.
 *
 * @class 	WooCommerce_Product_Fees
 * @author 	Caleb Burks
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooCommerce_Product_Fees {

	/**
	 * Constructor for the main product fees class.
	 *
	 * @access public
	 */
	public function __construct() {
		if ( is_admin() ) {
			// Product Settings
			require_once 'admin/class-wcpf-admin-product-settings.php';
			new WCPF_Admin_Product_Settings();
			// Global Settings
			require_once 'admin/class-wcpf-admin-global-settings.php';
			new WCPF_Admin_Global_Settings();
		}

		// Fee Classes
		require_once( 'fees/class-wcpf-fee.php' );
		require_once( 'fees/class-wcpf-product-fee.php' );
		require_once( 'fees/class-wcpf-variation-fee.php' );

		// Text Domain
		add_action( 'plugins_loaded', array( $this, 'text_domain' ) );

		// Hook in for fees to be added
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_fees' ), 15 );
	}

	/**
	 * Load Text Domain
	 */
	public function text_domain() {
	 	load_plugin_textdomain( 'woocommerce-product-fees', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}

	/**
	 * Add all fees at checkout.
	 *
	 * @access public
	 */
	public function add_fees() {
		foreach( WC()->cart->get_cart() as $cart_item => $values ) {
			// Assume there is no fee.
			$fee = false;

			if ( 0 !== $values['variation_id'] ) {
				// Get variation fee. Will return false if there is no fee.
				$fee = new WCPF_Variation_Fee( $values['product_id'], $values['quantity'], $values['data']->price, $values['variation_id'] );
			}

			if ( ! $fee ) {
				// Get product fee. Will return false if there is no fee.
				$fee = new WCPF_Product_Fee( $values['product_id'], $values['quantity'], $values['data']->price );
			}

			if ( $fee->return_fee() ) {
				$data = $fee->return_fee();
				do_action( 'wcpf_before_fee_is_added', $data );
				// Check if taxes need to be added.
				if ( get_option( 'wcpf_fee_tax_class', '' ) !== '' ) {
					WC()->cart->add_fee( $data['name'], $data['amount'], true, get_option( 'wcpf_fee_tax_class' ) );
				} else {
					WC()->cart->add_fee( $data['name'], $data['amount'], false );
				}
				do_action( 'wcpf_after_fee_is_added', $data );
			}
		}
	}

}
