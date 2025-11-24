<?php

namespace Koen12344\ProductFrame\Shortcodes;

class InlineLinkShortcode implements ShortcodeInterface {

	public function get_tag() {
		return 'prfr-link';
	}

	public function render( $attributes, $content ) {
		global $wpdb;

		$attributes = shortcode_atts([
			'product_id' => null,
		], $attributes, 'prfr-link');

		if($attributes['product_id'] === null){
			return $content;
		}

		$product_id = $attributes['product_id'];

		$product = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}prfr_products WHERE id = %d", $product_id));

		$product_url = esc_url(trailingslashit(home_url('/prfr/' . urlencode($product->slug))));

		if(!empty($content)){
			return '<a target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'">'.$content.'</a>';
		}

		return '<a target="_blank" rel="nofollow noopener sponsored" href="'.$product_url.'">'.esc_html($product->product_name).'</a>';
	}
}
