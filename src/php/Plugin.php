<?php

namespace Koen12344\ProductFrame;
use Koen12344\ProductFrame\DependencyInjection\Container;
use Koen12344\APH_Vendor\Koen12344\GithubPluginUpdater\Updater;

class Plugin {

	const DOMAIN = 'productframe';

	const VERSION = '0.5.0';

	const REST_NAMESPACE = 'prfr/v1';

	const DASHICON = '<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 282.056 282.056"><path fill="#9ea3a8" d="M138.717,105.974c-9.179,9.179-4.771,25.236-6.146,26.612c-1.204,1.205-17.024-3.441-26.615,6.15 c-9.328,9.329-9.251,24.639-0.434,34.452c4.98,5.543,10.682,7.854,16.701,9.09c12.023,2.471,50.354-0.965,54.744-5.355 c4.484-4.484,7.795-42.563,5.294-54.682c-1.248-6.041-3.546-11.722-9.091-16.701C163.355,96.723,148.047,96.646,138.717,105.974 "/> <path fill="#9ea3a8" d="M279.619,149.718L137.304,7.403c-3.247-3.248-9.66-5.764-14.249-5.59L8.659,0C4.071,0.176,0.175,4.072,0,8.66 l1.813,114.397c-0.175,4.588,2.342,11,5.59,14.248l142.315,142.316c3.247,3.246,8.562,3.246,11.81,0l118.094-118.094 C282.867,158.279,282.867,152.965,279.619,149.718z M17.243,34.171c-4.674-4.674-4.674-12.252,0-16.926 c4.676-4.676,12.252-4.676,16.927,0c4.675,4.674,4.675,12.252,0,16.926C29.495,38.845,21.919,38.844,17.243,34.171z M253.432,161.91l-91.523,91.521c-3.246,3.248-8.562,3.248-11.81,0L32.049,135.383c-3.248-3.248-3.248-8.563,0-11.811 l91.523-91.521c3.248-3.248,8.561-3.248,11.809,0L253.432,150.1C256.68,153.348,256.68,158.662,253.432,161.91z"/></svg>';

	private $container;

	private $loaded;


	public function __construct($file){
		$this->loaded = false;

		$this->container = new Container([
			'plugin_basename'       => plugin_basename($file),
			'plugin_domain'         => self::DOMAIN,
			'plugin_path'           => plugin_dir_path($file),
			'plugin_relative_path'  => basename(plugin_dir_path($file)),
			'plugin_url'            => plugin_dir_url($file),
			'plugin_version'        => self::VERSION,
			'plugin_rest_namespace' => self::REST_NAMESPACE,
			'plugin_dashicon'       => self::DASHICON,
		]);

		$updater = new Updater(
			new \WP_Http(),
			$file,
			'koen12344',
			'productframe',
			self::VERSION
		);
		$updater->register();


		add_image_size('prfr-logo', 100, 30 );
		add_image_size('prfr-product-thumb', 0, 150 );
	}

	public static function activate(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$products_table = $wpdb->prefix . 'prfr_products';

		$images_table = $wpdb->prefix.'prfr_images';

		$logs_table = $wpdb->prefix.'prfr_logs';

		$charset_collate = $wpdb->get_charset_collate();

		// Define the table schema
		$sql = "CREATE TABLE $products_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        feed_id bigint(20) UNSIGNED NOT NULL,
        campaign_id bigint(20) UNSIGNED DEFAULT NULL,
        product_id bigint(20) UNSIGNED DEFAULT NULL,
        sku varchar(255) DEFAULT NULL,
        product_name varchar(255) NOT NULL,
        product_price DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        product_original_price DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        product_currency varchar(3) NOT NULL,
		product_url varchar(255) NOT NULL,
	 	product_description TEXT,
	 	product_ean varchar(15) NOT NULL,
	 	slug varchar(255) NOT NULL,
	 	imported_at datetime NOT NULL,
        in_latest_import tinyint(1) NOT NULL DEFAULT 1,
	 	PRIMARY KEY  (id),
        UNIQUE KEY feed_campaign_product_unique (feed_id, campaign_id, product_id),
        UNIQUE KEY feed_sku_unique (feed_id, sku),
        UNIQUE KEY slug_unique (slug),
        KEY product_name_idx (product_name)
    ) $charset_collate;";

		$sql  .= "CREATE TABLE $images_table (
    		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    		feed_id bigint(20) UNSIGNED NOT NULL,
    		product_id bigint(20) UNSIGNED NOT NULL,
    		image_url varchar(255) NOT NULL,
    		wp_media_id bigint(20) UNSIGNED NOT NULL,
    		imported_at datetime NOT NULL,
    		PRIMARY KEY  (id),
    		KEY image_url_idx (image_url)
		) $charset_collate;";

		$sql .= "CREATE TABLE $logs_table (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		feed_id BIGINT UNSIGNED NULL,
		action VARCHAR(100) NULL,
		level VARCHAR(20) NOT NULL,
		message TEXT NOT NULL,
		context LONGTEXT NULL,
		created_at DATETIME NOT NULL,
		PRIMARY KEY (id),
		KEY feed_id (feed_id),
		KEY action (action)
	) $charset_collate;";

		dbDelta($sql);

		if(!wp_next_scheduled('prfr_update_feeds')){
			wp_schedule_event(time(), 'daily', 'prfr_update_feeds');
		}


	}

	public static function deactivate(){

	}

	public static function uninstall(){
		global $wpdb;

		//Delete all sideloaded media
		$wp_media = $wpdb->get_results("SELECT wp_media_id FROM {$wpdb->prefix}prfr_images WHERE wp_media_id > 0");
		if($wp_media){
			foreach($wp_media as $media){
				wp_delete_attachment($media->wp_media_id, true);
			}
		}

		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}prfr_images,{$wpdb->prefix}prfr_products,{$wpdb->prefix}prfr_logs");
	}

	public function is_loaded(): bool {
		return $this->loaded;
	}

	public function init(){
		if($this->is_loaded()){
			return;
		}

		$this->container->configure([
			Configuration\AdminConfiguration::class,
			Configuration\MetaboxConfiguration::class,
			Configuration\PostTypeConfiguration::class,
			Configuration\EventManagementConfiguration::class,
			Configuration\WordPressConfiguration::class,
			Configuration\RestApiConfiguration::class,
			Configuration\BackgroundProcessConfiguration::class,
			Configuration\LoggerConfiguration::class,
			Configuration\ShortcodeConfiguration::class,
		]);

		foreach($this->container['subscribers'] as $subscriber){
			$this->container['service.event_manager']->add_subscriber($subscriber);
		}

		$this->loaded = true;
	}

}
