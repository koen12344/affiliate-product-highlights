<?php

namespace Koen12344\AffiliateProductHighlights\Configuration;

use Koen12344\AffiliateProductHighlights\DependencyInjection\Container;
use Koen12344\AffiliateProductHighlights\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\AffiliateProductHighlights\RestAPI\GetItemsEndpoint;
use Koen12344\AffiliateProductHighlights\RestAPI\GetSelectionEndpoint;
use Koen12344\AffiliateProductHighlights\RestAPI\SaveSelectionEndpoint;

class RestApiConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['rest_endpoints'] = $container->service(function(Container $container){
			return [
				'get_items_endpoint' => new GetItemsEndpoint($container['wpdb']),
				'save_selection_endpoint' => new SaveSelectionEndpoint(),
				'get_selection_endpoint' => new GetSelectionEndpoint(),
			];
		});
	}
}
