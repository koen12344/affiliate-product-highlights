<?php

namespace Koen12344\ProductFrame\Shortcodes;

interface ShortcodeInterface {
	public function get_tag();
	public function render($attributes, $content);
}
