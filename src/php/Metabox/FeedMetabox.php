<?php

namespace Koen12344\AffiliateProductHighlights\Metabox;


use WP_Post;

class FeedMetabox extends PostTypeMetabox {


	public function get_identifier(): string {
		return 'phft_feed_metabox';
	}

	public function get_title(): string {
		return __('Feed settings', 'affiliate-product-highlights');
	}

	public function render(WP_Post $post) {

		$last_error = get_post_meta($post->ID, '_phft_last_error', true);
		$feed_url = get_post_meta($post->ID, '_phft_feed_url', true);

		$value = $feed_url ?: '';

		if(!empty($last_error)){
			echo sprintf(__('Last error: %s', 'affiliate-product-highlights'), $last_error);
		}

		echo "Feed Url: <input type='text' name='_phft_feed_url' value='{$value}' />";
	}
}
