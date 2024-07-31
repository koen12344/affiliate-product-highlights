<?php

namespace Koen12344\AffiliateProductHighlights\PostTypes;

class FeedPostType implements PostTypeInterface {

	const FEED_POST_TYPE = 'phft-feeds';
	/**
	 * @var string
	 */
	private $plugin_domain;

	public function __construct(string $plugin_domain){
		$this->plugin_domain = $plugin_domain;
	}
	public function __toString() {
		return self::FEED_POST_TYPE;
	}

	public function get_labels(): array {
		return [
			'name' => __('Feeds', 'affiliate-product-highlights'),
			'singular_name' => __('Feed', 'affiliate-product-highlights'),
			'add_new' => __('New', 'affiliate-product-highlights'),
			'add_new_item' => __('Add new feed', 'affiliate-product-highlights'),
			'edit' => __('Edit', 'affiliate-product-highlights'),
			'edit_item' => __('Edit feed', 'affiliate-product-highlights'),
			'new_item' => __('New feed', 'affiliate-product-highlights'),
			'view' => __('View', 'affiliate-product-highlights'),
			'view_item' => __('View feed', 'affiliate-product-highlights'),
			'search_items' => __('Find feed', 'affiliate-product-highlights'),
			'not_found' => __('No feeds found', 'affiliate-product-highlights'),
			'not_found_in_trash' => __('No feeds found in the trash', 'affiliate-product-highlights'),
		];
	}

	public function is_public(): bool {
		return false;
	}

	public function show_ui(): bool {
		return true;
	}

	public function is_hierarchical(): bool {
		return false;
	}

	public function has_archive(): bool {
		return false;
	}

	public function exclude_from_search(): bool {
		return true;
	}

	public function is_publicly_queryable(): bool {
		return false;
	}

	public function show_in_nav_menus(): bool {
		return false;
	}

	public function show_in_menu(): string {
		return $this->plugin_domain;
	}

	public function supports(): array {
		return [
			'title',
		];
	}

	public function can_export(): bool {
		return false;
	}
}
