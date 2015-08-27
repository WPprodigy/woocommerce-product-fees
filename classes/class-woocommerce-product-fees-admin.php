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

		echo '<div id="fees_product_data" class="fee_panel panel woocommerce_options_panel wc-metaboxes-wrapper">';
		echo '<div class="options_group">';

		// Text Field - Fee Name
		woocommerce_wp_text_input( array( 'id' => 'product-fee-name', 'label' => __( 'Fee Name', 'woocommerce-product-fees' ), 'data_type' => 'text', 'placeholder' => __('Product Fee', 'placeholder', 'woocommerce-product-fees'), 'desc_tip' => 'true', 'description' => __( 'This will be shown at checkout descriping the added fee.', 'woocommerce-product-fees' ) ) );

		// Text Field - Fee Amount
		woocommerce_wp_text_input( array( 'id' => 'product-fee-amount', 'label' => __( 'Fee Amount', 'woocommerce-product-fees' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price', 'desc_tip' => 'true', 'description' => __( 'Enter a monetary decimal without any currency symbols or thousand seperators. This field also accepts percentages.', 'woocommerce-product-fees' ) ) );
		
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

		// Text Field - Fee Amount
		$product_fee_amount_text_field = $_POST['product-fee-amount'];
		if( ! empty( $product_fee_amount_text_field ) ) {
			update_post_meta( $post_id, 'product-fee-amount', esc_attr( $product_fee_amount_text_field ) );
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
