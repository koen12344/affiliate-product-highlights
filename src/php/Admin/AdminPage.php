<?php

namespace Koen12344\AffiliateProductHighlights\Admin;

class AdminPage {
	public function __construct(){

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
			<h1>Slack Settings</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('phft_slack_settings_group');
				do_settings_sections('phft-slack-settings');
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
			'phft-slack-settings'
		);

		add_settings_field(
			'phft_slack_webhook_url',
			'Slack Webhook URL',
			function () {
				$value = esc_url(get_option('phft_slack_webhook_url', ''));
				echo "<input type='url' name='phft_slack_webhook_url' value='$value' style='width: 100%; max-width: 400px;' />";
			},
			'phft-slack-settings',
			'phft_slack_settings_section'
		);
	}
}
