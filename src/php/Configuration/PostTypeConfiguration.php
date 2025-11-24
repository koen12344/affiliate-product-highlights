<?php

namespace Koen12344\ProductFrame\Configuration;

use Koen12344\ProductFrame\DependencyInjection\Container;
use Koen12344\ProductFrame\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\ProductFrame\PostTypes\FeedPostType;
use Koen12344\ProductFrame\PostTypes\SelectionsPostType;

class PostTypeConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['posttypes'] = $container->service(function(Container $container){
			return [
				'selections_post_type' => new SelectionsPostType($container['plugin_domain']),
				'feed_post_type' => new FeedPostType($container['plugin_domain']),
			];
		});
	}
}
