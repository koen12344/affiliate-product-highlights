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

		$where_parts = [];
		$params = [];

		if ( ! empty( $search_term ) ) {
			$where_parts[] = "(product_name LIKE %s OR product_ean LIKE %s OR sku LIKE %s)";
			$params[] = '%' . $search_term . '%';
			$params[] = '%' . $search_term . '%';
			$params[] = '%' . $search_term . '%';
		}

		$filters = $request->get_param('filters');
		if(!empty($filters)) {
			foreach ($filters as $filter) {
				if(empty($filter['value'])){
					continue;
				}
				switch($filter['field']) {
					case 'in_selection':
						$include = wp_validate_boolean($filter['value']);
						$ids = array_map('intval', array_keys($selection));
						if(!empty($ids)) {
							$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

							$where_parts[] = "id " . ( !$include ? "NOT " : "" ) . "IN (" . $placeholders . ")";

							$params = array_merge( $params, $ids );
						}else{
							$where_parts[] = $include ? '1 = 0' : '1 = 1'; //little hack to prevent mysql error and return no results, or all results if there are no products in the selection
						}
						break;
					case 'in_latest_import':
						$in_import = wp_validate_boolean($filter['value']);
						$where_parts[] = "in_latest_import=%d";
						$params[] = $in_import ? 1 : 0;
						break;
					case 'feed':
						$feed_id = intval($filter['value']);
						$where_parts[] = "feed_id=%d";
						$params[] = $feed_id;
				}
			}
		}

		$where_clause = '';
		if ( ! empty( $where_parts ) ) {
			$where_clause = ' WHERE ' . implode( ' AND ', $where_parts );
		}


		// Total count query
		$count_query = "SELECT COUNT(*) FROM {$this->wpdb->prefix}phft_products $where_clause";
		$total_items = (int) $this->wpdb->get_var($this->wpdb->prepare( $count_query, $params ));

		// Pagination
		$per_page = max( 1, (int) $request->get_param('per_page') );
		$page = max( 1, (int) $request->get_param('page') );
		$offset = ( $page - 1 ) * $per_page;


		$order = $request->get_param('order') ? $request->get_param('order') : 'asc';
		$orderby = $request->get_param('orderby');

		$order_by_query = "";
		if(!empty($orderby)) {
			$fields = [
				'id'            => 'id',
				'product_name' => 'product_name',
				'in_latest_import' => 'in_latest_import',
				'product_price' => 'product_price',
			];
			$direction = strtoupper($order);
			$direction = in_array( $direction, [ 'ASC', 'DESC' ], true ) ? $direction : 'ASC';
			$order_by_query = "ORDER BY ".($fields[$orderby] ?? 'product_name')." {$direction}";
		}

		// Main data query
		$data_query = "
		SELECT *
		FROM {$this->wpdb->prefix}phft_products
		$where_clause
		$order_by_query
		LIMIT %d OFFSET %d
	";
		$params[] = $per_page;
		$params[] = $offset;
		$prepared_query = $this->wpdb->prepare( $data_query, $params );
		$results = $this->wpdb->get_results( $prepared_query );

		// Format and augment results
		$locale = get_locale();
		$fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );

		$feed_ids = array_unique( array_column($results, 'feed_id') );

		$product_ids = implode(',', array_map('intval', array_column($results, 'id')));
		$images_query = "SELECT product_id, image_url FROM {$this->wpdb->prefix}phft_images WHERE product_id IN ({$product_ids})";

		$images = $this->wpdb->get_results($images_query);

		$images_by_id = [];
		foreach($images as $image){
			//So we only get the first image
			if(!isset($images_by_id[$image->product_id])){
				$images_by_id[$image->product_id] = $image->image_url;
			}
		}

		$feeds = get_posts( [
			'post__in' => $feed_ids,
			'post_type' => 'phft-feeds',
			'numberposts' => -1,
		] );

		$feeds_by_id = [];
		foreach ( $feeds as $feed ) {
			$feeds_by_id[ $feed->ID ] = $feed;
		}

		$products = array_map(function($product) use ($fmt, $selection, $images_by_id, $feeds_by_id){
			$product->product_price = numfmt_format_currency( $fmt, $product->product_price, $product->product_currency );
			$product->feed_name     = $feeds_by_id[ $product->feed_id ]->post_title ?? '';
			$product->feed_url      = get_edit_post_link( $product->feed_id, null );
			$product->product_description = html_entity_decode(wp_trim_words(wp_strip_all_tags( $product->product_description ), 15),ENT_QUOTES | ENT_HTML5, 'UTF-8');
			$product->in_selection = array_key_exists($product->id, $selection);
			$product->image_url = $images_by_id[$product->id] ?? null;
			return $product;
		}, $results);

		$total_pages = (int) ceil( $total_items / $per_page );

		return rest_ensure_response( [
			'items' => $products,
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
