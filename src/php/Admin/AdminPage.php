<?php

namespace Koen12344\ProductFrame\Admin;

class AdminPage {
	private $plugin_path;
	private $plugin_url;

	public function __construct($plugin_path, $plugin_url){

		$this->plugin_path = $plugin_path;
		$this->plugin_url = $plugin_url;
	}

	public function get_menu_title(){
		return esc_html__('ProductFrame', 'productframe');
	}

	public function get_page_title(){
		return esc_html__('Settings', 'productframe');
	}

	public function get_capability(){
		return 'manage_options';
	}

	public function get_menu_slug(): string {
		return 'productframe';
	}

	public function render_page(){
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Slack Settings', 'productframe'); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('prfr_slack_settings_group');
				do_settings_sections('prfr-settings');
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function register_settings(){
		register_setting('prfr_slack_settings_group', 'prfr_slack_webhook_url', [
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default' => ''
		]);

		add_settings_section(
			'prfr_slack_settings_section',
			__('Slack Webhook Configuration', 'productframe'),
			null,
			'prfr-settings'
		);

		add_settings_field(
			'prfr_slack_webhook_url',
			__('Slack Webhook URL', 'productframe'),
			function () {
				$value = esc_url(get_option('prfr_slack_webhook_url', ''));
				echo "<input type='url' name='prfr_slack_webhook_url' value='". esc_attr($value)."' style='width: 100%; max-width: 400px;' />";
			},
			'prfr-settings',
			'prfr_slack_settings_section'
		);

		add_settings_section(
			'prfr_misc_section',
			__('Misc', 'productframe'),
			[$this, 'clear_thumbnail_cache'],
			'prfr-settings'
		);
	}

	public function clear_thumbnail_cache(){
		?>
			<div id="prfr-clear-thumbnail-cache"></div>
		<?php
	}

	public function register_js_assets(){

		$script_assets = require( $this->plugin_path . 'build/admin.asset.php');


		wp_register_script('prfr-admin-script', $this->plugin_url . 'build/admin.js', $script_assets['dependencies'], $script_assets['version'], true);

		wp_localize_script('prfr-admin-script', 'prfr_localize_admin', [

		]);
//		$test = wp_set_script_translations('prfr-admin-script', 'productframe', $this->plugin_path . 'languages');

	}

	public function load_js_assets(){
		wp_enqueue_script('prfr-admin-script');
		wp_enqueue_style( 'prfr-admin-style', $this->plugin_url . 'build/admin.css', array( 'wp-components' ) );
	}

}
