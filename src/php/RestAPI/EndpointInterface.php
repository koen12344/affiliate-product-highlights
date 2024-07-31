<?php

namespace Koen12344\AffiliateProductHighlights\RestAPI;

use WP_REST_Request;

interface EndpointInterface {
	public function get_arguments(): array;

	public function respond(WP_REST_Request $request);

	public function validate(WP_REST_Request $request): bool;

	public function get_methods() : array;

	public function get_path() : string;
}
