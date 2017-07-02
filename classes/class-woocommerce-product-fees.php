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
	public function add_fees( $cart ) {

		// Look for a fee-removing coupon.
		$cart_coupons = $cart->get_coupons();
		if ( ! empty( $cart_coupons ) ) {
				foreach ( $cart_coupons as $coupon ) {
					if ( 'yes' === $coupon->get_meta( 'wcpf_coupon_remove_fees' ) ) {
						// Exit now. No need to look for fees.
						return;
					}
				}
		}

		foreach( $cart->get_cart() as $cart_item => $values ) {
			$_product = $values['data'];
			$fee      = false;

			// Data we need from each product in the cart.
			$product_data = array(
				'id'     => $values['product_id'],
				'qty'    => $values['quantity'],
				'price'  => $_product->get_price()
			);

			// Check first for a variation specific fee, and use that if it exists.
			if ( 0 !== $values['variation_id'] ) {
				$product_data['variation_id'] = $values['variation_id'];

				// Get variation fee. Will return false if there is no fee.
				$fee = new WCPF_Variation_Fee( $product_data, $cart );
			}

			if ( ! $fee ) {
				// Get product fee. Will return false if there is no fee.
				$fee = new WCPF_Product_Fee( $product_data, $cart );
			}

			if ( $fee ) {
				$fee_data      = $fee->return_fee();
				$fee_tax_class = get_option( 'wcpf_fee_tax_class', '_no_tax' );

				// Change fee tax settings to the product's tax settings.
				if ( 'inherit_product_tax' === $fee_tax_class ) {
					$product_tax_status = $_product->get_tax_status();
					$product_tax_class  = $_product->get_tax_class();

					if ( 'taxable' === $product_tax_status ) {
						$fee_tax_class = $product_tax_class;
					} else {
						$fee_tax_class = '_no_tax';
					}
				}

				do_action( 'wcpf_before_fee_is_added', $fee_data, $_product );

				// Check if taxes need to be added.
				if ( wc_tax_enabled() && '_no_tax' !== $fee_tax_class ) {
					// Add fee with taxes.
					$cart->add_fee( $fee_data['name'], $fee_data['amount'], true, $fee_tax_class );
				} else {
					// Add fee without taxes.
					$cart->add_fee( $fee_data['name'], $fee_data['amount'] );
				}

				do_action( 'wcpf_after_fee_is_added', $fee_data, $_product );
			}
		}
	}

}
