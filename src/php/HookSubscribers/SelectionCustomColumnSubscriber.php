<?php

namespace Koen12344\AffiliateProductHighlights\HookSubscribers;

use Koen12344\AffiliateProductHighlights\EventManagement\SubscriberInterface;

class SelectionCustomColumnSubscriber implements SubscriberInterface{

	public static function get_subscribed_hooks(): array {
		return [
			'manage_phft-selections_posts_columns' => 'add_custom_columns',
			'manage_phft-selections_posts_custom_column' => ['show_custom_column', 10, 2]
		];
	}

	public function add_custom_columns($columns) {
		$columns['no_longer_in_feed'] = esc_html__('Items no longer in feed', 'affiliate-product-highlights');
		return $columns;
	}

	public function show_custom_column($column, $post_id) {
		global $wpdb;
		if($column === 'no_longer_in_feed'){
			$selection = get_post_meta($post_id, '_phft_item_selection', true);
			if(!is_array($selection) || empty($selection)){
				echo "0";
				return;
			}
			$ids = array_keys($selection);

			$placeholders = implode(',', array_fill(0, count($ids), '%d'));

			echo (int)$wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}phft_products WHERE id IN ({$placeholders}) AND in_latest_import=0",
				$ids
			));
		}
	}
}
