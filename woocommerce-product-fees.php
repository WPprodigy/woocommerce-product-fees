<?php
/**
 * Plugin Name: WooCommerce Product Fees
 * Plugin URI: http://calebburks.com/woocommerce-product-fees
 * Description: Add additional fees at checkout based on products that are in the cart.
 * Version: 1.1.1
 * Author: Caleb Burks
 * Author URI: http://calebburks.com
 *
 * Text Domain: woocommerce-product-fees
 * Domain Path: /i18n/languages/
 *
 * Tested up to: 4.3
 *
 * Copyright: (c) 2015 Caleb Burks
 * License: GPL v3 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-3.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'woocommerce_product_fees_load_after_plugins_loaded' );

function woocommerce_product_fees_load_after_plugins_loaded() {

	if ( ! class_exists( "Woocommerce_Product_Fees" ) && class_exists( 'WooCommerce' ) ) {

		require_once( 'classes/class-woocommerce-product-fees.php' );

	}

}

/* Silence is Golden */