<?php

namespace Koen12344\ProductFrame\RestAPI;

use WP_REST_Request;

class GetSelectionEndpoint implements EndpointInterface {

	public function get_arguments(): array {
		return [
			'selection_id' => [
				'required' => true,
				'sanitize_callback' => function($value){
					return (int)$value;
				}
			]
		];
	}

	public function respond( WP_REST_Request $request ) {
		$selection_id = (int)$request->get_param('selection_id');

		$selection = get_post_meta($selection_id, '_prfr_item_selection', true);

		return rest_ensure_response($selection);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [\WP_REST_Server::READABLE];
	}

	public function get_path(): string {
		return '/selection/';
	}
}
