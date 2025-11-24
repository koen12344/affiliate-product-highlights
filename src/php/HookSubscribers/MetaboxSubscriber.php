<?php

namespace Koen12344\ProductFrame\HookSubscribers;

use Koen12344\ProductFrame\EventManagement\SubscriberInterface;
use Koen12344\ProductFrame\Metabox\HasScriptMetaboxInterface;
use Koen12344\ProductFrame\Metabox\MetaboxInterface;

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
			'add_meta_boxes'  => 'register_meta_boxes',
			'admin_enqueue_scripts' => 'enqueue_scripts',
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

	public function enqueue_scripts($hook){
		if(!in_array($hook, [ 'post.php', 'post-new.php' ] )){
			return;
		}

		foreach($this->metaboxes as $metabox){
			if($metabox instanceof HasScriptMetaboxInterface){
				$metabox->enqueue_scripts($hook);
			}
		}
	}
}
