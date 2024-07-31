<?php

namespace Koen12344\AffiliateProductHighlights\Configuration;

use Koen12344\AffiliateProductHighlights\DependencyInjection\Container;
use Koen12344\AffiliateProductHighlights\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\AffiliateProductHighlights\Metabox\FeedMetabox;
use Koen12344\AffiliateProductHighlights\Metabox\SelectionMetabox;

class MetaboxConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['metaboxes'] = $container->service(function(Container $container){
			return [
				new FeedMetabox((string)$container['posttypes']['feed_post_type']),
				new SelectionMetabox((string)$container['posttypes']['selections_post_type']),
			];
		});
	}
}
