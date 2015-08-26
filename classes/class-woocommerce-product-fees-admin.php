<?php
/**
 * WooCommerce Product Fees - Admin Settings
 *
 * Creates and saves the product settings.
 *
 * @class 	Woocommerce_Product_Fees_Admin
 * @version 1.0
 * @author 	Caleb Burks
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woocommerce_Product_Fees_Admin {

	public function __construct() {

		// Add Product Settings
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'create_product_panel_tab' ) );
		add_action( 'woocommerce_product_write_panels', array( $this, 'product_settings_fields' ) );

		// Save Product Settings
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_settings_fields' ) );

		// CSS
		add_action( 'admin_head', array( $this, 'admin_css' ) );

	}

	public function create_product_panel_tab() {

		echo '<li class="fees_product_tab product_fee_options"><a href="#fees_product_data">'.__( 'Product Fees', 'woocommerce-composite-products' ).'</a></li>';

	}

	public function product_settings_fields() {
		global $woocommerce, $post;

		echo '<div id="fees_product_data" class="fee_panel panel woocommerce_options_panel wc-metaboxes-wrapper">';
		echo '<div class="options_group">';

		// Text Field - Fee Name
		woocommerce_wp_text_input( array( 'id' => 'product-fee-name', 'label' => __( 'Fee Name', 'woocommerce-product-fees' ), 'data_type' => 'text', 'placeholder' => __('Product Fee', 'placeholder', 'woocommerce-product-fees'), 'desc_tip' => 'true', 'description' => __( 'This will be shown at checkout descriping the added fee.', 'woocommerce-product-fees' ) ) );


		// Load product's base currency. It will be used to show the Admin which
		// fees can be calculated automatically
		$product_base_currency = WooCommerce_Product_Fees_Currency_Helper::get_product_base_currency($post->ID);

		// Get a list of the enabled currencies. We will need to display one price
		// field for each. By using the merge/unique trick, we can make sure that
		// product's base currency is always the first in the list (the enabled_currencies()
		// method returns them sorted alphabetically)
		$enabled_currencies = array_unique(array_merge(
			array($product_base_currency),
			WooCommerce_Product_Fees_Currency_Helper::enabled_currencies()
		));

		$placeholder = '';
		foreach($enabled_currencies as $currency) {
			$field_description = __( 'Enter a monetary decimal without any currency symbols or thousand separators. This field also accepts percentages.', 'woocommerce-product-fees' );
			// For additional currencies, add an extra description to explain that the
			// fees can be calculated automatically
			if($currency != $product_base_currency) {
				$field_description = ' ' . sprintf(
					__( 'If you leave this field empty, its value will be calculated automatically, by converting the base amount in %s to this currency.', 'woocommerce-product-fees' ),
					$product_base_currency
				);
			}

			// If we have more than one currency, show a different placeholder for
			// the base currency and the additional ones, to help the admin to understand
			// which ones can be calculated automatically. When a single currency is
			// used, the placeholder can be left empty, as the field's purpose is
			// obvious
			if(count($enabled_currencies) > 1) {
				$placeholder = ($currency === $product_base_currency) ? __('Base amount', 'woocommerce-product-fees') : __('Auto', 'woocommerce-product-fees');
			}

			// Text Field - Fee Amount
			woocommerce_wp_text_input( array(
				'id' => 'product-fee-amount-' . $currency,
				'label' => __( 'Fee Amount', 'woocommerce-product-fees' ) . ' (' . $currency . ')',
				'data_type' => 'price',
				'desc_tip' => 'true',
				'description' => $field_description,
				// The field must be set up as an array, so that we can get one value for
				// each currency
				'name' => 'product-fee-amount[' . $currency . ']',
				'placeholder' => $placeholder,
			));
		}

		echo '</div>';
		echo '<div class="options_group">';

		// Check Box - Fee Multiply Option
		woocommerce_wp_checkbox( array( 'id'=> 'product-fee-multiplier', 'label' => __('Multiply Fee by Quantity', 'woocommerce-product-fees' ), 'desc_tip' => 'true', 'description' => __( 'Multiply the fee by the quanitity of this product that is added to the cart.', 'woocommerce-product-fees' ) ) );

		echo '</div>';
		echo '</div>';

	}

	public function save_product_settings_fields( $post_id ){

		// Text Field - Fee Name
		$product_fee_name_text_field = $_POST['product-fee-name'];
		if( ! empty( $product_fee_name_text_field ) ) {
			update_post_meta( $post_id, 'product-fee-name', esc_attr( $product_fee_name_text_field ) );
		}

		// Text Field - Fee Amounts in each currency
		$product_fee_amounts = $_POST['product-fee-amount'];
		if( ! empty( $product_fee_amounts ) ) {
			// Save the fee amount for each currency
			foreach($product_fee_amounts as $currency => $amount) {
				update_post_meta( $post_id, 'product-fee-amount-' . $currency, esc_attr( $amount ) );
			}
		}

		// Check Box - Fee Multiply Option
		$product_fee_multiplier_checkbox = isset( $_POST['product-fee-multiplier'] ) ? 'yes' : 'no';
	    update_post_meta( $post_id, 'product-fee-multiplier', $product_fee_multiplier_checkbox );

	}

	public function admin_css() {
		echo "
		<style type='text/css'>
			#woocommerce-product-data ul.product_data_tabs li.product_fee_options a:before {
				content: '\\e01e';
			}
		</style>
		";
	}


} // End Class
new Woocommerce_Product_Fees_Admin();
