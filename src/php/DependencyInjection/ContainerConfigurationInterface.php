<?php

namespace Koen12344\ProductFrame\DependencyInjection;

/**
 * A container configuration object configures a dependency injection container during the build process.
 *
 * @package PGMB\DependencyInjection
 */
interface ContainerConfigurationInterface {
	/**
	 * Modifies the given dependency injection container.
	 *
	 * @param Container $container
	 */
	public function modify(Container $container);
}
