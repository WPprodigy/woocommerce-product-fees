<?php
/**
 * WooCommerce Product Fees
 *
 * Creates and saves the global settings.
 *
 * @class 	WCPF_Admin_Global_Settings
 * @author 	Caleb Burks
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPF_Admin_Global_Settings {

	public function __construct() {
		// Create WooCommerce > Settings > Products > Fees menu item.
		add_action( 'woocommerce_get_sections_products', array( $this, 'add_product_section' ), 10 );

		// Add settings to the page.
		add_action( 'woocommerce_get_settings_products', array( $this, 'product_settings_output' ), 10, 2 );

		// Add and save the coupon setting.
		add_action( 'woocommerce_coupon_options', array( $this, 'add_coupon_setting' ), 10, 2 );
		add_action( 'woocommerce_coupon_options_save', array( $this, 'save_coupon_setting' ), 10, 2 );
	}

	public function add_product_section( $sections ) {
		$sections['fees'] = __( 'Product Fees', 'woocommerce-product-fees' );
		return $sections;
	}

	public function product_settings_output( $settings, $current_section ) {
		if ( 'fees' == $current_section ) {
			$settings = $this->settings_fields();
		}
		return $settings;
	}

	public function settings_fields() {
		$settings = apply_filters( 'wcpf_global_product_settings', array(
			array(
				'title' => __( 'Product Fees', 'woocommerce-product-fees' ),
				'type' 	=> 'title',
				'desc' 	=> '',
				'id' 	=> 'product_fees_options',
			),

			array(
				'title'    => __( 'Fee Tax Class:', 'woocommerce-product-fees' ),
				'desc'     => __( 'Optionally control which tax class gets applied to fees, or leave it so no taxes are applied.', 'woocommerce-product-fees' ),
				'id'       => 'wcpf_fee_tax_class',
				'css'      => 'min-width:150px;',
				'default'  => 'title',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => $this->tax_classes(),
				'desc_tip' =>  true,
			),

			array(
				'title'   => __( 'Fee Name Conflicts', 'woocommerce-product-fees' ),
				'desc'    => __( 'If option #2 is chosen, whichever product comes first in the cart will take precedence. ', 'woocommerce-product-fees' ),
				'id'      => 'wcpf_name_conflicts',
				'default' => 'combine',
				'type'    => 'radio',
				'options' => array(
					'combine'      => __( '1) Combine fees with the same name. (recommended)', 'woocommerce-product-fees' ),
					'dont_combine' => __( '2) Only add one fee if the names are conflicting.', 'woocommerce-product-fees' ),
				),
				'desc_tip'        =>  true,
			),

			array( 'type' => 'sectionend', 'id' => 'product_fees_options' ),
		));

		return $settings;
	}

	public function tax_classes() {
		$tax_classes     = WC_Tax::get_tax_classes();
		$classes_options = array();

		$classes_options['_no_tax'] = __( 'No taxes for fees', 'woocommerce-product-fees' );

		// Add support for product-level tax settings.
		$classes_options['inherit_product_tax'] = __( 'Fee tax class based on the fee\'s product', 'woocommerce-product-fees' );

		// Manually add the standard tax as it's not returned by WC_Tax::get_tax_classes().
		// Thanks @daigo75
		$classes_options[''] = __( 'Standard', 'woocommerce' );

		if ( ! empty( $tax_classes ) ) {
			foreach ( $tax_classes as $class ) {
				$classes_options[ sanitize_title( $class ) ] = esc_html( $class );
			}
		}

		return $classes_options;
	}

	public function add_coupon_setting( $coupon_id, $coupon ) {
		woocommerce_wp_checkbox( array(
			'id'          => 'wcpf_coupon_remove_fees',
			'label'       => __( 'Remove product fees', 'woocommerce-product-fees' ),
			'description' => __( 'Check this box if the coupon should remove product fees.', 'woocommerce-product-fees' ),
			'value'       => $coupon->get_meta( 'wcpf_coupon_remove_fees' ),
		) );
	}

	public function save_coupon_setting( $post_id, $coupon ) {
		$value = isset( $_POST['wcpf_coupon_remove_fees'] ) ? 'yes' : '';

		$coupon->add_meta_data( 'wcpf_coupon_remove_fees', $value, true );
		$coupon->save_meta_data();
	}

} // End Class
