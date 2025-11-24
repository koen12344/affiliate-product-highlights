<?php

namespace Koen12344\ProductFrame\Configuration;

use Koen12344\ProductFrame\BackgroundProcessing\BackgroundProcess;
use Koen12344\ProductFrame\DependencyInjection\Container;
use Koen12344\ProductFrame\DependencyInjection\ContainerConfigurationInterface;

class BackgroundProcessConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container->register('BackgroundProcess', function($container){
			return new BackgroundProcess($container['Logger']);
		});
	}
}
