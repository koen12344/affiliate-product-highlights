<?php

namespace Koen12344\AffiliateProductHighlights\Metabox;

use WP_Post;

interface MetaboxInterface {
	public function get_identifier() : string;

	public function get_title() : string;

	public function render(WP_Post $post);

	public function get_screen();
}
