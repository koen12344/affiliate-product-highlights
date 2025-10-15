<?php

namespace Koen12344\AffiliateProductHighlights\PostTypes;

class SelectionsPostType implements PostTypeInterface {

	const SELECTIONS_POST_TYPE = 'phft-selections';
	private $plugin_domain;

	public function __toString() {
		return self::SELECTIONS_POST_TYPE;
	}

	public function __construct($plugin_domain) {
		$this->plugin_domain = $plugin_domain;
	}

	public function get_labels(): array {
		return [
			'name' => __('Selections', 'affiliate-product-highlights'),
			'singular_name' => __('Selection', 'affiliate-product-highlights'),
			'add_new' => __('New', 'affiliate-product-highlights'),
			'add_new_item' => __('Add new selection', 'affiliate-product-highlights'),
			'edit' => __('Edit', 'affiliate-product-highlights'),
			'edit_item' => __('Edit selection', 'affiliate-product-highlights'),
			'new_item' => __('New selection', 'affiliate-product-highlights'),
			'view' => __('View', 'affiliate-product-highlights'),
			'view_item' => __('View selection', 'affiliate-product-highlights'),
			'search_items' => __('Find selection', 'affiliate-product-highlights'),
			'not_found' => __('No selections found', 'affiliate-product-highlights'),
			'not_found_in_trash' => __('No selections found in the trash', 'affiliate-product-highlights')
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
		return true;
	}

	public function show_in_rest(): bool {
		return false;
	}
}
