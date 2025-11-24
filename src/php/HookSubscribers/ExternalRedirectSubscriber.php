<?php

namespace Koen12344\ProductFrame\HookSubscribers;

use Koen12344\ProductFrame\EventManagement\SubscriberInterface;
use Psr\Log\LoggerInterface;

class ExternalRedirectSubscriber implements SubscriberInterface{

	private LoggerInterface $logger;

	public function __construct(LoggerInterface $logger){
		$this->logger = $logger;
	}
	public static function get_subscribed_hooks(): array {
		return [
			'template_redirect' => 'redirect_external_link',
			'init'              => 'register_rewrite_rule',
		];
	}

	public function register_rewrite_rule(){
		add_rewrite_rule('^prfr/([^/]+)/?$', 'index.php?prfr_product=$matches[1]', 'top');
		add_rewrite_tag('%prfr_product%', '([^/]+)');
	}

	public function redirect_external_link(){
		$product_slug = sanitize_title_for_query(rawurldecode(get_query_var('prfr_product')));
		if(empty($product_slug)) {
			return;
		}

		$cache_key = 'prfr_product_' . md5($product_slug);
		$product = wp_cache_get($cache_key, 'prfr');
		if ($product === false) {
			global $wpdb;
			$product = $wpdb->get_row($wpdb->prepare("SELECT product_url, product_name, in_latest_import, feed_id FROM {$wpdb->prefix}prfr_products WHERE slug = %s", $product_slug));
			if ($product) {
				wp_cache_set($cache_key, $product, 'prfr', 3600*24);
			}
		}

		if($product){
			//phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- URls are extracted from the product feeds and unique for each vendor so it's impossible to whitelist them and use wp_safe_redirect
			wp_redirect($product->product_url, 302);
			if(!$product->in_latest_import){
				/* translators: %1$s is product name, %2$s is the URL of the page where the click originated */
				$this->logger->alert(sprintf(__('Detected outgoing click on item that is no longer available in feed: %1$s, referring URL: %2$s', 'productframe'), $product->product_name, wp_get_referer() ? wp_get_referer() : __('Unknown', 'productframe')), ['feed_id' => $product->feed_id, 'action' => 'redirect'] );
			}
			exit;
		}

		wp_die(
			esc_html__( 'Product not found.', 'productframe' ),
			esc_html__( 'Not Found', 'productframe' ),
			[ 'response' => 404 ]
		);

	}
}
