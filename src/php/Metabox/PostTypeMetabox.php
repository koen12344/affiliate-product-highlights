<?php

namespace Koen12344\AffiliateProductHighlights\Metabox;

abstract class PostTypeMetabox implements MetaboxInterface {

	private $post_type;
	public function __construct(string $post_type){
		$this->post_type = $post_type;
	}

	public function get_screen(): string {
		return $this->post_type;
	}
}
