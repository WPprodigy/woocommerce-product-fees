<?php

/**
 * Helper class to allow 3rd party plugins to implement multi-currency
 * capabilities. The class was designed to work with the Aelia Currency Switcher,
 * but it's generic enough to be adapted to other solutions.
 *
 * @author Aelia <support@aelia.co>
 * @link https://aelia.co
 * @link https://aelia.co/shop/currency-switcher-woocommerce/
 */
class WooCommerce_Product_Fees_Currency_Helper {
  // @var string Shop's base currency. Used for caching.
  protected static $_base_currency;

  /**
   * Returns shop's base currency. This method implements some simple caching,
   * to avoid calling get_option() too many times.
   *
   * @return string Shop's base currency.
   */
  protected static function shop_base_currency() {
    if(empty(self::$_base_currency)) {
      self::$_base_currency = get_option('woocommerce_currency');
    }
    return self::$_base_currency;
  }

  /**
   * Returns the list of the currencies enabled in the Aelia Currency Switcher.
   * If the Currency Switcher is not installed, or active, then the list will
   * only contain the shop base currency.
   *
   * @return array An array of currency codes.
   */
  public static function enabled_currencies() {
    return apply_filters('wc_aelia_cs_enabled_currencies', array(self::shop_base_currency()));
  }

  /**
   * Returns a product's base currency. A product's base currency is the point
   * of reference to calculate other prices, and it can differ from shop's base
   * currency.
   * For example, if a shop's base currency is USD, a product's base currency
   * can be EUR. In such case, product prices in other currencies can be
   * calculated automatically, as long as the EUR one is entered.
   *
   * @param int product_id A product ID.
   * @param string default_currency The default currency to use if the product
   * doesn't have a base currency.
   * @return string A currency code.
   */
  public static function get_product_base_currency($product_id, $default_currency = null) {
    if(empty($default_currency)) {
      $default_currency = self::shop_base_currency();
    }
    return apply_filters('wc_aelia_cs_product_base_currency', $default_currency, $product_id);
  }

  /**
   * Advanced integration with WooCommerce Currency Switcher, developed by Aelia
   * (http://aelia.co). This method can be used by any 3rd party plugin to
   * return prices in the active currency. The method allows to specify prices
   * explicitly, thus bypassing automatic FX conversion.
   *
   * @param double price The source price.
   * @param array prices_per_currency An optional array of currency => value
   * pairs. If an entry is found in this array that matches the $to_currency
   * parameters, such value is taken as is, and the automatic conversion logic
   * is skipped.
   * @param string to_currency The target currency. If empty, the active currency
   * is taken.
   * @param string from_currency The source currency. If empty, WooCommerce base
   * currency is taken.
   * @return double The price converted from source to destination currency.
   */
  public static function get_price_in_currency($price, array $prices_per_currency = array(), $to_currency = null, $from_currency = null) {
    if(empty($from_currency)) {
      $from_currency = self::shop_base_currency();
    }
    if(empty($to_currency)) {
      $to_currency = get_woocommerce_currency();
    }

    // If an explicit price was passed for the target currency, just take it
    if(!empty($prices_per_currency[$to_currency])) {
      return $prices_per_currency[$to_currency];
    }
    return self::convert($price, $to_currency, $from_currency);
  }

  /**
   * Converts an amount from one currency to another, using exchange rates.
   *
   * @param float amount The amount to convert.
   * @param string to_currency The destination currency.
   * @param string from_currency The source currency.
   * @return float The amount converted to the target destination currency.
   */
  public static function convert($amount, $to_currency, $from_currency = null) {
    return apply_filters('wc_aelia_cs_convert', $amount, $from_currency, $to_currency);
  }
}
