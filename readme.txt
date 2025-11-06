=== Affiliate Product Highlights ===
Contributors: koen12344
Donate link: https://koenreus.com
Tags: tradetracker, adtraction, affiliate, feed, products
Requires at least: 5.1
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 0.4.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A beautiful way to display products from affiliate network product feeds on your website. Currently supports Daisycon, TradeTracker and AdTraction.

== Description ==

With the **Affiliate Product Highlights** plugin for WordPress you can create beautiful in-content sections showing relevant
products from product feeds of various affiliate networks.

Networks currently supported:
* AdTraction
* TradeTracker
* Daisycon

**Features:**

* **Automatic product feed import**: Product feeds are updated daily for up-to-date pricing and product availability
* **Cloaked links**: Affiliate links are redirected through your own website URL
* **Fast & efficient**: Product data is stored locally, product images are sideloaded and optimized
* **Flexible shortcode**: Show random products, specific products, search by (partial) product name

See the plugin in action [here](https://projectplatenspelers.nl/).

[![Twitter URL](https://img.shields.io/twitter/url/https/twitter.com/KoenReus.svg?style=social&label=Follow%20%40KoenReus)](https://twitter.com/KoenReus)

== Installation ==

1. Download [the latest version](https://github.com/koen12344/affiliate-product-highlights/releases/latest)
1. Upload the plugin through 'Plugins' > 'Add New' > 'Upload plugin' in your WordPress Dashboard
1. Add one or more affiliate product feeds through 'Affiliate Product Highlights' > 'Feeds'
1. Place the [product-highlights] shortcode wherever you want to display your affiliate products using attributes below

### Supported shortcode attributes

* `selection`: An ID of a selection of products generated through 'Affiliate Product Highlights' > 'Selections'
* `limit`: The amount of products that should be displayed (default: 6)
* `product_ids`: Display specific product IDs. Separated by a comma, e.g. 123,323,312 (currently no easy way to get these IDs apart from going into PHPMyAdmin)
* `search`: Show only products containing this word or sentence in their title (may be inefficient with a lot of products in the database)
* `random`: Randomize the results

#### Examples

* `[product-highlights selection=107]`: Show products from the selection with ID 107
* `[product-highlights selection=107 limit=1 random=1]`: Show a single random product from the selection with ID 107
* `[product-highlights product_ids="2304,2306,2307,665"]`: Show products with specific IDs

### Styling

The plugin comes with minimal styling that can be easily adjusted with some custom CSS.

* `phft-products-multiple` (div): Wrapper class around all products (if the `limit` parameter is higher than 1)
* `phft-product` (div): Product wrapper class
* `phft-product-image` (div): Product image wrapper (contains a > img)
* `phft-product-description` (div)
* `phft-product-price` (div)
* `phft-button-link` (a): The call-to-action button

Colors can be adjusted by overriding the default CSS variables:

* `--phft-button-text-color`: Button text color (default: #fff)
* `--phft-button-background-color`: Button background color (default: #611431)
* `--phft-button-hover-color`: Button hover color (default: #363636)
* `--phft-product-border-color`: Color for the border around the individual products

== Frequently Asked Questions ==

== Screenshots ==

1. Plugin in action on the frontend
2. Making a selection of specific products

== Changelog ==

= 0.4.3 =
* Improved: Various improvements to pass WP Plugin Check
* Fix #29: Products with ASCII characters in slug not properly redirected
* Fix: Images not loading for some feeds in the selection creator

= 0.4.2 =
* Fix #25: GitHub updater not working

= 0.4.1 =
* Fix: Duplicated products when images URLs in feed are changed

= 0.4.0 =
* Added: Function to clear product thumbnail cache (#16)
* Added: Allow filtering product selections by feed (#7)
* Improved: Retain selection view settings in user memory (#5)
* Improved: Make "Selections" primary menu item (#4)
* Improved: Allow searching products by EAN/SKU in selection creator (#21)
* Improved: Do not show products that are no longer available in dynamic shortcodes (#8)
* Fix: Incorrect thumbnail cropping on non-square product images (#13)
* Fix: Descriptions showing html characters in selection creator (#19)
* Fix: Selection showing all products when filtering by Selected = Yes, despite none being selected (#18)
* Fix: Fatal error when a selection with no products is referenced (#12)
* Fix: Not all products being imported from TradeTracker feeds (#10)

= 0.2.0 =
* Added: Uninstall function
* Added: Daisycon support
* Added: Shortcode for direct links to products
* Improved: SQL logic

= 0.1.1 =
* Added: Product ID and Feed in selection section
* Added: AdTraction sale price
* Fix: product_ids parameter
* Fix: selection logic
* Fix: AdTraction prices above 999

= 0.1.0 =
* Initial public alpha release

== Upgrade Notice ==
