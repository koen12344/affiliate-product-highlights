<?php

namespace Koen12344\ProductFrame\Metabox;

interface HasScriptMetaboxInterface extends MetaboxInterface {
	function enqueue_scripts($hook);
}
