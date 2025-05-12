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
		$search_term_raw = $request->get_param('search');

		$json_params = $request->get_json_params();

		$selection = $json_params['selection'];

		$search_term = $this->wpdb->esc_like( $search_term_raw );
		$where_clause = '';
		if ( ! empty( $search_term ) ) {
			$where_clause = $this->wpdb->prepare(
				" WHERE product_name LIKE %s",
				'%' . $search_term . '%'
			);
		}
		$filters = $request->get_param('filters');
		if(!empty($filters)) {
			foreach ($filters as $filter) {
				if($filter['field'] === 'in_selection'){

					$include = wp_validate_boolean($filter['value']);

					$ids = implode(',', array_keys($selection));

					if($include) {
						$where_clause = " WHERE p.id IN (" . $ids . ")";
					}else{
						$where_clause = " WHERE p.id NOT IN (" . $ids . ")";
					}


				}
			}
		}

		// Total count query
		$count_query = "SELECT COUNT(*) FROM {$this->wpdb->prefix}phft_products p $where_clause";
		$total_items = (int) $this->wpdb->get_var( $count_query );

		// Pagination
		$per_page = max( 1, (int) $request->get_param('per_page') );
		$page = max( 1, (int) $request->get_param('page') );
		$offset = ( $page - 1 ) * $per_page;

		// Main data query
		$data_query = "
		SELECT p.*, i.image_url, i.wp_media_id, i.id AS image_id
		FROM {$this->wpdb->prefix}phft_products p
		LEFT JOIN {$this->wpdb->prefix}phft_images i ON p.id = i.product_id
		$where_clause
		LIMIT %d OFFSET %d
	";
		$prepared_query = $this->wpdb->prepare( $data_query, $per_page, $offset );
		$results = $this->wpdb->get_results( $prepared_query );

		// Format and augment results
		$locale = get_locale();
		$fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );

		$feed_ids = array_map( function ( $item ) {
			return $item->feed_id;
		}, $results );
		$unique_feed_ids = array_unique( $feed_ids );

		$feeds = get_posts( [
			'post__in' => $unique_feed_ids,
			'post_type' => 'phft-feeds',
		] );

		$feeds_by_id = [];
		foreach ( $feeds as $feed ) {
			$feeds_by_id[ $feed->ID ] = $feed;
		}

		foreach ( $results as $result ) {
			$result->product_price = numfmt_format_currency( $fmt, $result->product_price, $result->product_currency );
			$result->feed_name     = $feeds_by_id[ $result->feed_id ]->post_title ?? '';
			$result->feed_url      = get_edit_post_link( $result->feed_id, null );
			$result->product_description = wp_trim_words(wp_strip_all_tags( $result->product_description ), 15);
			$result->in_selection = array_key_exists($result->id, $selection);
		}

		$total_pages = (int) ceil( $total_items / $per_page );

		return rest_ensure_response( [
			'items' => $results,
			'paginationInfo' => [
				'totalPages' => $total_pages,
				'totalItems' => $total_items,
			]
		] );
	}


	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [ WP_REST_Server::CREATABLE];
	}

	public function get_path(): string {
		return '/items/';
	}
}
