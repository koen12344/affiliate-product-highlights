<?php

namespace Koen12344\ProductFrame\Shortcodes;

use Koen12344\ProductFrame\Plugin;
use NumberFormatter;

class DisplayProductsShortcode implements ShortcodeInterface {

	private $plugin_url;
	private $plugin_version;

	public function __construct($plugin_url, $plugin_version) {

		$this->plugin_url = $plugin_url;
		$this->plugin_version = $plugin_version;
	}

	public function get_tag() {
		return 'productframe';
	}

	public function enqueue_styles() {
		wp_register_style('prfr-style', $this->plugin_url . 'css/front.css', [], $this->plugin_version);
		wp_enqueue_style('prfr-style');
	}
	public function maybe_sideload_image($image_id, $wp_media_id, $url){
		global $wpdb;
		if(!$wp_media_id || !wp_get_attachment_image_url($wp_media_id)){
			require_once(ABSPATH . 'wp-admin/includes/media.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');

			$wp_media_id = media_sideload_image($url.'#.jpg', 0, null, 'id');

			if(is_wp_error($wp_media_id)){
				return $url;
			}
			$image_data = [
				'wp_media_id' => (int)$wp_media_id
			];
			$wpdb->update($wpdb->prefix . 'prfr_images', $image_data, [
				'id' => $image_id,
			]);
		}
		return wp_get_attachment_image_url($wp_media_id, 'prfr-product-thumb');
	}
	public function render($attributes, $content){
		global $wpdb;

		$this->enqueue_styles();

		$attributes = shortcode_atts([
			'limit'         => 6,
			'product_ids'   => null,
			'feed_id'       => null,
			'search'        => null,
			'random'        => null,
			'selection'     => null,
		], $attributes, 'productframe');


		$params = [];
		$where_parts = [];

		if(!is_null($attributes['selection'])){
			$item_selection = get_post_meta((int)$attributes['selection'], '_prfr_item_selection', true);
			if(!is_array($item_selection) || empty($item_selection)){
				return esc_html__('The product selection doesn\'t contain any items', 'productframe');
			}
			$ids                       = array_keys($item_selection);
			$attributes['product_ids'] = implode(',', $ids);
		}

		if(!is_null($attributes['product_ids'])){
			$product_ids = explode(',', $attributes['product_ids']);
			$placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
			$where_parts[] = "id IN ({$placeholders})";
			$params = array_merge($params, $product_ids);
		}

		if(!is_null($attributes['search'])){
			$search_term = $wpdb->esc_like($attributes['search']);
			$where_parts[] = "product_name LIKE '%".esc_sql($search_term)."%'";
			$where_parts[] = "in_latest_import=1";
		}



		$where_clause = '';
		if ( ! empty( $where_parts ) ) {
			$where_clause = ' WHERE ' . implode( ' AND ', $where_parts );
		}

		$limit = "";
		if(!is_null($attributes['random'])){
			$limit .= " ORDER BY RAND()";
		}

		$limit .= " LIMIT %d";
		$params[] = $attributes['limit'];

		$prepared = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}prfr_products $where_clause $limit",
			$params
		);

		$cache_key = 'prfr_'.md5($prepared);

		$products = wp_cache_get($cache_key, 'prfr');

		if(!$products){
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared  -- The query is prepared, but also used as md5 hashed key to fetch from the database
			$results = $wpdb->get_results($prepared, OBJECT_K);

			if(!$results){
				return $wpdb->print_error();
			}

			$product_ids = wp_parse_id_list(array_column($results, 'id'));
			$placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
			$images = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}prfr_images WHERE product_id IN ({$placeholders})", $product_ids));

			//		$images_query = "SELECT * FROM {$wpdb->prefix}prfr_images WHERE product_id IN ({$product_ids})";
			//		$images = $wpdb->get_results($images_query);
			/*
			 * 			$product_ids = explode(',', $atts['product_ids']);
				$placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
			 */



			$images_by_id = [];
			foreach($images as $image){
				//So we only get the first image
				if(!isset($images_by_id[$image->product_id])){
					$images_by_id[$image->product_id] = $image;
				}
			}

			$products = array_map(function($product) use ( $images_by_id ) {
				$product->images = [];
				if (isset($images_by_id[$product->id])) {
					$product->images[] = $this->maybe_sideload_image($images_by_id[$product->id]->id, $images_by_id[$product->id]->wp_media_id, $images_by_id[$product->id]->image_url);
				}
				return $product;
			}, $results);
			wp_cache_set($cache_key, $products, 'prfr', 3600*24);
		}

		if( $attributes['limit'] > 1){
			$output = '<div class="prfr-products-multiple">';
			foreach($products as $product){
				$output .= $this->draw_product($product);
			}
			$output .= '</div>';
			return $output;
		}

		$output = '<div class="prfr-products-single">';
		$output .= $this->draw_product(reset($products));
		$output .= '</div>';

		return $output;

	}

	public function draw_product($product){
		$locale = get_locale();
		$fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );
		$product_url = esc_url(trailingslashit(home_url('/prfr/' . urlencode($product->slug))));

		$has_sale = $product->product_original_price > $product->product_price;



		$output = '<div class="prfr-product'.($has_sale ? ' prfr-sale-product' : '').'">';
		$output .= get_the_post_thumbnail($product->feed_id, 'prfr-logo');
		$output .= '<a target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'"><h3>'.mb_strimwidth(esc_html($product->product_name), 0, 70, '...').'</h3></a>';
		if (!empty($product->images)) {
			$output .= '<div class="prfr-product-image">';
			$output .= '<a target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'"><img src="' . esc_url($product->images[0]) . '" alt="' . esc_attr($product->product_name) . '"></a>';
			$output .= '</div>';
		}
		$output .= '<div class="prfr-product-description">'. mb_strimwidth(wp_strip_all_tags($product->product_description), 0, 160, '...').'</div>';
		$output .= '<div class="prfr-product-price">'.numfmt_format_currency($fmt, $product->product_price, $product->product_currency).($has_sale ? '<span class="prfr-original-price">'.numfmt_format_currency($fmt, $product->product_original_price, $product->product_currency).'</span>':'').'</div>';
		$output .= '<a class="prfr-button-link" target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'">'.esc_html__('View', 'productframe').'</a>';
		$output .= '</div>';

		return $output;
	}


}
