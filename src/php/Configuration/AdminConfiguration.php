<?php

namespace Koen12344\AffiliateProductHighlights\Configuration;

use Koen12344\AffiliateProductHighlights\Admin\AdminPage;
use Koen12344\AffiliateProductHighlights\DependencyInjection\Container;
use Koen12344\AffiliateProductHighlights\DependencyInjection\ContainerConfigurationInterface;

class AdminConfiguration implements ContainerConfigurationInterface{

	public function modify( Container $container ) {
		$container['page.admin'] = $container->service(function($container) {
			return new AdminPage($container['plugin_path'], $container['plugin_url']);
		});
	}
}
