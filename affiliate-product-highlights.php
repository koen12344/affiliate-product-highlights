<?php
/**
 * Plugin Name:     Affiliate Product Highlights
 * Plugin URI:      https://koenreus.com
 * Description:     Plugin to display products from various affiliate network product feeds in WordPress
 * Author:          Koen Reus
 * Author URI:      https://koenreus.com
 * Text Domain:     affiliate-product-highlights
 * Domain Path:     /languages
 * Version:         0.4.4
 * License:         GPLv2 or later
 *
 * @package         Affiliate_Product_Highlights
 */

use Koen12344\AffiliateProductHighlights\Plugin;

require 'vendor/autoload.php';

register_activation_hook(__FILE__, ['Koen12344\AffiliateProductHighlights\Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['Koen12344\AffiliateProductHighlights\Plugin', 'deactivate']);
register_uninstall_hook(__FILE__, ['Koen12344\AffiliateProductHighlights\Plugin', 'uninstall']);

$affiliate_product_highlights = new Plugin(__FILE__);

add_action('after_setup_theme', [$affiliate_product_highlights, 'init']);
