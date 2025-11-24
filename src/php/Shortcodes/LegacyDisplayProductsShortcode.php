<?php

namespace Koen12344\ProductFrame\Shortcodes;

use Koen12344\ProductFrame\Plugin;
use NumberFormatter;

class LegacyDisplayProductsShortcode extends DisplayProductsShortcode {

	public function get_tag() {
		return 'product-highlights';
	}

}
