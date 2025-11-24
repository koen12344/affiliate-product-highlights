<?php

namespace Koen12344\ProductFrame\PostTypes;

class FeedPostType implements PostTypeInterface {

	const FEED_POST_TYPE = 'prfr-feeds';
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
			'name' => __('Feeds', 'productframe'),
			'singular_name' => __('Feed', 'productframe'),
			'add_new' => __('New', 'productframe'),
			'add_new_item' => __('Add new feed', 'productframe'),
			'edit' => __('Edit', 'productframe'),
			'edit_item' => __('Edit feed', 'productframe'),
			'new_item' => __('New feed', 'productframe'),
			'view' => __('View', 'productframe'),
			'view_item' => __('View feed', 'productframe'),
			'search_items' => __('Find feed', 'productframe'),
			'not_found' => __('No feeds found', 'productframe'),
			'not_found_in_trash' => __('No feeds found in the trash', 'productframe'),
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
			'thumbnail',
		];
	}

	public function show_in_rest(): bool {
		return true;
	}
	public function can_export(): bool {
		return false;
	}
}
