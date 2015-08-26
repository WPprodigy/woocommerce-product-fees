<?php
/**
 * WooCommerce Product Fees
 *
 * Creates and adds the fees at checkout.
 *
 * @class 	Woocommerce_Product_Fees
 * @version 1.0
 * @author 	Caleb Burks
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Lod the helper class to handle multiple currencies
require_once 'class-currency-helper.php';

class Woocommerce_Product_Fees {

	public function __construct() {

		if ( is_admin() ) {
			// Load Admin Settings
			require_once 'class-woocommerce-product-fees-admin.php';
		}

		// Text Domain
		add_action( 'plugins_loaded', array( $this, 'text_domain' ) );

		// Add the fee
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'get_product_fee_data' ) );

	}

	/**
	 * Load Text Domain
	 */
	public function text_domain() {

	 	load_plugin_textdomain( 'woocommerce-product-fees', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Creates the fee.
	 */
	public function add_product_fee( $fee_amount, $fee_name ) {

 		global $woocommerce;

  		$woocommerce->cart->add_fee( __($fee_name, 'woocommerce-product-fees'), $fee_amount );

	}

	/**
	 * Checks if the fee has a percentage in it, and then converts the fee from a percentage to a decimal
	 */
	public function percentage_conversion( $product_fee, $cart_product_price ) {

		if ( strpos( $product_fee, '%' ) ) {

			// Convert to Decimal
			$decimal = str_replace( '%', '', $product_fee ) / 100;

			// Multiply by Product Price
			$fee = $cart_product_price * $decimal;

		} else {

			$fee = $product_fee;

		}

		return $fee;

	}

	/**
	 * Checks if the fee should be multiplied by the quantity of the product in the cart
	 */
	public function quantity_multiply( $product_fee, $cart_product_price, $quantity_multiply, $cart_product_qty ) {

		// Pull in the percentage check
		$product_fee = $this->percentage_conversion( $product_fee, $cart_product_price );

		if ( $quantity_multiply == 'yes' ) {

			// Multiply the fee by the quantity
			$new_product_fee = $cart_product_qty * $product_fee;

		} else {

			$new_product_fee = $product_fee;

		}

		return $new_product_fee;

	}

	/**
	 * Checks if products in the cart have added fees. If so, then it send the data to add_product_fee().
	 */
	public function product_specific_fee( $product_id, $product_fee, $product_fee_name, $quantity_multiply ) {

		global $woocommerce;

		foreach( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {

			$cart_product = $values['data'];
			$cart_product_qty = $values['quantity'];
			$cart_product_price = $cart_product->price;

			// Checks if each product in the cart has additional fees that need to be added
			if ( $cart_product->id == $product_id ) {

				$new_product_fee = $this->quantity_multiply( $product_fee, $cart_product_price, $quantity_multiply, $cart_product_qty );

				// Send multiplied fee data to add_product_fee()
				$this->add_product_fee( $new_product_fee, $product_fee_name );

			}

		}

	}

	/**
	 * Returns the fee associated with a product, if any. This method is
	 * multi-currency aware, and it's able to retrieve the specific fee that applies
	 * for the active currency. If none is found, then the fee in product's base
	 * currency is taken and converted automatically, using exchange rates.
	 *
	 * @param int product_id A product ID.
	 * @return float|null The product fee, if any, or an empty value if no fee
	 * applies.
	 * @author Aelia <support@aelia.co>
	 */
	protected function get_product_fee($product_id) {
		// This check was copied from the original code, and it seems to verify that
		// the product has fees associated to it. It may be superflous, though
		// TODO Review check and (eventually) remove it
		if( !get_post_meta( $product_id, 'product-fee-name', true ) ) {
			return false;
		}

		$active_currency = get_woocommerce_currency();
		$product_fee = get_post_meta( $product_id, 'product-fee-amount-' . $active_currency, true );

		// If there isn't a product fee specified for the active currency, check if
		// there is a fee for product's base currency
		if( empty( $product_fee ) ) {
			// Load product's base currency. It will be used to show the Admin which
			// fees can be calculated automatically
			$product_base_currency = WooCommerce_Product_Fees_Currency_Helper::get_product_base_currency($product_id);

			// If there is a fee in base currency, retrieve it and convert it to the
			// active currency
			$product_fee = get_post_meta( $product_id, 'product-fee-amount-' . $product_base_currency, true );
			if( !empty( $product_fee ) ) {
				// If the fee is a percentage, there is no need to convert it. If it's
				// a fixed value, then we can convert it to the active currency
				if ( !strpos( $product_fee, '%' ) ) {
					$product_fee = WooCommerce_Product_Fees_Currency_Helper::convert($product_fee, $active_currency, $product_base_currency);
				}
				return $product_fee;
			}
		}
		return $product_fee;
	}

	/**
	 * Pulls data from WooCommerce product settings and sends it to product_specific_fee().
	 */
	public function get_product_fee_data() {

		global $woocommerce;

		foreach( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {

			$cart_product = $values['data'];

			// Checks for a fee name and fee amount in the product settings
			$fee_amount = $this->get_product_fee($cart_product->id);
			if ( !empty( $fee_amount ) ) {

				$fee_name = get_post_meta( $cart_product->id, 'product-fee-name', true );
				$fee_multiplier = get_post_meta( $cart_product->id, 'product-fee-multiplier', true );

				// Send fee data to product_specific_fee()
				$this->product_specific_fee( $cart_product->id, $fee_amount, $fee_name, $fee_multiplier );

			}

		}

	}


} // End Class
new Woocommerce_Product_Fees();
