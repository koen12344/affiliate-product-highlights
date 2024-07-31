<?php

namespace Koen12344\AffiliateProductHighlights\RestAPI;

use NumberFormatter;
use WP_REST_Request;
use WP_REST_Server;
use wpdb;

class GetItemsEndpoint implements EndpointInterface {

	/**
	 * @var wpdb
	 */
	private $wpdb;

	public function __construct(wpdb $wpdb) {
		$this->wpdb = $wpdb;
	}

	public function get_arguments(): array {
		return [
			'search' => [
				'required' => false,
				'sanitize_callback' => function($value){
					return filter_var($value, FILTER_SANITIZE_STRING);
				}
			]
		];
	}

	public function respond( WP_REST_Request $request ) {
		$query = "SELECT p.*, i.image_url, i.wp_media_id, i.id AS image_id FROM {$this->wpdb->prefix}phft_products p LEFT JOIN {$this->wpdb->prefix}phft_images i ON p.id = i.product_id";

		$search_term = $this->wpdb->esc_like($request->get_param('search'));
		if(!empty($search_term)){
			$query .= " WHERE product_name LIKE '%".esc_sql($search_term)."%'";
		}


//		$query = $wpdb->prepare($query,
//			$params
//		);

		$query .= " LIMIT 10";

		$results = $this->wpdb->get_results($query);

		$locale = get_locale();
		$fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );

		foreach($results as $result){
			$result->product_price = numfmt_format_currency($fmt, $result->product_price, $result->product_currency);
		}

		return rest_ensure_response($results);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [ WP_REST_Server::READABLE];
	}

	public function get_path(): string {
		return '/items/';
	}
}
