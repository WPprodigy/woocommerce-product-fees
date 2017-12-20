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
	 */
	public function __construct() {
		if ( is_admin() ) {
			// Product & global settings
			require_once 'admin/class-wcpf-admin-product-settings.php';
			require_once 'admin/class-wcpf-admin-global-settings.php';
		}

		// Text domain
		add_action( 'plugins_loaded', array( $this, 'text_domain' ) );

		// Hook-in for fees to be added
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_fees' ), 15 );
	}

	/**
	 * Load Text Domain
	 */
	public function text_domain() {
	 	load_plugin_textdomain( 'woocommerce-product-fees', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Check if a product contains fee data.
	 *
	 * @param int $id Product ID.
	 * @return bool True or false based on existance of custom meta.
	 */
	public function product_contains_fee_data( $id ) {
		$fee_name   = get_post_meta( $id, 'product-fee-name', true );
		$fee_amount = get_post_meta( $id, 'product-fee-amount', true );

		if ( '' !== $fee_name && '' !== $fee_amount && $fee_amount > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Convert a fee amount from percentage to the actual cost.
	 *
	 * @param string $fee_amount Fee amount.
	 * @param int $item_price Item price.
	 * @return int $fee_amount The actual cost of the fee.
	 */
	public function make_percentage_adjustments( $fee_amount, $item_price ) {
		// Replace with a standard decimal separator for calculations.
		$fee_amount = str_replace( wc_get_price_decimal_separator(), '.', $fee_amount );

		if ( strpos( $fee_amount, '%' ) ) {
			// Convert to decimal, then multiply by the cart item's price.
			$fee_amount = ( str_replace( '%', '', $fee_amount ) / 100 ) * $item_price;
		}

		return $fee_amount;
	}

	/**
	 * Multiply the fee by the cart item quantity if needed.
	 *
	 * @param int $amount Fee amount.
	 * @param string $multiplier Whether the item should be multiplied by qty or not.
	 * @param int $qty Cart item quantity.
	 * @return int $amount The actual cost of the fee.
	 */
	public function maybe_multiply_by_quantity( $amount, $multiplier, $qty ) {
		// Multiply the fee by the quantity if needed.
		if ( 'yes' === $multiplier ) {
			$amount = $qty * $amount;
		}

		return $amount;
	}

	/**
	 * Get the fee data from a product.
	 *
	 * @param array $item Cart item data.
	 * @return array $fee_data Fee data.
	 */
	public function get_fee_data( $item ) {
		$fee_data = false;

		// Assign the variation's parent ID if no fee at the variation level.
		if ( 0 !== $item['variation_id'] ) {
			if ( 0 !== $item['variation_id'] && ! $this->product_contains_fee_data( $item['id'] ) ) {
				$item['id'] = $item['parent_id'];
			}
		}

		if ( $this->product_contains_fee_data( $item['id'] ) ) {
			$fee_data = array(
				'name'       => get_post_meta( $item['id'], 'product-fee-name', true ),
				'amount'     =>	get_post_meta( $item['id'], 'product-fee-amount', true ),
				'multiplier' => get_post_meta( $item['id'], 'product-fee-multiplier', true )
			);

			$fee_data['amount'] = $this->make_percentage_adjustments( $fee_data['amount'], $item['price'] );
			$fee_data['amount'] = $this->maybe_multiply_by_quantity( $fee_data['amount'], $fee_data['multiplier'], $item['qty'] );
		}

		return $fee_data;
	}

	/**
	 * Check if fees should be removed due to a coupon.
	 *
	 * @param object $cart WC Cart object.
	 * @return bool True or false based on existance of coupon meta.
	 */
	public function maybe_remove_fees_for_coupon( $cart ) {

		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			return false;
		}

		// Look for a fee-removing coupon.
		$cart_coupons = $cart->get_coupons();
		if ( ! empty( $cart_coupons ) ) {
				foreach ( $cart_coupons as $coupon ) {
					if ( 'yes' === $coupon->get_meta( 'wcpf_coupon_remove_fees' ) ) {
						return true;
					}
				}
		}

		return false;
	}

	/**
	 * Get the fee's tax class.
	 *
	 * @param object $product WC Cart item object.
	 * @return string $fee_tax_class Which tax class to use for the fee.
	 */
	public function get_fee_tax_class( $product ) {
		$fee_tax_class = get_option( 'wcpf_fee_tax_class', '_no_tax' );

		if ( ! wc_tax_enabled() ) {
			return '_no_tax';
		}

		// Change fee tax settings to the product's tax settings.
		if ( 'inherit_product_tax' === $fee_tax_class ) {
			if ( 'taxable' === $product->get_tax_status() ) {
				$fee_tax_class = $product->get_tax_class();
			} else {
				$fee_tax_class = '_no_tax';
			}
		}

		return $fee_tax_class;
	}

	/**
	 * Get all the fees.
	 *
	 * @param object $cart WC Cart object.
	 * @return array $fees An array of fees to be added.
	 */
	public function get_fees( $cart ) {
		$fees = array();

		if ( $this->maybe_remove_fees_for_coupon( $cart ) ) {
			return $fees;
		}

		foreach( $cart->get_cart() as $cart_item => $item ) {

			// Get the data we need from each product in the cart.
			$item_data = array(
				'id'           => $item['data']->get_id(),
				'variation_id' => $item['variation_id'],
				'parent_id'    => $item['data']->get_parent_id(),
				'qty'          => $item['quantity'],
				'price'        => $item['data']->get_price()
			);

			$fee = $this->get_fee_data( $item_data );

			if ( $fee ) {
				$fee_id        = strtolower( $fee['name'] );
				$fee_tax_class = $this->get_fee_tax_class( $item['data'] );

				if ( array_key_exists( $fee_id, $fees ) && 'combine' === get_option( 'wcpf_name_conflicts', 'combine' ) ) {
					$fees[$fee_id]['amount'] += $fee['amount'];
				} else {
					$fees[$fee_id] = apply_filters( 'wcpf_filter_fee_data', array(
						'name' => $fee['name'],
						'amount' => $fee['amount'],
						'taxable' => ( '_no_tax' === $fee_tax_class ) ? false : true,
						'tax_class' => $fee_tax_class
					), $item_data );
				}
			}
		}

		return $fees;
	}

	/**
	 * Add the fees to the cart.
	 *
	 * @param object $cart WC Cart object.
	 * @return null
	 */
	public function add_fees( $cart ) {
		$fees = $this->get_fees( $cart );

		if ( empty( $fees ) ) {
			return;
		}

		foreach ( $fees as $fee ) {
			$cart->add_fee( $fee['name'], $fee['amount'], $fee['taxable'], $fee['tax_class'] );
		}
	}

}
