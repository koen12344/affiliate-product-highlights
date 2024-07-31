<?php

namespace Koen12344\AffiliateProductHighlights\Metabox;

use WP_Post;

class SelectionMetabox extends PostTypeMetabox {

	public function get_identifier(): string {
		return 'phft_selection_metabox';
	}

	public function get_title(): string {
		return __('Item selection', 'affiliate-product-highlights');
	}

	public function render( WP_Post $post ) {
		echo '<div id="phft_selection_metabox-inner"></div>';
	}

}
