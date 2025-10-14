<?php

namespace Koen12344\AffiliateProductHighlights\HookSubscribers;

use Koen12344\AffiliateProductHighlights\Admin\AdminPage;
use Koen12344\AffiliateProductHighlights\EventManagement\EventManager;
use Koen12344\AffiliateProductHighlights\EventManagement\EventManagerAwareSubscriberInterface;
use Koen12344\AffiliateProductHighlights\EventManagement\SubscriberInterface;

class AdminPageSubscriber implements EventManagerAwareSubscriberInterface {

	private mixed $admin_page;
	private EventManager $event_manager;

	public function __construct(AdminPage $admin_page) {
		$this->admin_page = $admin_page;
	}

	public static function get_subscribed_hooks(): array {
		return [
			'admin_menu' => 'add_admin_page',
			'admin_init' => 'register_settings',
			'admin_enqueue_scripts' => 'register_js_assets',
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
		$this->event_manager->add_callback("admin_print_scripts-{$page_hook}", [$this->admin_page, 'load_js_assets']);
	}

	public function register_settings(){
		$this->admin_page->register_settings();
	}

	function register_js_assets(){
		$this->admin_page->register_js_assets();
	}

	public function set_event_manager( EventManager $event_manager ) {
		$this->event_manager = $event_manager;
	}
}
