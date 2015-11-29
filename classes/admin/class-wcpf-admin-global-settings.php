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
		add_action( 'woocommerce_get_sections_products', array( $this, 'add_menu_item' ) );

		// Add settings to the page.
		add_action( 'woocommerce_get_settings_products', array( $this, 'output' ), null, 2 );
	}

	public function add_menu_item( $sections ) {
		$sections['fees'] = __( 'Product Fees', 'woocommerce-product-fees' );
		return $sections;
	}

	public function output( $settings, $current_section ) {
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
				'options'  => array( '' => __( 'No taxes for fees', 'woocommerce-product-fees' ) ) + $this->tax_classes(),
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

		if ( ! empty( $tax_classes ) ) {
			foreach ( $tax_classes as $class ) {
				$classes_options[ sanitize_title( $class ) ] = esc_html( $class );
			}
		}

		return $classes_options;
	}

} // End Class
