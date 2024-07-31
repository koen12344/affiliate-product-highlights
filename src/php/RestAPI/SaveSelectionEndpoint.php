<?php

namespace Koen12344\AffiliateProductHighlights\RestAPI;

use WP_REST_Request;

class SaveSelectionEndpoint implements EndpointInterface {

	public function get_arguments(): array {
		return [
			'selection_id' => [
				'type' => 'integer',
				'required' => true,
				'sanitize_callback' => function($value){
					return (int)$value;
				}
			],
			'selection' => [
				'required' => true,
				'type' => 'array',
				'sanitize_callback' => function($value){
					return $value;
				}
			],
		];
	}

	public function respond( WP_REST_Request $request ) {
		$selection_id = (int)$request->get_param('selection_id');

		$selection = $request->get_param('selection');

		update_post_meta($selection_id, '_phft_item_selection', $selection);

		return rest_ensure_response(true);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [\WP_REST_Server::CREATABLE];
	}

	public function get_path(): string {
		return '/selection/';
	}
}
