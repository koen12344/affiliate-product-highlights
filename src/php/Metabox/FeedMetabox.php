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
		wp_nonce_field('phft_save_feed_metabox', 'phft_feed_metabox_nonce');
		$last_error = get_post_meta($post->ID, '_phft_last_error', true);
		$feed_url = get_post_meta($post->ID, '_phft_feed_url', true);

		$value = $feed_url ?: '';

		if(!empty($last_error)){
			/* translators: %s is error message */
			echo esc_html(sprintf(__('Last error: %s', 'affiliate-product-highlights'), $last_error));
			echo "<br /><br />";
		}

		echo esc_html__("Feed Url:", 'affiliate-product-highlights')."&nbsp;<input type='text' name='_phft_feed_url' value='" . esc_attr($value) . "' />";
	}
}
