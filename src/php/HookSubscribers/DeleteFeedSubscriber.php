<?php

namespace Koen12344\ProductFrame\HookSubscribers;

use Koen12344\ProductFrame\EventManagement\SubscriberInterface;
use Koen12344\ProductFrame\PostTypes\FeedPostType;

class DeleteFeedSubscriber implements SubscriberInterface {

	public static function get_subscribed_hooks(): array {
		return [
			'before_delete_post' => ['delete_feed', 10, 2]
		];
	}

	public function delete_feed($post_id, \WP_Post $post){
		if($post->post_type !== FeedPostType::FEED_POST_TYPE){
			return;
		}

		global $wpdb;

		$wp_media = $wpdb->get_results($wpdb->prepare("SELECT wp_media_id FROM {$wpdb->prefix}prfr_images WHERE feed_id = %d AND wp_media_id > 0", $post_id));

		if($wp_media){
			foreach($wp_media as $media){
				wp_delete_attachment($media->wp_media_id, true);
			}
		}

		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}prfr_products WHERE feed_id = %d", $post_id));
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}prfr_images WHERE feed_id = %d", $post_id));
	}
}
