<?php

namespace Koen12344\ProductFrame\Configuration;

use Koen12344\ProductFrame\DependencyInjection\Container;
use Koen12344\ProductFrame\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\ProductFrame\Metabox\FeedMetabox;
use Koen12344\ProductFrame\Metabox\SelectionMetabox;

class MetaboxConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['metaboxes'] = $container->service(function(Container $container){
			return [
				new FeedMetabox(),
				new SelectionMetabox($container['plugin_path'], $container['plugin_url']),
			];
		});
	}
}
