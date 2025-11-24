<?php
/**
 * Plugin Name:     ProductFrame – Curated products from affiliate feeds
 * Plugin URI:      https://github.com/koen12344/productframe
 * Description:     Plugin to display products from various affiliate network product feeds in WordPress
 * Author:          Koen Reus
 * Author URI:      https://koenreus.com
 * Text Domain:     productframe
 * Domain Path:     /languages
 * Version:         0.5.0
 * License:         GPLv2 or later
 *
 * @package         ProductFrame
 */

use Koen12344\ProductFrame\Plugin;

require 'vendor/autoload.php';

register_activation_hook(__FILE__, ['Koen12344\ProductFrame\Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['Koen12344\ProductFrame\Plugin', 'deactivate']);
register_uninstall_hook(__FILE__, ['Koen12344\ProductFrame\Plugin', 'uninstall']);

$prfr_plugin = new Plugin(__FILE__);

add_action('after_setup_theme', [$prfr_plugin, 'init']);
