<?php

namespace Koen12344\AffiliateProductHighlights\Configuration;

use Koen12344\AffiliateProductHighlights\DependencyInjection\Container;
use Koen12344\AffiliateProductHighlights\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\AffiliateProductHighlights\RestAPI\ClearThumbnailEndpoint;
use Koen12344\AffiliateProductHighlights\RestAPI\GetItemsEndpoint;
use Koen12344\AffiliateProductHighlights\RestAPI\GetSelectionEndpoint;
use Koen12344\AffiliateProductHighlights\RestAPI\SaveSelectionEndpoint;
use Koen12344\AffiliateProductHighlights\RestAPI\SaveViewEndpoint;

class RestApiConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['rest_endpoints'] = $container->service(function(Container $container){
			return [
				'get_items_endpoint' => new GetItemsEndpoint($container['wpdb']),
				'save_selection_endpoint' => new SaveSelectionEndpoint(),
				'get_selection_endpoint' => new GetSelectionEndpoint(),
				'save_view_endpoint' => new SaveViewEndpoint(),
				'clear_thumbnails_endpoint' => new ClearThumbnailEndpoint($container['wpdb']),
			];
		});
	}
}
