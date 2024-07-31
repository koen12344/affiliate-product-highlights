<?php

namespace Koen12344\AffiliateProductHighlights\PostTypes;

interface PostTypeInterface {

	/**
	 * @return string Post Type identifier
	 */
	public function __toString();

	public function get_labels(): array;

	public function is_public():bool;

	public function show_ui():bool;

	public function is_hierarchical():bool;

	public function has_archive():bool;

	public function exclude_from_search(): bool;

	public function is_publicly_queryable(): bool;

	public function show_in_nav_menus(): bool;

	public function show_in_menu(): string;

	public function supports(): array;

	public function can_export(): bool;

}
