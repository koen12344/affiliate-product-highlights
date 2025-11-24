<?php

namespace Koen12344\ProductFrame\Configuration;

use Koen12344\ProductFrame\DependencyInjection\Container;
use Koen12344\ProductFrame\DependencyInjection\ContainerConfigurationInterface;

class WordPressConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['wpdb'] = $container->service(function(Container $container){
			global $wpdb;
			return $wpdb;
		});
	}
}
