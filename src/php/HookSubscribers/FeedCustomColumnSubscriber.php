<?php

namespace Koen12344\ProductFrame\HookSubscribers;

use Koen12344\ProductFrame\EventManagement\SubscriberInterface;

class FeedCustomColumnSubscriber implements SubscriberInterface{

	public static function get_subscribed_hooks(): array {
		return [
			'manage_prfr-feeds_posts_columns' => 'add_custom_columns',
			'manage_prfr-feeds_posts_custom_column' => ['show_custom_column', 10, 2]
		];
	}

	public function add_custom_columns($columns) {
		$columns['imported_items'] = esc_html__('Imported Items', 'productframe');
		$columns['no_longer_in_feed'] = esc_html__('No longer in feed', 'productframe');
		$columns['last_import'] = esc_html__('Last Import', 'productframe');
		return $columns;
	}

	public function show_custom_column($column, $post_id) {
		global $wpdb;
		if ($column === 'imported_items') {
			echo (int)$wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}prfr_products WHERE feed_id = %d",
				$post_id
			));
		}elseif($column === 'no_longer_in_feed'){
			echo (int)$wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}prfr_products WHERE feed_id = %d AND in_latest_import=0",
				$post_id
			));
		}elseif($column === 'last_import'){
			$last_import = get_post_meta($post_id, '_prfr_last_import', true);
			echo esc_html($last_import);
		}
	}
}
