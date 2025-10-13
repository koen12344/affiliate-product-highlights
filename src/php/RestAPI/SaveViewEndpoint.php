<?php

namespace Koen12344\AffiliateProductHighlights\RestAPI;

use WP_REST_Request;

class SaveViewEndpoint implements EndpointInterface {

	public function get_arguments(): array {
		return [
			'view' => [
				'required' => true,
				'type' => 'array',
				'sanitize_callback' => function($value){
					return $value;
				}
			],
		];
	}

	public function respond( WP_REST_Request $request ) {
		$view = $request->get_param('view');

		update_user_meta(get_current_user_id(), 'phft_selection_view', $view);

		return rest_ensure_response(true);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [\WP_REST_Server::CREATABLE];
	}

	public function get_path(): string {
		return '/view/';
	}
}
