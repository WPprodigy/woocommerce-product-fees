=== WooCommerce Product Fees ===
Contributors: woothemes, iCaleb
Tags: woocommerce, woocommerce fees, woocommerce surcharge, product fees, product surcharge, additional fees, product based fees
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 1.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

WooCommerce Product Fees allows you to add additional fees at checkout based on products that are in the cart.

== Description ==

A WooCommerce plugin that adds a product settings tab for creating additional fees that show up at checkout if that product is in the cart.

In the WooCommerce products edit screen, there will be a new product data tab called 'Product Fees' where you can:

* Give the fee a custom name that is displayed to the customer at checkout describing the fee.
* Enter the cost of the fee as either a flat rate number or as a percentage.
* Choose whether or not the fee should be multiplied based on the quantity of the product in the cart.

There will also be similar fields within each variation tab on the product. If the variation does not have a fee, it will fallback and look to use the product fee if one exists.

For the plugin settings, you can go **WooCommerce > Settings > Products > Product Fees**. Here you will find two settings:

* Fee Tax Class - Assign a tax class to be applied to fees, or leave it so fees are not taxed.
* Fee Name Conflicts - Choose whether fees with the same name should be combined or not.

= Features =

I have kept this plugin simple on purpose. It is very lightweight and should not conflict with any other plugins or themes. Here are the features included in the plugin:

* Percentage based fees that go off of the product's price.
* Variation specific fees.
* A quantity multiplier that can be toggled on/off per product and per variation.
* Option to combine fees with the same name.
* Ability to assign a tax class to be used on fees.
* Coupon setting that will remove fees when added.

= Contributions =

If you’re interested in contributing to WooCommerce Product Fees - head over to the [WooCommerce Product Fees GitHub Repository](https://github.com/WPprodigy/woocommerce-product-fees) to find out how you can pitch in.

Thanks to [Ben Smith](https://profiles.wordpress.org/benjamin-smith) for contributing the plugin's banner and icon image designs.

== Installation ==

= Minimum Requirements =

* WordPress 4.0 or greater
* WooCommerce 2.2 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of WooCommerce Product Fees, log in to your WordPress dashboard, navigate to the Plugins menu, and click Add New.

In the search field type “WooCommerce Product Fees” and click Search Plugins. Once you’ve found the plugin you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading this plugin and uploading it to your webserver via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Frequently Asked Questions ==

= Where can I get support or talk to other users? =

You can post here in the support section at WordPress.org. Just click on the Support tab to the right.

= Where can I request new features? =

Feel free to submit a feature request as an issue at the [WooCommerce Product Fees GitHub Repository](https://github.com/WPprodigy/woocommerce-product-fees). Be sure to check if this has already been suggested though. You can also suggest a feature in the support forums here at WordPress.org using the Support tab on the right.

= Where can I report bugs or contribute to the project? =

Bugs can be reported either in the support forum or preferably on the [WooCommerce Product Fees GitHub Repository](https://github.com/WPprodigy/woocommerce-product-fees).

== Screenshots ==

1. The product settings tab.
2. The variation settings.
3. The product fees settings area.
4. Fees shown at checkout.
5. Optional coupon setting.

== Changelog ==

= 1.3.0 - 12/20/2017 =
* Feature - Coupons have an option to remove fees in WC 3.0+
* Feature - Support for CSV Import/Export in WC 3.1+
* Feature - Fees have an option to inherit product tax settings.
* Patch - WooCommerce 3.0 Compatibility.
* Fix - The standard tax rate wasn't working/available.
* Fix - Added support for localized decimal point separators.
* Fix - Added WC Subscriptions compatibility.
* Fix - Fees with the same letters but different capitalizations will now match and can be merged.
* Fix - Don't add $0 fees for every product.
* Refactor - Sorry, had to do it again :). Reduced code and prepared for adding automated testing.

= 1.2.0 - 11/29/2015 =
* Feature - Ability to assign a tax class to be used on fees.
* Feature - Adds a setting to choose whether to combine fees with the same name or not.
* Feature - Variation products can now use the fee multiplier on the global product level.
* Fix - Various calculation issues in regards to multiple variation children being in the cart.
* Fix - Add language .pot file.
* Refactor - Completely re-wrote the plugin to make it more efficient and extendable.

= 1.1.1 - 10/1/2015 =
* Feature - Add fees with the same name.
* Fix - Display non-variation fees.

= 1.1.0 - 09/30/2015 =
* Feature - Variation specific fees.
* Fix - Save blank fee names and values.

= 1.0.0 - 08/25/2015 =
* Feature - Percentage based fees.
* Feature - Multiply by product quantity.
