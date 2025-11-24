<?php

namespace Koen12344\ProductFrame\Metabox;

use Koen12344\ProductFrame\PostTypes\SelectionsPostType;
use WP_Post;

class SelectionMetabox implements HasScriptMetaboxInterface {

	private $plugin_path;
	private $plugin_url;

	public function __construct($plugin_path, $plugin_url){

		$this->plugin_path = $plugin_path;
		$this->plugin_url = $plugin_url;
	}
	public function get_identifier(): string {
		return 'prfr_selection_metabox';
	}

	public function get_title(): string {
		return __('Item selection', 'productframe');
	}

	public function render( WP_Post $post ) {
		echo '<div id="prfr_selection_metabox-inner"></div>';
	}

	function enqueue_scripts( $hook ) {
		$screen = get_current_screen();
		if(!is_object($screen) || $screen->post_type != SelectionsPostType::SELECTIONS_POST_TYPE){
			return;
		}

		$script_assets = require( $this->plugin_path . 'build/metabox.asset.php');

		wp_register_script('prfr-metabox', $this->plugin_url.'build/metabox.js', $script_assets['dependencies'], $script_assets['version'], true);
		wp_localize_script('prfr-metabox', 'psfg_localize_metabox', [
			'selection_id' => get_the_ID(),
			'userView' => get_user_meta(get_current_user_id(), 'prfr_selection_view', true),
		]);
		wp_set_script_translations('prfr-metabox', 'productframe', $this->plugin_path.'languages');

		wp_enqueue_script('prfr-metabox');

		wp_enqueue_style( 'prfr-metabox-style', $this->plugin_url . 'build/metabox.css', array( 'wp-components' ), $script_assets['version'] );
	}

	public function get_screen() {
		return SelectionsPostType::SELECTIONS_POST_TYPE;
	}
}
