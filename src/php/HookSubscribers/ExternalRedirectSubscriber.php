<?php

namespace Koen12344\AffiliateProductHighlights\HookSubscribers;

use Koen12344\AffiliateProductHighlights\EventManagement\SubscriberInterface;
use Psr\Log\LoggerInterface;

class ExternalRedirectSubscriber implements SubscriberInterface{

	private LoggerInterface $logger;

	public function __construct(LoggerInterface $logger){
		$this->logger = $logger;
	}
	public static function get_subscribed_hooks(): array {
		return [
			'template_redirect' => 'redirect_external_link',
		];
	}

	public function redirect_external_link(){
		$product_slug = get_query_var('phft_product');
		if($product_slug){
			$cache_key = 'phft_product_' . md5($product_slug);
			$product = wp_cache_get($cache_key, 'phft');
			if ($product === false) {
				global $wpdb;
				$product = $wpdb->get_row($wpdb->prepare("SELECT product_url, product_name, in_latest_import, feed_id FROM {$wpdb->prefix}phft_products WHERE slug = %s", $product_slug));
				if ($product) {
					wp_cache_set($cache_key, $product, 'phft', 3600*24);
				}
			}

			if($product){
				wp_redirect($product->product_url, 302);
				if(!$product->in_latest_import){
					$this->logger->alert(sprintf('Detected outgoing click on item that is no longer available in feed: %s, referring URL: %s', $product->product_name, wp_get_referer()), ['feed_id' => $product->feed_id, 'action' => 'redirect'] );
				}
				exit;
			}
		}
	}
}
