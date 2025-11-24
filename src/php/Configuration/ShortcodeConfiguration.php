<?php

namespace Koen12344\ProductFrame\Configuration;


use Koen12344\ProductFrame\DependencyInjection\Container;
use Koen12344\ProductFrame\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\ProductFrame\Shortcodes\DisplayProductsShortcode;
use Koen12344\ProductFrame\Shortcodes\InlineLinkShortcode;
use Koen12344\ProductFrame\Shortcodes\LegacyDisplayProductsShortcode;

class ShortcodeConfiguration implements ContainerConfigurationInterface {

	/**
	 * @inheritDoc
	 */
	public function modify( Container $container ) {
		$container['shortcodes'] = $container->service(function($container) {
			return [
				'inline_link_shortcode' => new InlineLinkShortcode(),
				'display_products_shortcode' => new DisplayProductsShortcode($container['plugin_url'], $container['plugin_version']),
				'legacy_display_products_shortcode' => new LegacyDisplayProductsShortcode($container['plugin_url'], $container['plugin_version']),
			];
		});
	}
}
