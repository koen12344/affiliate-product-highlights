<?php

namespace Koen12344\AffiliateProductHighlights\Admin;

class AdminPage {
	public function __construct(){

	}

	public function get_menu_title(){
		return __('Affiliate Product Highlights', 'affiliate-product-highlights');
	}

	public function get_page_title(){
		return __('Settings', 'affiliate-product-highlights');
	}

	public function get_capability(){
		return 'manage_options';
	}
}
