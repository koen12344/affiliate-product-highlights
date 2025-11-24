<?php

namespace Koen12344\ProductFrame\PostTypes;

class SelectionsPostType implements PostTypeInterface {

	const SELECTIONS_POST_TYPE = 'prfr-selections';
	private $plugin_domain;

	public function __toString() {
		return self::SELECTIONS_POST_TYPE;
	}

	public function __construct($plugin_domain) {
		$this->plugin_domain = $plugin_domain;
	}

	public function get_labels(): array {
		return [
			'name' => __('Selections', 'productframe'),
			'singular_name' => __('Selection', 'productframe'),
			'add_new' => __('New', 'productframe'),
			'add_new_item' => __('Add new selection', 'productframe'),
			'edit' => __('Edit', 'productframe'),
			'edit_item' => __('Edit selection', 'productframe'),
			'new_item' => __('New selection', 'productframe'),
			'view' => __('View', 'productframe'),
			'view_item' => __('View selection', 'productframe'),
			'search_items' => __('Find selection', 'productframe'),
			'not_found' => __('No selections found', 'productframe'),
			'not_found_in_trash' => __('No selections found in the trash', 'productframe')
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
