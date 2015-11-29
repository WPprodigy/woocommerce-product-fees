<?php
/**
 * Plugin Name: WooCommerce Product Fees
 * Plugin URI: http://calebburks.com/woocommerce-product-fees
 * Description: Add additional fees at checkout based on products that are in the cart.
 * Version: 1.2
 * Author: Caleb Burks
 * Author URI: http://calebburks.com
 *
 * Text Domain: woocommerce-product-fees
 * Domain Path: /languages/
 *
 * Requires at least: 4.0
 * Tested up to: 4.4
 *
 * Copyright: (c) 2015 Caleb Burks
 * License: GPL v3 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-3.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'wc_product_fees_load_after_plugins_loaded' );
function wc_product_fees_load_after_plugins_loaded() {
	if ( ! class_exists( "WooCommerce_Product_Fees" ) && class_exists( 'WooCommerce' ) ) {
		require_once( 'classes/class-woocommerce-product-fees.php' );
		new WooCommerce_Product_Fees;
	}
}

/* Silence is Golden */