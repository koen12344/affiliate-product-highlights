<?php

namespace Koen12344\AffiliateProductHighlights\Configuration;

use Koen12344\AffiliateProductHighlights\DependencyInjection\Container;
use Koen12344\AffiliateProductHighlights\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\AffiliateProductHighlights\EventManagement\EventManager;
use Koen12344\AffiliateProductHighlights\HookSubscribers\FeedCustomColumnSubscriber;
use Koen12344\AffiliateProductHighlights\HookSubscribers\MetaboxSubscriber;
use Koen12344\AffiliateProductHighlights\HookSubscribers\PostTypeSubscriber;
use Koen12344\AffiliateProductHighlights\HookSubscribers\RestApiSubscriber;

class EventManagementConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['service.event_manager'] = $container->service(function(Container $container) : EventManager{
			return new EventManager();
		});

		$container['subscribers'] = $container->service(function(Container $container){
			return [
				new MetaboxSubscriber($container['metaboxes']),
				new PostTypeSubscriber($container['posttypes']),
				new RestApiSubscriber($container['plugin_rest_namespace'], $container['rest_endpoints']),
				new FeedCustomColumnSubscriber(),
			];
		});
	}
}
