<?php

namespace Koen12344\AffiliateProductHighlights\HookSubscribers;

use Koen12344\AffiliateProductHighlights\Admin\AdminPage;
use Koen12344\AffiliateProductHighlights\EventManagement\SubscriberInterface;

class AdminPageSubscriber implements SubscriberInterface{

	private mixed $admin_page;

	public function __construct(AdminPage $admin_page) {
		$this->admin_page = $admin_page;
	}

	public static function get_subscribed_hooks(): array {
		return [
			'admin_menu' => 'add_admin_page',
			'admin_init' => 'register_settings',
		];
	}

	public function add_admin_page(){
		$page_hook = add_menu_page(
			$this->admin_page->get_page_title(),
			$this->admin_page->get_menu_title(),
			'edit_posts',
			$this->admin_page->get_menu_slug(),
			[$this->admin_page, 'render_page']
		);

		$submenu_hook = add_submenu_page(
			$this->admin_page->get_menu_slug(),
			$this->admin_page->get_page_title(),
			$this->admin_page->get_page_title(),
			$this->admin_page->get_capability(),
			$this->admin_page->get_menu_slug(),
			[$this->admin_page, 'render_page'],
			3
		);
	}

	public function register_settings(){
		$this->admin_page->register_settings();
	}
}
