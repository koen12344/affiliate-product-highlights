<?php

namespace Koen12344\ProductFrame\Metabox;


use Koen12344\ProductFrame\PostTypes\FeedPostType;
use WP_Post;

class FeedMetabox implements MetaboxInterface {


	public function get_identifier(): string {
		return 'prfr_feed_metabox';
	}

	public function get_title(): string {
		return __('Feed settings', 'productframe');
	}

	public function render(WP_Post $post) {
		wp_nonce_field('prfr_save_feed_metabox', 'prfr_feed_metabox_nonce');
		$last_error = get_post_meta($post->ID, '_prfr_last_error', true);
		$feed_url = get_post_meta($post->ID, '_prfr_feed_url', true);

		$value = $feed_url ?: '';

		if(!empty($last_error)){
			/* translators: %s is error message */
			echo esc_html(sprintf(__('Last error: %s', 'productframe'), $last_error));
			echo "<br /><br />";
		}

		echo esc_html__("Feed Url:", 'productframe')."&nbsp;<input type='text' name='_prfr_feed_url' value='" . esc_attr($value) . "' />";
	}

	public function get_screen() {
		return FeedPostType::FEED_POST_TYPE;
	}
}
