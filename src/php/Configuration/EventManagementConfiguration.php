<?php

namespace Koen12344\ProductFrame\Configuration;

use Koen12344\ProductFrame\DependencyInjection\Container;
use Koen12344\ProductFrame\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\ProductFrame\EventManagement\EventManager;
use Koen12344\ProductFrame\HookSubscribers\AdminPageSubscriber;
use Koen12344\ProductFrame\HookSubscribers\DeleteFeedSubscriber;
use Koen12344\ProductFrame\HookSubscribers\ExternalRedirectSubscriber;
use Koen12344\ProductFrame\HookSubscribers\FeedCustomColumnSubscriber;
use Koen12344\ProductFrame\HookSubscribers\MetaboxSubscriber;
use Koen12344\ProductFrame\HookSubscribers\PostTypeSubscriber;
use Koen12344\ProductFrame\HookSubscribers\RestApiSubscriber;
use Koen12344\ProductFrame\HookSubscribers\SaveFeedSubscriber;
use Koen12344\ProductFrame\HookSubscribers\SelectionCustomColumnSubscriber;
use Koen12344\ProductFrame\HookSubscribers\ShortcodeSubscriber;
use Koen12344\ProductFrame\HookSubscribers\UpdateFeedsSubscriber;

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
				new SelectionCustomColumnSubscriber(),
				new AdminPageSubscriber($container['page.admin'], $container['plugin_dashicon']),
				new ExternalRedirectSubscriber($container['Logger']),
				new DeleteFeedSubscriber(),
				new SaveFeedSubscriber($container['BackgroundProcess']),
				new UpdateFeedsSubscriber($container['BackgroundProcess']),
				new ShortcodeSubscriber($container['shortcodes']),
			];
		});
	}
}
