<?php

namespace Koen12344\ProductFrame\HookSubscribers;

use Koen12344\ProductFrame\EventManagement\SubscriberInterface;
use Koen12344\ProductFrame\Shortcodes\ShortcodeInterface;

class ShortcodeSubscriber implements SubscriberInterface {


	private $shortcodes;

	/**
	 * @param ShortcodeInterface[] $shortcodes
	 */
	public function __construct($shortcodes) {

		$this->shortcodes = $shortcodes;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_hooks(): array {
		return [
			'init'  => 'register_shortcodes',
		];
	}

	public function register_shortcodes(){
		foreach($this->shortcodes as $shortcode){
			add_shortcode($shortcode->get_tag(), [$shortcode, 'render']);
		}
	}
}
