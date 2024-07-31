<?php

namespace Koen12344\AffiliateProductHighlights\HookSubscribers;

use Koen12344\AffiliateProductHighlights\EventManagement\SubscriberInterface;
use Koen12344\AffiliateProductHighlights\Metabox\MetaboxInterface;

class MetaboxSubscriber implements SubscriberInterface {

	/**
	 * @var array
	 */
	private $metaboxes;

	/**
	 * @param MetaboxInterface[] $metaboxes
	 */
	public function __construct(array $metaboxes) {
		$this->metaboxes = $metaboxes;
	}

	public static function get_subscribed_hooks(): array {
		return [
			'add_meta_boxes'  => 'register_meta_boxes'
		];
	}

	private function register_meta_box(MetaboxInterface $metabox){
		add_meta_box(
			$metabox->get_identifier(),
			$metabox->get_title(),
			[$metabox, 'render'],
			$metabox->get_screen()
		);
	}

	public function register_meta_boxes(){
		foreach($this->metaboxes as $metabox){
			$this->register_meta_box($metabox);
		}
	}
}
