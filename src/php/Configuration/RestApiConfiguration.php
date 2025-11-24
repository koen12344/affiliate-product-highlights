<?php

namespace Koen12344\ProductFrame\Configuration;

use Koen12344\ProductFrame\DependencyInjection\Container;
use Koen12344\ProductFrame\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\ProductFrame\RestAPI\ClearThumbnailEndpoint;
use Koen12344\ProductFrame\RestAPI\GetItemsEndpoint;
use Koen12344\ProductFrame\RestAPI\GetSelectionEndpoint;
use Koen12344\ProductFrame\RestAPI\SaveSelectionEndpoint;
use Koen12344\ProductFrame\RestAPI\SaveViewEndpoint;

class RestApiConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['rest_endpoints'] = $container->service(function(Container $container){
			return [
				'get_items_endpoint' => new GetItemsEndpoint(),
				'save_selection_endpoint' => new SaveSelectionEndpoint(),
				'get_selection_endpoint' => new GetSelectionEndpoint(),
				'save_view_endpoint' => new SaveViewEndpoint(),
				'clear_thumbnails_endpoint' => new ClearThumbnailEndpoint($container['wpdb']),
			];
		});
	}
}
