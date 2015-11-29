<?php
/**
 * WooCommerce Product Fees
 *
 * The main class for getting fee data.
 *
 * @class 	WCPF_Fee
 * @author 	Caleb Burks
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPF_Fee {

	/**
	 * Add filter to the finalized fee data, and combine same name fees.
	 *
	 * @access public
	 */
	public function get_fee( $fee ) {
		// Abort if there is no fee data.
		if ( false == $fee ) {
			return false;
		}
		$fee_data = apply_filters( 'wcpf_filter_fee_data',  $fee );
		$already_applied_fees = WC()->cart->fees;

		foreach ( $already_applied_fees as $applied_fee ) {
			// If the fee has the same name as a fee already in the cart,
			// then add the fee amounts together and present as a single fee.
			if ( $applied_fee->name == $fee_data['name'] && apply_filters( 'wcpf_add_same_name_fees', true ) ) {
				if ( get_option( 'wcpf_name_conflicts', 'combine' ) === 'combine' ) {
					$applied_fee->amount += $fee_data['amount'];
					do_action( 'wcpf_fee_names_combined', $applied_fee, $fee_data );
					return;
				}
			}
		}
		
		return $fee_data;
	}

}
