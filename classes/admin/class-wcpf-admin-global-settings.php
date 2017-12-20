<?php
/**
 * WooCommerce Product Fees
 *
 * Creates global product settings, coupon options, and adds csv import support.
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
		// Create WooCommerce > Settings > Products > Fees menu item / settings page.
		add_action( 'woocommerce_get_sections_products', array( $this, 'add_product_section' ), 10 );
		add_action( 'woocommerce_get_settings_products', array( $this, 'product_settings_output' ), 10, 2 );

		// Add and save the coupon setting.
		if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
			add_action( 'woocommerce_coupon_options', array( $this, 'add_coupon_setting' ), 10, 2 );
			add_action( 'woocommerce_coupon_options_save', array( $this, 'save_coupon_setting' ), 10, 2 );
		}

		// CSV Import/Export Support.
		if ( version_compare( WC_VERSION, '3.1', '>=' ) ) {
			// Import
			add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'add_columns_to_importer_exporter' ), 10 );
			add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'add_column_to_mapping_screen' ), 10 );
			add_filter( 'woocommerce_product_import_pre_insert_product_object', array( $this, 'process_import' ), 10, 2 );

			// Export
			add_filter( 'woocommerce_product_export_column_names', array( $this, 'add_columns_to_importer_exporter' ), 10 );
			add_filter( 'woocommerce_product_export_product_default_columns', array( $this, 'add_columns_to_importer_exporter' ), 10 );
			add_filter( 'woocommerce_product_export_product_column_wcpf_fee_name', array( $this, 'add_export_data_fee_name' ), 10, 2 );
			add_filter( 'woocommerce_product_export_product_column_wcpf_fee_amount', array( $this, 'add_export_data_fee_amount' ), 10, 2 );
			add_filter( 'woocommerce_product_export_product_column_wcpf_fee_multiplier', array( $this, 'add_export_data_fee_multiplier' ), 10, 2 );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Product Fees Settings
	|--------------------------------------------------------------------------
	*/

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

	/*
	|--------------------------------------------------------------------------
	| Coupon Settings
	|--------------------------------------------------------------------------
	*/

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

	/*
	|--------------------------------------------------------------------------
	| CSV Import/Export Support
	| https://github.com/woocommerce/woocommerce/wiki/Product-CSV-Importer-&-Exporter
	|--------------------------------------------------------------------------
	*/

	public function add_columns_to_importer_exporter( $options ) {
		// column slug => column name
		$options['wcpf_fee_name'] = 'Fee Name';
		$options['wcpf_fee_amount'] = 'Fee Amount';
		$options['wcpf_fee_multiplier'] = 'Fee Multiplier';

		return $options;
	}

	public function add_column_to_mapping_screen( $columns ) {
		// potential column name => column slug
		$columns['Fee Name'] = 'wcpf_fee_name';
		$columns['fee name'] = 'wcpf_fee_name';

		$columns['Fee Amount'] = 'wcpf_fee_amount';
		$columns['fee amount'] = 'wcpf_fee_amount';

		$columns['Fee Multiplier'] = 'wcpf_fee_multiplier';
		$columns['fee multiplier'] = 'wcpf_fee_multiplier';

		return $columns;
	}

	public function process_import( $object, $data ) {
		if ( ! empty( $data['wcpf_fee_name'] ) ) {
			$object->update_meta_data( 'product-fee-name', $data['wcpf_fee_name'] );
		}

		if ( ! empty( $data['wcpf_fee_amount'] ) ) {
			$object->update_meta_data( 'product-fee-amount', $data['wcpf_fee_amount'] );
		}

		if ( ! empty( $data['wcpf_fee_multiplier'] ) ) {
			$object->update_meta_data( 'product-fee-multiplier', $data['wcpf_fee_multiplier'] );
		}

		return $object;
	}

	public function add_export_data_fee_name( $value, $product ) {
		return $product->get_meta( 'product-fee-name', true, 'edit' );
	}

	public function add_export_data_fee_amount( $value, $product ) {
		return $product->get_meta( 'product-fee-amount', true, 'edit' );
	}

	public function add_export_data_fee_multiplier( $value, $product ) {
		return $product->get_meta( 'product-fee-multiplier', true, 'edit' );
	}

}

return new WCPF_Admin_Global_Settings();
