<?php
/**
 * WooCommerce Product Fees
 *
 * Get variation fee data.
 *
 * @class 	WCPF_Variation_Fee
 * @author 	Caleb Burks
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPF_Variation_Fee extends WCPF_Product_Fee {

	/** @var int Variation ID */
	public $variation_id = 0;

	/**
	 * Constructor for the variation fee class
	 *
	 * @access public
	 */
	public function __construct( $id, $qty, $price, $variation_id ) {
		parent::__construct( $id, $qty, $price );
		$this->variation_id = $variation_id;
	}

	/**
	 * Get fee data from product variation settings.
	 *
	 * @access public
	 */
	public function get_variation_fee_data() {
		// Variation ID
		$variation_id = $this->variation_id;

		// Check if the variation has a fee
		if ( get_post_meta( $variation_id, 'product-fee-name', true ) != '' && get_post_meta( $variation_id, 'product-fee-amount', true ) != '' ) {
			$fee = array(
				'name' => get_post_meta( $variation_id, 'product-fee-name', true ), 
				'amount' =>	get_post_meta( $variation_id, 'product-fee-amount', true ),
				'multiplier' => get_post_meta( $variation_id, 'product-fee-multiplier', true ),
				'product_id' => $variation_id
			);

			return apply_filters( 'wcpf_individual_variation_fee_data',  $fee );
		} else {
			// No variation fee data found.
			return false;
		}
	}

	/**
	 * Return final fee data
	 *
	 * @access public
	 */
	public function return_fee() {
		$variation_fee_data = $this->get_variation_fee_data();
		$fee_data = parent::quantity_multiply( $variation_fee_data );

		return WCPF_Fee::get_fee( $fee_data );
	}

}
