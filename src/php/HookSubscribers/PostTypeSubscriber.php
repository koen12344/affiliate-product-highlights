<?php

namespace Koen12344\ProductFrame\HookSubscribers;

use Koen12344\ProductFrame\EventManagement\SubscriberInterface;
use Koen12344\ProductFrame\PostTypes\PostTypeInterface;

class PostTypeSubscriber implements SubscriberInterface {

	private $post_types;


	/**
	 * @param PostTypeInterface[] $post_types
	 */
	public function __construct(array $post_types) {

		$this->post_types = $post_types;

	}

	public static function get_subscribed_hooks(): array {
		return [
			'init' => 'register_post_types',
		];
	}

	public function register_post_types(){
		foreach($this->post_types as $post_type){
			register_post_type((string)$post_type,
				[
					'labels' => $post_type->get_labels(),
					'public' => $post_type->is_public(),
					'show_ui' => $post_type->show_ui(),
					'hierarchical' => $post_type->is_hierarchical(),
					'has_archive'=> $post_type->has_archive(),
					'exclude_from_search' => $post_type->exclude_from_search(),
					'publicly_queryable' => $post_type->is_publicly_queryable(),
					'show_in_nav_menus' => $post_type->show_in_nav_menus(),
					'show_in_menu' => $post_type->show_in_menu(),
					'supports' => $post_type->supports(),
					'can_export' => $post_type->can_export(),
					'show_in_rest' => $post_type->show_in_rest(),
				]
			);
		}
	}
}
