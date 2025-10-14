<?php

namespace Koen12344\AffiliateProductHighlights\Admin;

class AdminPage {
	private $plugin_path;
	private $plugin_url;

	public function __construct($plugin_path, $plugin_url){

		$this->plugin_path = $plugin_path;
		$this->plugin_url = $plugin_url;
	}

	public function get_menu_title(){
		return esc_html__('Affiliate Product Highlights', 'affiliate-product-highlights');
	}

	public function get_page_title(){
		return esc_html__('Settings', 'affiliate-product-highlights');
	}

	public function get_capability(){
		return 'manage_options';
	}

	public function get_menu_slug(): string {
		return 'affiliate-product-highlights';
	}

	public function render_page(){
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Slack Settings', 'affiliate-product-highlights'); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('phft_slack_settings_group');
				do_settings_sections('phft-settings');
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function register_settings(){
		register_setting('phft_slack_settings_group', 'phft_slack_webhook_url', [
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default' => ''
		]);

		add_settings_section(
			'phft_slack_settings_section',
			'Slack Webhook Configuration',
			null,
			'phft-settings'
		);

		add_settings_field(
			'phft_slack_webhook_url',
			'Slack Webhook URL',
			function () {
				$value = esc_url(get_option('phft_slack_webhook_url', ''));
				echo "<input type='url' name='phft_slack_webhook_url' value='$value' style='width: 100%; max-width: 400px;' />";
			},
			'phft-settings',
			'phft_slack_settings_section'
		);

		add_settings_section(
			'phft_misc_section',
			'Misc',
			[$this, 'clear_thumbnail_cache'],
			'phft-settings'
		);
	}

	public function clear_thumbnail_cache(){
		?>
			<div id="phft-clear-thumbnail-cache"></div>
		<?php
	}

	public function register_js_assets(){

		$script_assets = require( $this->plugin_path . 'build/admin.asset.php');


		wp_register_script('phft-admin-script', $this->plugin_url . 'build/admin.js', $script_assets['dependencies'], $script_assets['version'], true);

		wp_localize_script('phft-admin-script', 'phft_localize_admin', [

		]);
		wp_set_script_translations('phft-admin-script', 'affiliate-product-highlights', $this->plugin_path . 'languages');
	}

	public function load_js_assets(){
		wp_enqueue_script('phft-admin-script');
		wp_enqueue_style( 'phft-admin-style', $this->plugin_url . 'build/admin.css', array( 'wp-components' ) );
	}

}
