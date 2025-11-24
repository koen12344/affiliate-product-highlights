<?php

namespace Koen12344\ProductFrame\Configuration;

use Koen12344\ProductFrame\DependencyInjection\Container;
use Koen12344\ProductFrame\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\ProductFrame\Logger\Logger;

class LoggerConfiguration implements ContainerConfigurationInterface {

	/**
	 * @inheritDoc
	 */
	public function modify( Container $container ) {
		$container->register('Logger', function($container) {
			global $wpdb;
			return new Logger($wpdb);
		});
	}
}
