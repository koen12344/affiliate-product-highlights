<?php

namespace Koen12344\AffiliateProductHighlights\HookSubscribers;

use Koen12344\AffiliateProductHighlights\EventManagement\SubscriberInterface;

class FeedCustomColumnSubscriber implements SubscriberInterface{

	public static function get_subscribed_hooks(): array {
		return [
			'manage_phft-feeds_posts_columns' => 'add_custom_columns',
			'manage_phft-feeds_posts_custom_column' => ['show_custom_column', 10, 2]
		];
	}

	public function add_custom_columns($columns) {
		$columns['imported_items'] = esc_html__('Imported Items', 'affiliate-product-highlights');
		$columns['last_import'] = esc_html__('Last Import', 'affiliate-product-highlights');
		return $columns;
	}

	public function show_custom_column($column, $post_id) {
		global $wpdb;
		if ($column === 'imported_items') {
			echo $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}phft_products WHERE feed_id = %d",
				$post_id
			));
		}elseif($column === 'last_import'){
			$last_import = get_post_meta($post_id, '_phft_last_import', true);
			echo wp_date( get_option( 'date_format' ), $last_import );
			echo "&nbsp;";
			echo wp_date( get_option( 'time_format' ), $last_import );
		}
	}
}
