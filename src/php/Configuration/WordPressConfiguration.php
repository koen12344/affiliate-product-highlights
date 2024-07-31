<?php

namespace Koen12344\AffiliateProductHighlights\Configuration;

use Koen12344\AffiliateProductHighlights\DependencyInjection\Container;
use Koen12344\AffiliateProductHighlights\DependencyInjection\ContainerConfigurationInterface;

class WordPressConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['wpdb'] = $container->service(function(Container $container){
			global $wpdb;
			return $wpdb;
		});
	}
}
